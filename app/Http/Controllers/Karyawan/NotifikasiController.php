<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\Auth;

/**
 * DIMODIFIKASI — Karyawan/NotifikasiController.php
 * Perubahan (fix bug badge):
 *  1. index() sekarang mencatat notif_read_at = now() saat halaman notifikasi dibuka
 *  2. getBadgeCount() sekarang hanya menghitung notifikasi yang updated_at
 *     LEBIH BARU dari notif_read_at user → badge hilang setelah halaman dibuka
 */
class NotifikasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── FIX BUG: Catat waktu user membuka halaman notifikasi ──
        // Ini yang membuat badge dot merah hilang setelah halaman dibuka
        $user->update(['notif_read_at' => now()]);

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

        // Gabungkan dan urutkan
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
                    : ($p->status_level2 === 'approved'  ? 'approved_l2'
                    : ($p->status_level2 === 'rejected'  ? 'rejected' : 'pending'));

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

        // unreadCount hanya untuk tampilan summary (7 hari)
        $unreadCount = $notifikasis->filter(
            fn($n) => $n['time']->diffInDays(now()) <= 7
        )->count();

        return view('karyawan.notifikasi', compact('notifikasis', 'unreadCount'));
    }

    /**
     * Badge count: hitung notifikasi yang muncul SETELAH user terakhir buka halaman notifikasi.
     * Jika notif_read_at null → hitung semua dalam 7 hari (user belum pernah buka).
     */
    public static function getBadgeCount(): int
    {
        if (!Auth::check()) return 0;

        $user      = Auth::user();
        $readAt    = $user->notif_read_at;
        // Jika belum pernah buka notifikasi, gunakan 7 hari sebagai batas
        $threshold = $readAt ?? now()->subDays(7);

        $pemakaian = Pemakaian::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>', $threshold)
            ->count();

        $pengadaan = Pengadaan::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status_level2', '!=', 'pending')
                  ->orWhere('status_level3', 'completed');
            })
            ->where('updated_at', '>', $threshold)
            ->count();

        return $pemakaian + $pengadaan;
    }
}
