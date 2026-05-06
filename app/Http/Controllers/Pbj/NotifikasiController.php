<?php

namespace App\Http\Controllers\Pbj;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\Auth;

/**
 * BARU — Pbj\NotifikasiController.php
 * Notifikasi untuk Level 3 (Pejabat Pengadaan / PBJ).
 * Isi notifikasi: pengadaan yang masuk setelah di-approve Divisi Umum,
 * beserta status tindak lanjut (pending/completed/rejected) dan verifikasi admin.
 */
class NotifikasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Catat waktu buka halaman → badge hilang
        $user->update(['notif_read_at' => now()]);

        // Ambil semua pengadaan yang sudah disetujui Divisi Umum (domain PBJ)
        $pengadaans = Pengadaan::with(['user', 'details.barang'])
            ->where('status_level2', 'approved')
            ->latest('updated_at')
            ->get();

        $notifikasis = collect();

        foreach ($pengadaans as $p) {
            // Status granular sesuai kondisi
            if ($p->status_level3 === 'completed') {
                if ($p->status_admin_verifikasi === 'verified') {
                    $granularStatus = 'verified';
                } elseif ($p->status_admin_verifikasi === 'rejected') {
                    $granularStatus = 'verifikasi_ditolak';
                } else {
                    $granularStatus = 'menunggu_verifikasi';
                }
            } elseif ($p->status_level3 === 'rejected') {
                $granularStatus = 'rejected';
            } else {
                $granularStatus = 'pending';
            }

            $notifikasis->push([
                'type'    => 'pengadaan',
                'id'      => $p->id,
                'status'  => $granularStatus,
                'pemohon' => $p->user->name ?? '—',
                'barangs' => $p->details->map(function ($d) {
                    $nama = $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru';
                    return $nama . ' ×' . $d->jumlah;
                })->join(', '),
                'time'    => $p->updated_at,
            ]);
        }

        $notifikasis = $notifikasis->sortByDesc('time')->values();

        $unreadCount = $notifikasis->filter(
            fn($n) => $n['time']->diffInDays(now()) <= 7
        )->count();

        // Hitung summary
        $totalPending   = $notifikasis->where('status', 'pending')->count();
        $totalSelesai   = $notifikasis->whereIn('status', ['menunggu_verifikasi', 'verified'])->count();
        $totalDitolak   = $notifikasis->whereIn('status', ['rejected', 'verifikasi_ditolak'])->count();

        return view('pbj.notifikasi', compact(
            'notifikasis', 'unreadCount', 'totalPending', 'totalSelesai', 'totalDitolak'
        ));
    }

    /**
     * Badge count untuk sidebar dan topbar PBJ.
     * Hitung pengadaan masuk yang updated SETELAH notif_read_at.
     */
    public static function getBadgeCount(): int
    {
        if (!Auth::check()) return 0;

        $user      = Auth::user();
        $readAt    = $user->notif_read_at;
        $threshold = $readAt ?? now()->subDays(7);

        return Pengadaan::where('status_level2', 'approved')
            ->where('updated_at', '>', $threshold)
            ->count();
    }
}
