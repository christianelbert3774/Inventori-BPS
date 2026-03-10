@extends('layouts.admin')

@section('title', 'Permintaan Pengadaan Barang')

@section('topbar-title')
  Permintaan <span>Pengadaan</span>
@endsection

@section('content')
{{--
  ┌──────────────────────────────────────────────────────────────┐
  │  BARU — admin/permintaan-pengadaan.blade.php                 │
  │  Halaman daftar permintaan pengadaan dari karyawan.          │
  │  Jika disetujui, status berubah menjadi 'approved'           │
  │  dan diteruskan ke Level 3 (PBJ) untuk ditindaklanjuti.      │
  └──────────────────────────────────────────────────────────────┘
--}}

  {{-- PAGE HEADER --}}
  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Permintaan Pengadaan</span>
    </div>
    <h2>Permintaan Pengadaan Barang</h2>
    <p>Kelola semua permintaan pengadaan barang dari karyawan. Setujui untuk meneruskan ke PBJ.</p>
  </div>

  {{-- FILTER & SEARCH --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="padding:16px 20px;">
      <form method="GET" action="{{ route('admin.pengadaan.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;width:100%;">
        <div style="flex:1;min-width:200px;position:relative;">
          <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pemohon..."
                 style="width:100%;padding:8px 12px 8px 36px;border:1.5px solid #E2E8F0;border-radius:8px;
                        font-size:13px;font-family:inherit;outline:none;"
                 onfocus="this.style.borderColor='#0055A5'" onblur="this.style.borderColor='#E2E8F0'"/>
        </div>
        <div class="filter-bar">
          <a href="{{ route('admin.pengadaan.index', request()->only('q')) }}"
             class="filter-pill {{ !request('status') ? 'active' : '' }}">Semua</a>
          <a href="{{ route('admin.pengadaan.index', array_merge(request()->only('q'), ['status'=>'pending'])) }}"
             class="filter-pill pending {{ request('status')==='pending' ? 'active' : '' }}">Menunggu</a>
          <a href="{{ route('admin.pengadaan.index', array_merge(request()->only('q'), ['status'=>'approved'])) }}"
             class="filter-pill approved {{ request('status')==='approved' ? 'active' : '' }}">Diteruskan ke PBJ</a>
          <a href="{{ route('admin.pengadaan.index', array_merge(request()->only('q'), ['status'=>'rejected'])) }}"
             class="filter-pill rejected {{ request('status')==='rejected' ? 'active' : '' }}">Ditolak</a>
        </div>
        <button type="submit" style="padding:8px 18px;background:#0055A5;color:#fff;border:none;
                border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
          <i class="bi bi-search"></i> Cari
        </button>
      </form>
    </div>
  </div>

  {{-- TABEL --}}
  <div class="card">
    <div class="card-header">
      <div>
        <h3>Daftar Permintaan Pengadaan</h3>
        <div class="card-sub">Total {{ $pengadaans->total() }} permintaan ditemukan</div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Pemohon</th>
            <th>Tanggal</th>
            <th>Barang yang Diminta</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengadaans as $idx => $pengadaan)
            <tr>
              <td style="color:#94A3B8;font-size:12px;">{{ $pengadaans->firstItem() + $idx }}</td>
              <td>
                <div style="font-weight:600;color:#1E293B;">{{ $pengadaan->user->name ?? '-' }}</div>
                <div style="font-size:11px;color:#94A3B8;">{{ $pengadaan->user->email ?? '' }}</div>
              </td>
              <td style="font-size:12px;color:#64748B;white-space:nowrap;">
                {{ $pengadaan->created_at->locale('id')->isoFormat('D MMM Y') }}<br>
                <span style="color:#94A3B8;">{{ $pengadaan->created_at->format('H:i') }}</span>
              </td>
              <td style="max-width:200px;">
                @foreach($pengadaan->details as $detail)
                  <div style="font-size:12px;color:#475569;display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                    <i class="bi bi-box-arrow-up" style="color:#0055A5;font-size:10px;"></i>
                    <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                  </div>
                @endforeach
              </td>
              <td>
                @foreach($pengadaan->details as $detail)
                  <span style="background:#EFF6FF;color:#1D4ED8;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600;">
                    {{ $detail->jumlah }} {{ $detail->barang->satuan ?? '' }}
                  </span>
                @endforeach
              </td>
              <td>
                @php
                  $sMap = [
                    'pending'  => ['Menunggu', 'badge-pending', 'bi-clock'],
                    'approved' => ['Diteruskan ke PBJ', 'badge-forwarded', 'bi-send'],
                    'rejected' => ['Ditolak', 'badge-rejected', 'bi-x-circle'],
                  ];
                  [$lbl, $cls, $ico] = $sMap[$pengadaan->status_level2] ?? ['-', '', ''];
                @endphp
                <span class="status-badge {{ $cls }}">
                  <i class="bi {{ $ico }}"></i> {{ $lbl }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                  <a href="{{ route('admin.pengadaan.show', $pengadaan->id) }}" class="btn-detail">
                    <i class="bi bi-eye"></i> Detail
                  </a>

                  @if($pengadaan->status_level2 === 'pending')
                    {{-- Terima & Teruskan ke PBJ --}}
                    <button type="button" class="btn-approve"
                      onclick="showConfirm({
                        title: 'Teruskan ke PBJ?',
                        message: 'Permintaan pengadaan ini akan disetujui dan diteruskan ke PBJ untuk ditindaklanjuti.',
                        icon: 'bi-send-fill', iconColor: '#059669',
                        confirmText: 'Ya, Teruskan', confirmClass: 'confirm-btn-success',
                        onConfirm: function() {
                          document.getElementById('form-approve-pgd-{{ $pengadaan->id }}').submit();
                        }
                      })">
                      <i class="bi bi-send"></i> Teruskan
                    </button>
                    <form id="form-approve-pgd-{{ $pengadaan->id }}"
                          method="POST" action="{{ route('admin.pengadaan.approve', $pengadaan->id) }}" style="display:none;">
                      @csrf @method('PATCH')
                    </form>

                    {{-- Tolak --}}
                    <button type="button" class="btn-reject"
                      onclick="showConfirm({
                        title: 'Tolak Permintaan?',
                        message: 'Permintaan pengadaan ini akan ditolak.',
                        icon: 'bi-x-circle-fill', iconColor: '#DC2626',
                        confirmText: 'Ya, Tolak', confirmClass: 'confirm-btn-danger',
                        onConfirm: function() {
                          document.getElementById('form-reject-pgd-{{ $pengadaan->id }}').submit();
                        }
                      })">
                      <i class="bi bi-x-lg"></i> Tolak
                    </button>
                    <form id="form-reject-pgd-{{ $pengadaan->id }}"
                          method="POST" action="{{ route('admin.pengadaan.reject', $pengadaan->id) }}" style="display:none;">
                      @csrf @method('PATCH')
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <h4>Tidak Ada Permintaan</h4>
                  <p>Belum ada permintaan pengadaan yang sesuai filter.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($pengadaans->total() > 0)
    <div class="card-footer">
      <span style="font-size:13px;color:var(--text-secondary)">
        Menampilkan {{ $pengadaans->firstItem() }}–{{ $pengadaans->lastItem() }} dari {{ $pengadaans->total() }} permintaan
      </span>
      <div class="pagination">
        @if($pengadaans->onFirstPage())
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
        @else
          <a href="{{ $pengadaans->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
        @endif
        @foreach($pengadaans->getUrlRange(1, $pengadaans->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="pg-btn {{ $page == $pengadaans->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        @if($pengadaans->hasMorePages())
          <a href="{{ $pengadaans->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
        @else
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>
        @endif
      </div>
    </div>
    @endif
  </div>

@endsection
