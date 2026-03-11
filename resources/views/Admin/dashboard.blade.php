@extends('layouts.admin')
@section('title', 'Dashboard Divisi Umum')
@section('topbar-title') Portal <span>Divisi Umum</span> @endsection

@section('content')
{{--
  DIMODIFIKASI — admin/dashboard.blade.php
  Perubahan: Tambah tabel stok barang di bagian bawah
  (mirip dashboard karyawan Level 1)
--}}

  <div class="page-header">
    <div class="breadcrumb"><span>SIBAS</span><span class="sep">/</span><span class="current">Dashboard</span></div>
    <h2>Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
    <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }} · Sistem Inventori - Divisi Umum</p>
  </div>

  {{-- STAT ROW 1: Inventori --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">Inventori Barang</div>
  <div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalBarang }}</div><div class="lbl">Total Jenis Barang</div>
        <div class="change up">Terdaftar di inventori</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangTersedia }}</div><div class="lbl">Barang Tersedia</div>
        <div class="change up">Stok > 10 unit</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangHampirHabis }}</div><div class="lbl">Hampir Habis</div>
        <div class="change down">Stok ≤ 10 unit</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $barangHabis }}</div><div class="lbl">Stok Habis</div>
        <div class="change down">Perlu pengadaan</div>
      </div>
    </div>
  </div>

  {{-- STAT ROW 2: Permintaan Menunggu --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">Permintaan Menunggu Tindakan</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:32px;">
    <a href="{{ route('admin.pemakaian.index', ['status'=>'pending']) }}" style="text-decoration:none;">
      <div class="stat-card" style="border-left:4px solid #F59E0B;cursor:pointer;transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon orange"><i class="bi bi-cart-check"></i></div>
        <div class="stat-info">
          <div class="num" style="color:#D97706;">{{ $pemakaianMenunggu }}</div>
          <div class="lbl">Permintaan Pemakaian</div>
          <div class="change" style="color:#D97706;"><span class="pending-dot"></span>Menunggu Persetujuan</div>
        </div>
        <i class="bi bi-chevron-right" style="color:#CBD5E1;margin-left:auto;align-self:center;"></i>
      </div>
    </a>
    <a href="{{ route('admin.pengadaan.index', ['status'=>'pending']) }}" style="text-decoration:none;">
      <div class="stat-card" style="border-left:4px solid #3B82F6;cursor:pointer;transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
        <div class="stat-info">
          <div class="num" style="color:#2563EB;">{{ $pengadaanMenunggu }}</div>
          <div class="lbl">Permintaan Pengadaan</div>
          <div class="change" style="color:#2563EB;"><span class="pending-dot" style="background:#3B82F6;"></span>Menunggu Persetujuan</div>
        </div>
        <i class="bi bi-chevron-right" style="color:#CBD5E1;margin-left:auto;align-self:center;"></i>
      </div>
    </a>
  </div>

  {{-- TABEL STOK BARANG --}}
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header">
      <div>
        <h3>Stok Barang Inventori</h3>
        <div class="card-sub">{{ $totalBarang }} jenis barang terdaftar</div>
      </div>
      <div class="card-actions">
        <div class="filter-tabs">
          <a href="{{ route('admin.dashboard') }}" class="filter-tab {{ !request('filter') ? 'active' : '' }}">Semua</a>
          <a href="{{ route('admin.dashboard', ['filter'=>'tersedia']) }}" class="filter-tab {{ request('filter')==='tersedia' ? 'active' : '' }}">Tersedia</a>
          <a href="{{ route('admin.dashboard', ['filter'=>'hampir_habis']) }}" class="filter-tab {{ request('filter')==='hampir_habis' ? 'active' : '' }}">Hampir Habis</a>
          <a href="{{ route('admin.dashboard', ['filter'=>'habis']) }}" class="filter-tab {{ request('filter')==='habis' ? 'active' : '' }}">Habis</a>
        </div>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Nama Barang</th><th>Kode</th><th>Stok</th><th>Satuan</th><th>Status</th></tr>
        </thead>
        <tbody>
          @forelse($barangs as $barang)
            @php
              $pct = $barang->stok > 0 ? min(100, ($barang->stok / max($barang->stok,50))*100) : 0;
              if ($barang->stok==0)      { $sc='badge-empty';     $sl='Habis';       $fc='fill-red'; }
              elseif($barang->stok<=10)  { $sc='badge-low';       $sl='Hampir Habis';$fc='fill-orange'; }
              else                       { $sc='badge-available'; $sl='Tersedia';    $fc='fill-green'; }
            @endphp
            <tr>
              <td><div class="item-name">{{ $barang->nama_barang }}</div></td>
              <td><div class="item-code">{{ $barang->kode_barang }}</div></td>
              <td>
                <div class="stock-bar-wrap">
                  <div class="stock-bar"><div class="stock-bar-fill {{ $fc }}" style="width:{{ $pct }}%"></div></div>
                  <span class="stock-num">{{ $barang->stok }}</span>
                </div>
              </td>
              <td style="color:var(--text-secondary);font-size:13px">{{ $barang->satuan }}</td>
              <td><span class="badge-status {{ $sc }}">{{ $sl }}</span></td>
            </tr>
          @empty
            <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i><h4>Belum Ada Barang</h4></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($barangs->total() > 0)
    <div class="card-footer">
      <span style="font-size:13px;color:var(--text-secondary)">Menampilkan {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }} dari {{ $barangs->total() }} barang</span>
      <div class="pagination">
        @if($barangs->onFirstPage())<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
        @else<a href="{{ $barangs->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>@endif
        @foreach($barangs->getUrlRange(1,$barangs->lastPage()) as $page=>$url)
          <a href="{{ $url }}" class="pg-btn {{ $page==$barangs->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($barangs->hasMorePages())<a href="{{ $barangs->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
        @else<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>@endif
      </div>
    </div>
    @endif
  </div>

  {{-- AKTIVITAS TERBARU --}}
  <div class="card">
    <div class="card-header">
      <div><h3>Aktivitas Terbaru</h3><div class="card-sub">Gabungan permintaan dari karyawan</div></div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Nama Pemohon</th><th>Jenis</th><th>Barang</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          @forelse($aktivitasTerbaru as $a)
            <tr>
              <td><div style="font-weight:600;color:#1E293B;">{{ $a['pemohon'] }}</div></td>
              <td>
                @if($a['jenis']==='Pemakaian')
                  <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;padding:3px 10px;border-radius:20px;"><i class="bi bi-cart-check"></i> Pemakaian</span>
                @else
                  <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;padding:3px 10px;border-radius:20px;"><i class="bi bi-bag-check"></i> Pengadaan</span>
                @endif
              </td>
              <td style="font-size:13px;color:#475569;max-width:180px;"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $a['barang'] }}</div></td>
              <td style="font-size:12px;color:#94A3B8;">{{ \Carbon\Carbon::parse($a['tanggal'])->locale('id')->isoFormat('D MMM Y') }}</td>
              <td>
                @php $sMap=['pending'=>['Menunggu','badge-pending'],'approved'=>['Disetujui','badge-approved'],'rejected'=>['Ditolak','badge-rejected']];[$lbl,$cls]=$sMap[$a['status']]??['-',''];@endphp
                <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
              </td>
              <td><a href="{{ route($a['route_detail'], $a['id']) }}" class="btn-detail"><i class="bi bi-eye"></i> Detail</a></td>
            </tr>
          @empty
            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><h4>Belum Ada Aktivitas</h4></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer" style="justify-content:flex-end;">
      <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.pemakaian.index') }}" class="btn-detail"><i class="bi bi-cart-check"></i> Semua Pemakaian</a>
        <a href="{{ route('admin.pengadaan.index') }}" class="btn-detail"><i class="bi bi-bag-check"></i> Semua Pengadaan</a>
      </div>
    </div>
  </div>

@endsection
