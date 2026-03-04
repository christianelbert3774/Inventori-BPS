@extends('layouts.app')

@section('title', 'Dashboard Karyawan')

@section('topbar-title')
  Portal <span>Karyawan</span>
@endsection

@section('content')
  {{-- PAGE HEADER --}}
  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span>
      <span class="sep">/</span>
      <span class="current">Dashboard</span>
    </div>
    <h2>Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
    <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }} · Berikut ringkasan inventori barang saat ini.</p>
  </div>

  {{-- STAT CARDS --}}
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalBarang }}</div>
        <div class="lbl">Total Jenis Barang</div>
        <div class="change up">Terdaftar di inventori</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangTersedia }}</div>
        <div class="lbl">Barang Tersedia</div>
        <div class="change up">Stok > 10 unit</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangHampirHabis }}</div>
        <div class="lbl">Hampir Habis</div>
        <div class="change down">Stok ≤ 10 unit</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangHabis }}</div>
        <div class="lbl">Stok Habis</div>
        <div class="change down">Perlu pengadaan</div>
      </div>
    </div>
  </div>

  {{-- QUICK ACTIONS --}}
  <div class="quick-actions">
    <a href="{{ route('karyawan.pemakaian.create') }}" class="qa-card">
      <div class="qa-icon blue"><i class="bi bi-cart-plus"></i></div>
      <div class="qa-info">
        <h4>Ajukan Permintaan Pemakaian</h4>
        <p>Minta barang dari stok yang tersedia di gudang</p>
      </div>
      <i class="bi bi-chevron-right" style="color:var(--text-secondary);margin-left:auto"></i>
    </a>
    <a href="{{ route('karyawan.pengadaan.create') }}" class="qa-card">
      <div class="qa-icon orange"><i class="bi bi-bag-plus"></i></div>
      <div class="qa-info">
        <h4>Ajukan Permintaan Pengadaan</h4>
        <p>Usulkan restock atau pengadaan barang baru</p>
      </div>
      <i class="bi bi-chevron-right" style="color:var(--text-secondary);margin-left:auto"></i>
    </a>
  </div>

  {{-- STOK TABLE --}}
  <div class="card">
    <div class="card-header">
      <div>
        <h3>Daftar Stok Barang Inventori</h3>
        <div class="card-sub">{{ $totalBarang }} jenis barang terdaftar</div>
      </div>
      <div class="card-actions">
        <div class="filter-tabs">
          <a href="{{ route('karyawan.dashboard') }}"
             class="filter-tab {{ !request('filter') ? 'active' : '' }}">Semua</a>
          <a href="{{ route('karyawan.dashboard', ['filter' => 'tersedia']) }}"
             class="filter-tab {{ request('filter') === 'tersedia' ? 'active' : '' }}">Tersedia</a>
          <a href="{{ route('karyawan.dashboard', ['filter' => 'hampir_habis']) }}"
             class="filter-tab {{ request('filter') === 'hampir_habis' ? 'active' : '' }}">Hampir Habis</a>
          <a href="{{ route('karyawan.dashboard', ['filter' => 'habis']) }}"
             class="filter-tab {{ request('filter') === 'habis' ? 'active' : '' }}">Habis</a>
        </div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Barang</th>
            <th>Kode</th>
            <th>Stok</th>
            <th>Satuan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($barangs as $barang)
            @php
              $pct = $barang->stok > 0 ? min(100, ($barang->stok / max($barang->stok, 50)) * 100) : 0;
              if ($barang->stok == 0) {
                $statusClass = 'badge-empty'; $statusLabel = 'Habis'; $fillClass = 'fill-red';
              } elseif ($barang->stok <= 10) {
                $statusClass = 'badge-low'; $statusLabel = 'Hampir Habis'; $fillClass = 'fill-orange';
              } else {
                $statusClass = 'badge-available'; $statusLabel = 'Tersedia'; $fillClass = 'fill-green';
              }
            @endphp
            <tr>
              <td>
                <div class="item-name">{{ $barang->nama_barang }}</div>
              </td>
              <td>
                <div class="item-code">{{ $barang->kode_barang }}</div>
              </td>
              <td>
                <div class="stock-bar-wrap">
                  <div class="stock-bar">
                    <div class="stock-bar-fill {{ $fillClass }}" style="width:{{ $pct }}%"></div>
                  </div>
                  <span class="stock-num {{ $barang->stok == 0 ? 'style=color:#DC2626' : ($barang->stok <= 10 ? '' : '') }}">
                    {{ $barang->stok }}
                  </span>
                </div>
              </td>
              <td style="color:var(--text-secondary);font-size:13px">{{ $barang->satuan }}</td>
              <td><span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
              <td>
                @if($barang->stok > 0)
                  <a href="{{ route('karyawan.pemakaian.create', ['barang_id' => $barang->id]) }}"
                     class="btn-action btn-outline">
                    <i class="bi bi-plus"></i> Minta
                  </a>
                @else
                  <a href="{{ route('karyawan.pengadaan.create') }}"
                     class="btn-action btn-orange">
                    <i class="bi bi-bag-plus"></i> Adakan
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <h4>Belum Ada Barang</h4>
                  <p>Data barang inventori belum tersedia.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      <span style="font-size:13px;color:var(--text-secondary)">
        Menampilkan {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }} dari {{ $barangs->total() }} barang
      </span>
      <div class="pagination">
        @if($barangs->onFirstPage())
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
        @else
          <a href="{{ $barangs->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
        @endif

        @foreach($barangs->getUrlRange(1, $barangs->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="pg-btn {{ $page == $barangs->currentPage() ? 'active' : '' }}">
            {{ $page }}
          </a>
        @endforeach

        @if($barangs->hasMorePages())
          <a href="{{ $barangs->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
        @else
          <span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>
        @endif
      </div>
    </div>
  </div>
@endsection
