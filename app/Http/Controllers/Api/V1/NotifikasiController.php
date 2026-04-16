<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pemakaian;
use App\Models\Pengadaan;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Daftar notifikasi — gabungan pemakaian & pengadaan yang statusnya berubah.
     * Logic identik dengan Karyawan\NotifikasiController@index web.
     *
     * Juga mencatat notif_read_at = now() saat endpoint ini dipanggil.
     */
    public function index()
    {
        $user = Auth::user();

        // Catat waktu user membuka notifikasi (sama seperti web)
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

        // Gabungkan dan urutkan (identik dengan web)
        $notifikasis = collect();

        foreach ($pemakaians as $p) {
            $notifikasis->push([
                'type'    => 'pemakaian',
                'id'      => $p->id,
                'status'  => $p->status,
                'barangs' => $p->details->map(function ($d) {
                    $nama = $d->barang->nama_barang ?? 'Barang';
                    return $nama . ' ×' . $d->jumlah;
                })->join(', '),
                'by'      => $p->approvedBy?->name ?? '—',
                'time'    => $p->updated_at->toIso8601String(),
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
                'barangs' => $p->details->map(function ($d) {
                    $nama = $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru';
                    return $nama . ' ×' . $d->jumlah;
                })->join(', '),
                'by'      => '—',
                'time'    => $p->updated_at->toIso8601String(),
            ]);
        }

        $notifikasis = $notifikasis->sortByDesc('time')->values();

        // Unread count (7 hari terakhir, identik dengan web)
        $unreadCount = $notifikasis->filter(
            fn($n) => now()->diffInDays(\Carbon\Carbon::parse($n['time'])) <= 7
        )->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'notifikasi'   => $notifikasis,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Badge count — hitung notifikasi yang muncul SETELAH user terakhir buka notifikasi.
     * Logic identik dengan Karyawan\NotifikasiController::getBadgeCount web.
     *
     * Digunakan Android untuk menampilkan badge di bottom navigation.
     */
    public function badgeCount()
    {
        $user      = Auth::user();
        $readAt    = $user->notif_read_at;
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

        return response()->json([
            'success' => true,
            'data'    => [
                'badge_count' => $pemakaian + $pengadaan,
            ],
        ]);
    }
}
