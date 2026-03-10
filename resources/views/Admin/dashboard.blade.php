@extends('layouts.admin')

@section('title', 'Dashboard Divisi Umum')

@section('topbar-title')
  Portal <span>Divisi Umum</span>
@endsection

@section('content')
{{--
  ┌──────────────────────────────────────────────────────────────┐
  │  BARU — admin/dashboard.blade.php                            │
  │  Dashboard utama Level 2. Menampilkan statistik stok,        │
  │  jumlah permintaan yang menunggu, dan tabel aktivitas        │
  │  terbaru dari seluruh permintaan karyawan.                   │
  └──────────────────────────────────────────────────────────────┘
--}}

  {{-- PAGE HEADER --}}
  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span>
      <span class="sep">/</span>
      <span class="current">Dashboard Divisi Umum</span>
    </div>
    <h2>Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
    <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }} · Portal Admin Gudang — Divisi Umum BPS.</p>
  </div>

  {{-- STAT CARDS ROW 1: Inventori Barang --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">
    Inventori Barang
  </div>
  <div class="stat-grid" style="margin-bottom:24px;">
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

  {{-- STAT CARDS ROW 2: Permintaan Menunggu --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">
    Permintaan Menunggu Tindakan
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:32px;">

    <a href="{{ route('admin.pemakaian.index', ['status' => 'pending']) }}" style="text-decoration:none;">
      <div class="stat-card" style="border-left:4px solid #F59E0B;cursor:pointer;transition:transform .15s ease;"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon orange"><i class="bi bi-cart-check"></i></div>
        <div class="stat-info">
          <div class="num" style="color:#D97706;">{{ $pemakaianMenunggu }}</div>
          <div class="lbl">Permintaan Pemakaian</div>
          <div class="change" style="color:#D97706;">
            <span class="pending-dot"></span>Menunggu Persetujuan
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#CBD5E1;margin-left:auto;align-self:center;"></i>
      </div>
    </a>

    <a href="{{ route('admin.pengadaan.index', ['status' => 'pending']) }}" style="text-decoration:none;">
      <div class="stat-card" style="border-left:4px solid #3B82F6;cursor:pointer;transition:transform .15s ease;"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
        <div class="stat-info">
          <div class="num" style="color:#2563EB;">{{ $pengadaanMenunggu }}</div>
          <div class="lbl">Permintaan Pengadaan</div>
          <div class="change" style="color:#2563EB;">
            <span class="pending-dot" style="background:#3B82F6;"></span>Menunggu Persetujuan
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#CBD5E1;margin-left:auto;align-self:center;"></i>
      </div>
    </a>

  </div>

  {{-- TABEL AKTIVITAS TERBARU --}}
  <div class="card">
    <div class="card-header">
      <div>
        <h3>Aktivitas Terbaru</h3>
        <div class="card-sub">Gabungan permintaan pemakaian dan pengadaan dari karyawan</div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Pemohon</th>
            <th>Jenis Permintaan</th>
            <th>Barang</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($aktivitasTerbaru as $aktivitas)
            <tr>
              <td>
                <div style="font-weight:600;color:#1E293B;">{{ $aktivitas['pemohon'] }}</div>
              </td>
              <td>
                @if($aktivitas['jenis'] === 'Pemakaian')
                  <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;
                                background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;
                                padding:3px 10px;border-radius:20px;">
                    <i class="bi bi-cart-check"></i> Pemakaian
                  </span>
                @else
                  <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;
                                background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;
                                padding:3px 10px;border-radius:20px;">
                    <i class="bi bi-bag-check"></i> Pengadaan
                  </span>
                @endif
              </td>
              <td style="font-size:13px;color:#475569;max-width:200px;">
                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $aktivitas['barang'] }}">
                  {{ $aktivitas['barang'] }}
                </div>
              </td>
              <td style="font-size:12px;color:#94A3B8;">
                {{ \Carbon\Carbon::parse($aktivitas['tanggal'])->locale('id')->isoFormat('D MMM Y, HH:mm') }}
              </td>
              <td>
                @php
                  $s = $aktivitas['status'];
                  $labelMap = [
                    'pending'  => ['Menunggu', 'badge-pending'],
                    'approved' => ['Disetujui', 'badge-approved'],
                    'rejected' => ['Ditolak', 'badge-rejected'],
                  ];
                  [$lbl, $cls] = $labelMap[$s] ?? ['-', ''];
                @endphp
                <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
              </td>
              <td>
                <a href="{{ route($aktivitas['route_detail'], $aktivitas['id']) }}" class="btn-detail">
                  <i class="bi bi-eye"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <h4>Belum Ada Aktivitas</h4>
                  <p>Belum ada permintaan yang masuk dari karyawan.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer" style="justify-content:flex-end;">
      <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.pemakaian.index') }}" class="btn-detail">
          <i class="bi bi-cart-check"></i> Lihat Semua Pemakaian
        </a>
        <a href="{{ route('admin.pengadaan.index') }}" class="btn-detail">
          <i class="bi bi-bag-check"></i> Lihat Semua Pengadaan
        </a>
      </div>
    </div>
  </div>

@endsection
