<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Halaman daftar semua notifikasi.
     */
    public function index()
    {
        $user = Auth::user();

        // Pemakaian yang statusnya berubah (approved/rejected)
        $pemakaians = Pemakaian::with(['details.barang', 'approvedBy'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->get();

        // Pengadaan yang statusnya berubah
        $pengadaans = Pengadaan::with(['details.barang'])
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status_level2', '!=', 'pending')
                  ->orWhere('status_level3', 'completed');
            })
            ->latest('updated_at')
            ->get();

        // Gabungkan dan urutkan berdasarkan updated_at
        $notifikasis = collect();

        foreach ($pemakaians as $p) {
            $notifikasis->push([
                'type'    => 'pemakaian',
                'id'      => $p->id,
                'status'  => $p->status,
                'barangs' => $p->details->map(fn($d) => $d->barang->nama_barang . ' ×' . $d->jumlah)->join(', '),
                'by'      => $p->approvedBy?->name ?? '—',
                'time'    => $p->updated_at,
            ]);
        }

        foreach ($pengadaans as $p) {
            $status = $p->status_level3 === 'completed' ? 'completed'
                    : ($p->status_level2 === 'approved' ? 'approved_l2'
                    : ($p->status_level2 === 'rejected' ? 'rejected' : 'pending'));

            $notifikasis->push([
                'type'    => 'pengadaan',
                'id'      => $p->id,
                'status'  => $status,
                'barangs' => $p->details->map(fn($d) => $d->barang->nama_barang . ' ×' . $d->jumlah)->join(', '),
                'by'      => '—',
                'time'    => $p->updated_at,
            ]);
        }

        $notifikasis = $notifikasis->sortByDesc('time')->values();

        // Hitung badge (notifikasi baru: updated dalam 7 hari)
        $unreadCount = $notifikasis->filter(
            fn($n) => $n['time']->diffInDays(now()) <= 7
        )->count();

        return view('karyawan.notifikasi', compact('notifikasis', 'unreadCount'));
    }

    /**
     * Jumlah notifikasi untuk badge di topbar (dipanggil via helper/view composer).
     * Bisa juga dipakai sebagai endpoint JSON jika diperlukan.
     */
    public static function getBadgeCount(): int
    {
        if (!Auth::check()) return 0;

        $user = Auth::user();

        $pemakaian = Pemakaian::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        $pengadaan = Pengadaan::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status_level2', '!=', 'pending')
                  ->orWhere('status_level3', 'completed');
            })
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return $pemakaian + $pengadaan;
    }
}
