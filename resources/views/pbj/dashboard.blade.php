@extends('layouts.pbj')
@section('title', 'Dashboard PBJ')
@section('topbar-title') Portal <span>Pejabat Pengadaan</span> @endsection

@section('content')

  <div class="page-header">
    <div class="breadcrumb"><span>SIBAS</span><span class="sep">/</span><span class="current">Dashboard</span></div>
    <h2>Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
    <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }} · Sistem Inventori - Pejabat Pengadaan</p>
  </div>

  {{-- STAT CARDS --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">Ringkasan Pengadaan</div>
  <div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalMasuk }}</div><div class="lbl">Total Pengadaan Masuk</div>
        <div class="change up">Sudah disetujui Divisi Umum</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-info">
        <div class="num" style="color:#D97706;">{{ $pending }}</div><div class="lbl">Menunggu Diproses</div>
        <div class="change" style="color:#D97706;"><span class="pending-dot"></span>Perlu ditindaklanjuti</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $selesai }}</div><div class="lbl">Selesai Diproses</div>
        <div class="change up">Sudah diselesaikan</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $ditolak }}</div><div class="lbl">Ditolak</div>
        <div class="change down">Tidak dapat diproses</div>
      </div>
    </div>
  </div>

  {{-- QUICK ACTIONS --}}
  <div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.8px;">Aksi Cepat</div>
  <div class="quick-actions" style="margin-bottom:24px;">
    <a href="{{ route('pbj.pengadaan.index', ['status' => 'pending']) }}" class="qa-card">
      <div class="qa-icon orange"><i class="bi bi-hourglass-split"></i></div>
      <div class="qa-info">
        <h4>Lihat Pengadaan Menunggu</h4>
        <p>{{ $pending }} permintaan yang perlu ditindaklanjuti</p>
      </div>
      <i class="bi bi-chevron-right" style="color:var(--text-secondary);margin-left:auto"></i>
    </a>
    <a href="{{ route('pbj.pengadaan.index') }}" class="qa-card">
      <div class="qa-icon blue"><i class="bi bi-list-ul"></i></div>
      <div class="qa-info">
        <h4>Semua Pengadaan</h4>
        <p>Lihat seluruh riwayat dan status pengadaan</p>
      </div>
      <i class="bi bi-chevron-right" style="color:var(--text-secondary);margin-left:auto"></i>
    </a>
  </div>

  {{-- PENGADAAN TERBARU --}}
  <div class="card">
    <div class="card-header">
      <div><h3>Pengadaan Terbaru</h3><div class="card-sub">Daftar permintaan pengadaan yang masuk</div></div>
      <div class="card-actions">
        <a href="{{ route('pbj.pengadaan.index') }}" class="btn-detail"><i class="bi bi-list-ul"></i> Lihat Semua</a>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Pemohon</th><th>Barang</th><th>Tanggal</th><th>Status PBJ</th><th>Verifikasi Admin</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          @forelse($terbaru as $p)
            <tr>
              <td style="color:#94A3B8;">{{ $p->id }}</td>
              <td><div style="font-weight:600;color:#1E293B;">{{ $p->user->name ?? '-' }}</div></td>
              <td style="font-size:13px;color:#475569;max-width:200px;">
                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  {{ $p->details->map(fn($d) => $d->tipe_item === 'baru' ? $d->nama_barang_baru : ($d->barang->nama_barang ?? '-'))->implode(', ') }}
                </div>
              </td>
              <td style="font-size:12px;color:#94A3B8;">{{ $p->created_at->format('d/m/Y H:i') }}</td>
              <td>
                @php
                  $map = ['pending'=>['Menunggu','badge-pending'], 'completed'=>['Selesai','badge-approved'], 'rejected'=>['Ditolak','badge-rejected']];
                  [$lbl, $cls] = $map[$p->status_level3] ?? ['-',''];
                @endphp
                <span class="status-badge {{ $cls }}">{{ $lbl }}</span>
              </td>
              <td>
                @if($p->status_level3 === 'completed')
                  @php
                    $vMap = ['belum'=>['Menunggu Verifikasi','badge-verify'], 'verified'=>['Terverifikasi','badge-approved'], 'rejected'=>['Ditolak','badge-rejected']];
                    [$vLbl, $vCls] = $vMap[$p->status_admin_verifikasi] ?? ['—',''];
                  @endphp
                  <span class="status-badge {{ $vCls }}">{{ $vLbl }}</span>
                @else
                  <span style="color:#94A3B8;font-size:12px;">—</span>
                @endif
              </td>
              <td>
                <a href="{{ route('pbj.pengadaan.show', $p->id) }}" class="btn-detail">
                  <i class="bi bi-eye"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><h4>Belum Ada Pengadaan</h4><p>Belum ada pengadaan yang masuk untuk diproses.</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
