@extends('layouts.admin')

@section('title', 'Detail Permintaan Pengadaan')

@section('topbar-title')
  Detail <span>Permintaan Pengadaan</span>
@endsection

@section('content')
{{--
  ┌──────────────────────────────────────────────────────────────┐
  │  BARU — admin/detail-pengadaan.blade.php                     │
  │  Halaman detail permintaan pengadaan. Menampilkan info       │
  │  pemohon, barang yang ingin diadakan, dan jumlahnya.         │
  │  Admin dapat meneruskan ke PBJ atau menolak dari sini.       │
  └──────────────────────────────────────────────────────────────┘
--}}

  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
      <span class="sep">/</span>
      <a href="{{ route('admin.pengadaan.index') }}" style="color:inherit;text-decoration:none;">Pengadaan</a>
      <span class="sep">/</span>
      <span class="current">Detail #{{ $pengadaan->id }}</span>
    </div>
    <h2>Detail Permintaan Pengadaan</h2>
    <p>Informasi lengkap permintaan pengadaan barang dari karyawan.</p>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- KOLOM KIRI --}}
    <div>
      {{-- Info Pemohon --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3><i class="bi bi-person-circle" style="color:#0055A5;margin-right:8px;"></i>Informasi Pemohon</h3>
        </div>
        <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div>
            <div class="detail-label">Nama Lengkap</div>
            <div class="detail-value">{{ $pengadaan->user->name ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Email</div>
            <div class="detail-value">{{ $pengadaan->user->email ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Bagian / Divisi</div>
            <div class="detail-value">{{ $pengadaan->user->bagian ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Tanggal Pengajuan</div>
            <div class="detail-value">
              {{ $pengadaan->created_at->locale('id')->isoFormat('dddd, D MMMM Y — HH:mm') }}
            </div>
          </div>
          <div>
            <div class="detail-label">Status Level 2 (Divisi Umum)</div>
            <div class="detail-value">
              @php
                $sMap = [
                  'pending'  => ['Menunggu Persetujuan', 'badge-pending'],
                  'approved' => ['Diteruskan ke PBJ', 'badge-forwarded'],
                  'rejected' => ['Ditolak', 'badge-rejected'],
                ];
                [$lbl, $cls] = $sMap[$pengadaan->status_level2] ?? ['-', ''];
              @endphp
              <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
            </div>
          </div>
          <div>
            <div class="detail-label">Status Level 3 (PBJ)</div>
            <div class="detail-value">
              @php
                $s3Map = [
                  'pending'   => ['Menunggu', 'badge-pending'],
                  'completed' => ['Selesai', 'badge-approved'],
                ];
                [$lbl3, $cls3] = $s3Map[$pengadaan->status_level3] ?? ['-', ''];
              @endphp
              <span class="status-badge {{ $cls3 }}">{{ $lbl3 }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Daftar Barang Pengadaan --}}
      <div class="card">
        <div class="card-header">
          <h3><i class="bi bi-bag-plus" style="color:#0055A5;margin-right:8px;"></i>Barang yang Diadakan</h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Barang</th>
                <th>Kode</th>
                <th>Jumlah Diminta</th>
                <th>Stok Sekarang</th>
                <th>Satuan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pengadaan->details as $i => $detail)
                <tr>
                  <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                  <td style="font-weight:600;color:#1E293B;">{{ $detail->barang->nama_barang ?? '-' }}</td>
                  <td><span style="font-family:'DM Mono',monospace;font-size:12px;color:#0055A5;">{{ $detail->barang->kode_barang ?? '-' }}</span></td>
                  <td>
                    <span style="font-weight:700;color:#1D4ED8;">
                      {{ $detail->jumlah }}
                    </span>
                  </td>
                  <td>
                    @php $stok = $detail->barang->stok ?? 0; @endphp
                    <span style="font-weight:600;color:{{ $stok === 0 ? '#DC2626' : ($stok <= 10 ? '#D97706' : '#059669') }}">
                      {{ $stok }}
                    </span>
                  </td>
                  <td style="font-size:12px;color:#64748B;">{{ $detail->barang->satuan ?? '-' }}</td>
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

          @if($pengadaan->status_level2 === 'pending')
            <p style="font-size:13px;color:#64748B;margin-bottom:20px;line-height:1.6;">
              Tinjau permintaan pengadaan ini. Jika disetujui, permintaan akan
              <strong>diteruskan ke PBJ</strong> untuk dilakukan pembelian barang.
            </p>

            {{-- Tombol Teruskan ke PBJ --}}
            <button type="button"
              onclick="showConfirm({
                title: 'Teruskan ke PBJ?',
                message: 'Permintaan pengadaan akan disetujui dan diteruskan ke PBJ untuk ditindaklanjuti.',
                icon: 'bi-send-fill', iconColor: '#059669',
                confirmText: 'Ya, Teruskan ke PBJ', confirmClass: 'confirm-btn-success',
                onConfirm: function() { document.getElementById('form-approve-pgd-detail').submit(); }
              })"
              style="width:100%;padding:12px;border-radius:10px;border:none;background:#059669;
                     color:#fff;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;
                     display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;">
              <i class="bi bi-send-fill"></i> Teruskan ke PBJ
            </button>
            <form id="form-approve-pgd-detail" method="POST"
                  action="{{ route('admin.pengadaan.approve', $pengadaan->id) }}">
              @csrf @method('PATCH')
            </form>

            {{-- Tombol Tolak --}}
            <button type="button"
              onclick="showConfirm({
                title: 'Tolak Permintaan?',
                message: 'Permintaan pengadaan ini akan ditolak.',
                icon: 'bi-x-circle-fill', iconColor: '#DC2626',
                confirmText: 'Ya, Tolak', confirmClass: 'confirm-btn-danger',
                onConfirm: function() { document.getElementById('form-reject-pgd-detail').submit(); }
              })"
              style="width:100%;padding:12px;border-radius:10px;border:none;background:#FEF2F2;
                     color:#DC2626;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;
                     display:flex;align-items:center;justify-content:center;gap:8px;border:1.5px solid #FCA5A5;">
              <i class="bi bi-x-circle-fill"></i> Tolak Permintaan
            </button>
            <form id="form-reject-pgd-detail" method="POST"
                  action="{{ route('admin.pengadaan.reject', $pengadaan->id) }}">
              @csrf @method('PATCH')
            </form>

          @elseif($pengadaan->status_level2 === 'approved')
            <div style="text-align:center;padding:16px 0;">
              <div style="width:56px;height:56px;background:#DBEAFE;border-radius:50%;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="bi bi-send-fill" style="font-size:24px;color:#1D4ED8;"></i>
              </div>
              <div style="font-weight:700;color:#1E40AF;margin-bottom:4px;">Diteruskan ke PBJ</div>
              <div style="font-size:12px;color:#94A3B8;">
                oleh {{ $pengadaan->approvedLevel2By->name ?? '-' }}
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
                oleh {{ $pengadaan->approvedLevel2By->name ?? '-' }}
              </div>
            </div>
          @endif

          <hr style="border:none;border-top:1px solid #F1F5F9;margin:16px 0;">
          <a href="{{ route('admin.pengadaan.index') }}"
             style="display:flex;align-items:center;justify-content:center;gap:6px;
                    color:#64748B;font-size:13px;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
          </a>
        </div>
      </div>
    </div>

  </div>

@endsection
