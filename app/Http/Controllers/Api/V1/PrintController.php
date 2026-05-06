<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class PrintController extends Controller
{
    /**
     * Resolve user dari token yang dikirim via query parameter.
     * WebView tidak selalu reliable kirim header Authorization,
     * jadi kita terima token via ?token=xxx
     */
    private function resolveUser(Request $request)
    {
        // Coba dari auth middleware dulu (jika header Authorization ada)
        if ($request->user()) {
            return $request->user();
        }

        // Fallback: cari dari query param ?token=
        $token = $request->query('token');
        if (!$token) {
            abort(401, 'Token tidak ditemukan.');
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            abort(401, 'Token tidak valid.');
        }

        return $accessToken->tokenable;
    }

    /**
     * Print satu permintaan pemakaian.
     * Return HTML view (sama dengan versi web).
     */
    public function pemakaian(Request $request, Pemakaian $pemakaian)
    {
        $user = $this->resolveUser($request);

        // Pastikan pemakaian milik user ini
        if ($pemakaian->user_id !== $user->id) {
            abort(403);
        }

        $pemakaian->load(['details.barang', 'approvedBy']);

        return view('karyawan.print-single-pemakaian', compact('user', 'pemakaian'));
    }

    /**
     * Print satu permintaan pengadaan.
     * Return HTML view (sama dengan versi web).
     */
    public function pengadaan(Request $request, Pengadaan $pengadaan)
    {
        $user = $this->resolveUser($request);

        // Pastikan pengadaan milik user ini
        if ($pengadaan->user_id !== $user->id) {
            abort(403);
        }

        $pengadaan->load(['details.barang']);

        return view('karyawan.print-single-pengadaan', compact('user', 'pengadaan'));
    }

    /**
     * Print laporan aktivitas bulanan.
     * Return HTML view (sama dengan versi web).
     */
    public function aktivitas(Request $request)
    {
        $user  = $this->resolveUser($request);
        $bulan = $request->input('bulan', now()->format('Y-m'));

        // Parse bulan
        $start = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $pengadaans = Pengadaan::with(['details.barang'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $bulanLabel = $start->locale('id')->isoFormat('MMMM Y');

        return view('karyawan.print-aktivitas', compact(
            'user', 'pemakaians', 'pengadaans', 'bulanLabel', 'bulan'
        ));
    }
}
