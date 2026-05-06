<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\Auth;

/**
 * BARU — Admin\NotifikasiController.php
 * Notifikasi untuk Level 2 (Divisi Umum).
 * Isi notifikasi: permintaan baru masuk dari karyawan (pemakaian & pengadaan).
 * Fix bug badge: menggunakan notif_read_at yang sama dengan Level 1.
 */
class NotifikasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Catat waktu buka halaman → badge hilang
        $user->update(['notif_read_at' => now()]);

        // Permintaan pemakaian baru (semua status, diurutkan terbaru)
        $pemakaians = Pemakaian::with(['user', 'details.barang'])
            ->latest('updated_at')
            ->get();

        // Permintaan pengadaan baru
        $pengadaans = Pengadaan::with(['user', 'details.barang'])
            ->latest('updated_at')
            ->get();

        $notifikasis = collect();

        foreach ($pemakaians as $p) {
            $notifikasis->push([
                'type'    => 'pemakaian',
                'id'      => $p->id,
                'status'  => $p->status,
                'pemohon' => $p->user->name ?? '—',
                'barangs' => $p->details->map(fn($d) => ($d->barang->nama_barang ?? '-') . ' ×' . $d->jumlah)->join(', '),
                'time'    => $p->updated_at,
            ]);
        }

        foreach ($pengadaans as $p) {
            // Determine granular status for admin clarity
            if ($p->status_level2 === 'rejected') {
                $granularStatus = 'rejected';
            } elseif ($p->status_level2 === 'pending') {
                $granularStatus = 'pending';
            } elseif ($p->status_level2 === 'approved' && $p->status_level3 === 'completed') {
                if ($p->status_admin_verifikasi === 'verified') {
                    $granularStatus = 'verified';
                } elseif ($p->status_admin_verifikasi === 'rejected') {
                    $granularStatus = 'verifikasi_ditolak';
                } else {
                    $granularStatus = 'menunggu_verifikasi';
                }
            } elseif ($p->status_level2 === 'approved' && $p->status_level3 === 'rejected') {
                $granularStatus = 'ditolak_pbj';
            } elseif ($p->status_level2 === 'approved') {
                $granularStatus = 'diproses_pbj';
            } else {
                $granularStatus = $p->status_level2;
            }

            $notifikasis->push([
                'type'    => 'pengadaan',
                'id'      => $p->id,
                'status'  => $granularStatus,
                'pemohon' => $p->user->name ?? '—',
                'barangs' => $p->details->map(fn($d) => ($d->barang->nama_barang ?? $d->nama_barang_baru ?? '-') . ' ×' . $d->jumlah)->join(', '),
                'time'    => $p->updated_at,
            ]);
        }

        $notifikasis = $notifikasis->sortByDesc('time')->values();

        $unreadCount = $notifikasis->filter(
            fn($n) => $n['time']->diffInDays(now()) <= 7
        )->count();

        return view('admin.notifikasi', compact('notifikasis', 'unreadCount'));
    }

    /**
     * Badge count untuk sidebar dan topbar admin.
     * Hitung permintaan baru (pemakaian/pengadaan) yang masuk SETELAH notif_read_at.
     */
    public static function getBadgeCount(): int
    {
        if (!Auth::check()) return 0;

        $user      = Auth::user();
        $readAt    = $user->notif_read_at;
        $threshold = $readAt ?? now()->subDays(7);

        $pemakaian = Pemakaian::where('updated_at', '>', $threshold)->count();
        $pengadaan = Pengadaan::where('updated_at', '>', $threshold)->count();

        return $pemakaian + $pengadaan;
    }
}
