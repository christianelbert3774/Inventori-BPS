@extends('layouts.admin')

@section('title', 'Permintaan Pemakaian Barang')

@section('topbar-title')
  Permintaan <span>Pemakaian</span>
@endsection

@section('content')
{{--
  ┌──────────────────────────────────────────────────────────────┐
  │  BARU — admin/permintaan-pemakaian.blade.php                 │
  │  Halaman daftar permintaan pemakaian dari karyawan.          │
  │  Admin dapat melihat, menyetujui, atau menolak tiap          │
  │  permintaan. Approve otomatis mengurangi stok barang.        │
  └──────────────────────────────────────────────────────────────┘
--}}

  {{-- PAGE HEADER --}}
  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Permintaan Pemakaian</span>
    </div>
    <h2>Permintaan Pemakaian Barang</h2>
    <p>Kelola semua permintaan pemakaian barang dari karyawan. Setujui untuk mengurangi stok.</p>
  </div>

  {{-- FILTER & SEARCH --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="padding:16px 20px;">
      <form method="GET" action="{{ route('admin.pemakaian.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;width:100%;">
        {{-- Search --}}
        <div style="flex:1;min-width:200px;position:relative;">
          <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pemohon..."
                 style="width:100%;padding:8px 12px 8px 36px;border:1.5px solid #E2E8F0;border-radius:8px;
                        font-size:13px;font-family:inherit;outline:none;transition:border .15s;"
                 onfocus="this.style.borderColor='#0055A5'" onblur="this.style.borderColor='#E2E8F0'"/>
        </div>
        {{-- Filter Status --}}
        <div class="filter-bar">
          <a href="{{ route('admin.pemakaian.index', request()->only('q')) }}"
             class="filter-pill {{ !request('status') ? 'active' : '' }}">Semua</a>
          <a href="{{ route('admin.pemakaian.index', array_merge(request()->only('q'), ['status'=>'pending'])) }}"
             class="filter-pill pending {{ request('status')==='pending' ? 'active' : '' }}">Menunggu</a>
          <a href="{{ route('admin.pemakaian.index', array_merge(request()->only('q'), ['status'=>'approved'])) }}"
             class="filter-pill approved {{ request('status')==='approved' ? 'active' : '' }}">Disetujui</a>
          <a href="{{ route('admin.pemakaian.index', array_merge(request()->only('q'), ['status'=>'rejected'])) }}"
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
        <h3>Daftar Permintaan Pemakaian</h3>
        <div class="card-sub">Total {{ $pemakaians->total() }} permintaan ditemukan</div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Pemohon</th>
            <th>Tanggal</th>
            <th>Barang (Detail)</th>
            <th>Status</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pemakaians as $idx => $pemakaian)
            <tr>
              <td style="color:#94A3B8;font-size:12px;">{{ $pemakaians->firstItem() + $idx }}</td>
              <td>
                <div style="font-weight:600;color:#1E293B;">{{ $pemakaian->user->name ?? '-' }}</div>
                <div style="font-size:11px;color:#94A3B8;">{{ $pemakaian->user->email ?? '' }}</div>
              </td>
              <td style="font-size:12px;color:#64748B;white-space:nowrap;">
                {{ $pemakaian->created_at->locale('id')->isoFormat('D MMM Y') }}<br>
                <span style="color:#94A3B8;">{{ $pemakaian->created_at->format('H:i') }}</span>
              </td>
              <td style="max-width:240px;">
                @foreach($pemakaian->details as $detail)
                  <div style="font-size:12px;color:#475569;display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                    <i class="bi bi-box" style="color:#0055A5;font-size:10px;"></i>
                    <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                    <span style="background:#EFF6FF;color:#1D4ED8;border-radius:6px;padding:1px 6px;font-size:10px;font-weight:600;">
                      {{ $detail->jumlah }} {{ $detail->barang->satuan ?? '' }}
                    </span>
                  </div>
                @endforeach
              </td>
              <td>
                @php
                  $sMap = [
                    'pending'  => ['Menunggu', 'badge-pending', 'bi-clock'],
                    'approved' => ['Disetujui', 'badge-approved', 'bi-check-circle'],
                    'rejected' => ['Ditolak', 'badge-rejected', 'bi-x-circle'],
                  ];
                  [$lbl, $cls, $ico] = $sMap[$pemakaian->status] ?? ['-', '', ''];
                @endphp
                <span class="status-badge {{ $cls }}">
                  <i class="bi {{ $ico }}"></i> {{ $lbl }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                  {{-- Detail --}}
                  <a href="{{ route('admin.pemakaian.show', $pemakaian->id) }}" class="btn-detail">
                    <i class="bi bi-eye"></i> Detail
                  </a>

                  @if($pemakaian->status === 'pending')
                    {{-- Terima --}}
                    <button type="button" class="btn-approve"
                      onclick="showConfirm({
                        title: 'Setujui Permintaan?',
                        message: 'Menyetujui permintaan ini akan otomatis mengurangi stok barang. Lanjutkan?',
                        icon: 'bi-check-circle-fill', iconColor: '#059669',
                        confirmText: 'Ya, Setujui', confirmClass: 'confirm-btn-success',
                        onConfirm: function() {
                          document.getElementById('form-approve-{{ $pemakaian->id }}').submit();
                        }
                      })">
                      <i class="bi bi-check-lg"></i> Terima
                    </button>
                    <form id="form-approve-{{ $pemakaian->id }}"
                          method="POST" action="{{ route('admin.pemakaian.approve', $pemakaian->id) }}" style="display:none;">
                      @csrf @method('PATCH')
                    </form>

                    {{-- Tolak --}}
                    <button type="button" class="btn-reject"
                      onclick="showConfirm({
                        title: 'Tolak Permintaan?',
                        message: 'Permintaan ini akan ditolak dan status berubah menjadi Ditolak.',
                        icon: 'bi-x-circle-fill', iconColor: '#DC2626',
                        confirmText: 'Ya, Tolak', confirmClass: 'confirm-btn-danger',
                        onConfirm: function() {
                          document.getElementById('form-reject-{{ $pemakaian->id }}').submit();
                        }
                      })">
                      <i class="bi bi-x-lg"></i> Tolak
                    </button>
                    <form id="form-reject-{{ $pemakaian->id }}"
                          method="POST" action="{{ route('admin.pemakaian.reject', $pemakaian->id) }}" style="display:none;">
                      @csrf @method('PATCH')
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <h4>Tidak Ada Permintaan</h4>
                  <p>Belum ada permintaan pemakaian yang sesuai filter.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    @if($pemakaians->total() > 0)
    <div class="card-footer">
      <span style="font-size:13px;color:var(--text-secondary)">
        Menampilkan {{ $pemakaians->firstItem() }}–{{ $pemakaians->lastItem() }} dari {{ $pemakaians->total() }} permintaan
      </span>
      <div class="pagination">
        @if($pemakaians->onFirstPage())
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
        @else
          <a href="{{ $pemakaians->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
        @endif
        @foreach($pemakaians->getUrlRange(1, $pemakaians->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="pg-btn {{ $page == $pemakaians->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        @if($pemakaians->hasMorePages())
          <a href="{{ $pemakaians->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
        @else
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>
        @endif
      </div>
    </div>
    @endif
  </div>

@endsection
