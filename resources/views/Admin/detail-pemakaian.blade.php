@extends('layouts.admin')

@section('title', 'Detail Permintaan Pemakaian')

@section('topbar-title')
  Detail <span>Permintaan Pemakaian</span>
@endsection

@section('content')
{{--
  ┌──────────────────────────────────────────────────────────────┐
  │  BARU — admin/detail-pemakaian.blade.php                     │
  │  Halaman detail permintaan pemakaian. Menampilkan info        │
  │  lengkap pemohon, daftar barang yang diminta beserta jumlah  │
  │  dan stok saat ini. Admin dapat approve/reject dari sini.    │
  └──────────────────────────────────────────────────────────────┘
--}}

  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
      <span class="sep">/</span>
      <a href="{{ route('admin.pemakaian.index') }}" style="color:inherit;text-decoration:none;">Pemakaian</a>
      <span class="sep">/</span>
      <span class="current">Detail #{{ $pemakaian->id }}</span>
    </div>
    <h2>Detail Permintaan Pemakaian</h2>
    <p>Informasi lengkap permintaan pemakaian barang dari karyawan.</p>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- KOLOM KIRI: Info + Tabel Barang --}}
    <div>
      {{-- Info Pemohon --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3><i class="bi bi-person-circle" style="color:#0055A5;margin-right:8px;"></i>Informasi Pemohon</h3>
        </div>
        <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div>
            <div class="detail-label">Nama Lengkap</div>
            <div class="detail-value">{{ $pemakaian->user->name ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Email</div>
            <div class="detail-value">{{ $pemakaian->user->email ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Bagian / Divisi</div>
            <div class="detail-value">{{ $pemakaian->user->bagian ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Jabatan</div>
            <div class="detail-value">{{ $pemakaian->user->jabatan ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Tanggal Permintaan</div>
            <div class="detail-value">
              {{ $pemakaian->created_at->locale('id')->isoFormat('dddd, D MMMM Y — HH:mm') }}
            </div>
          </div>
          <div>
            <div class="detail-label">Status Saat Ini</div>
            <div class="detail-value">
              @php
                $sMap = [
                  'pending'  => ['Menunggu Persetujuan', 'badge-pending'],
                  'approved' => ['Disetujui', 'badge-approved'],
                  'rejected' => ['Ditolak', 'badge-rejected'],
                ];
                [$lbl, $cls] = $sMap[$pemakaian->status] ?? ['-', ''];
              @endphp
              <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Daftar Barang yang Diminta --}}
      <div class="card">
        <div class="card-header">
          <h3><i class="bi bi-box-seam" style="color:#0055A5;margin-right:8px;"></i>Barang yang Diminta</h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Barang</th>
                <th>Kode</th>
                <th>Jumlah Diminta</th>
                <th>Stok Tersedia</th>
                <th>Keterangan</th>
                <th>Kecukupan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pemakaian->details as $i => $detail)
                <tr>
                  <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                  <td style="font-weight:600;color:#1E293B;">{{ $detail->barang->nama_barang ?? '-' }}</td>
                  <td><span style="font-family:'DM Mono',monospace;font-size:12px;color:#0055A5;">{{ $detail->barang->kode_barang ?? '-' }}</span></td>
                  <td>
                    <span style="font-weight:700;color:#1D4ED8;">
                      {{ $detail->jumlah }} {{ $detail->barang->satuan ?? '' }}
                    </span>
                  </td>
                  <td>
                    @php $stok = $detail->barang->stok ?? 0; @endphp
                    <span style="font-weight:600;color:{{ $stok === 0 ? '#DC2626' : ($stok <= 10 ? '#D97706' : '#059669') }}">
                      {{ $stok }} {{ $detail->barang->satuan ?? '' }}
                    </span>
                  </td>
                  <td style="font-size:12px;color:#64748B;">{{ $detail->keterangan ?? '-' }}</td>
                  <td>
                    @if(($detail->barang->stok ?? 0) >= $detail->jumlah)
                      <span class="status-badge badge-approved"><i class="bi bi-check-circle"></i> Cukup</span>
                    @else
                      <span class="status-badge badge-rejected"><i class="bi bi-exclamation-triangle"></i> Kurang</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- KOLOM KANAN: Panel Aksi --}}
    <div>
      <div class="card" style="position:sticky;top:24px;">
        <div class="card-header">
          <h3><i class="bi bi-gear" style="color:#0055A5;margin-right:8px;"></i>Tindakan</h3>
        </div>
        <div style="padding:20px 24px;">

          @if($pemakaian->status === 'pending')
            <p style="font-size:13px;color:#64748B;margin-bottom:20px;line-height:1.6;">
              Periksa ketersediaan stok lalu ambil tindakan.
              Menyetujui permintaan akan <strong>otomatis mengurangi stok</strong> barang yang bersangkutan.
            </p>

            {{-- Tombol Setujui --}}
            <button type="button"
              onclick="showConfirm({
                title: 'Setujui Permintaan?',
                message: 'Stok barang akan otomatis berkurang sesuai jumlah yang diminta. Lanjutkan?',
                icon: 'bi-check-circle-fill', iconColor: '#059669',
                confirmText: 'Ya, Setujui', confirmClass: 'confirm-btn-success',
                onConfirm: function() { document.getElementById('form-approve-detail').submit(); }
              })"
              style="width:100%;padding:12px;border-radius:10px;border:none;background:#059669;
                     color:#fff;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;
                     display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;
                     transition:background .15s;"
              onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
              <i class="bi bi-check-circle-fill"></i> Setujui Permintaan
            </button>
            <form id="form-approve-detail" method="POST"
                  action="{{ route('admin.pemakaian.approve', $pemakaian->id) }}">
              @csrf @method('PATCH')
            </form>

            {{-- Tombol Tolak --}}
            <button type="button"
              onclick="showConfirm({
                title: 'Tolak Permintaan?',
                message: 'Permintaan akan ditolak dan tidak ada perubahan stok.',
                icon: 'bi-x-circle-fill', iconColor: '#DC2626',
                confirmText: 'Ya, Tolak', confirmClass: 'confirm-btn-danger',
                onConfirm: function() { document.getElementById('form-reject-detail').submit(); }
              })"
              style="width:100%;padding:12px;border-radius:10px;border:none;background:#FEF2F2;
                     color:#DC2626;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;
                     display:flex;align-items:center;justify-content:center;gap:8px;border:1.5px solid #FCA5A5;">
              <i class="bi bi-x-circle-fill"></i> Tolak Permintaan
            </button>
            <form id="form-reject-detail" method="POST"
                  action="{{ route('admin.pemakaian.reject', $pemakaian->id) }}">
              @csrf @method('PATCH')
            </form>

          @elseif($pemakaian->status === 'approved')
            <div style="text-align:center;padding:16px 0;">
              <div style="width:56px;height:56px;background:#D1FAE5;border-radius:50%;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="bi bi-check-circle-fill" style="font-size:24px;color:#059669;"></i>
              </div>
              <div style="font-weight:700;color:#065F46;margin-bottom:4px;">Permintaan Disetujui</div>
              <div style="font-size:12px;color:#94A3B8;">
                oleh {{ $pemakaian->approvedBy->name ?? '-' }}<br>
                {{ $pemakaian->approved_at?->locale('id')->isoFormat('D MMM Y, HH:mm') ?? '-' }}
              </div>
            </div>

          @else
            <div style="text-align:center;padding:16px 0;">
              <div style="width:56px;height:56px;background:#FEE2E2;border-radius:50%;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="bi bi-x-circle-fill" style="font-size:24px;color:#DC2626;"></i>
              </div>
              <div style="font-weight:700;color:#991B1B;margin-bottom:4px;">Permintaan Ditolak</div>
              <div style="font-size:12px;color:#94A3B8;">
                oleh {{ $pemakaian->approvedBy->name ?? '-' }}<br>
                {{ $pemakaian->approved_at?->locale('id')->isoFormat('D MMM Y, HH:mm') ?? '-' }}
              </div>
            </div>
          @endif

          <hr style="border:none;border-top:1px solid #F1F5F9;margin:16px 0;">
          <a href="{{ route('admin.pemakaian.index') }}"
             style="display:flex;align-items:center;justify-content:center;gap:6px;
                    color:#64748B;font-size:13px;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
          </a>
        </div>
      </div>
    </div>

  </div>

@endsection
