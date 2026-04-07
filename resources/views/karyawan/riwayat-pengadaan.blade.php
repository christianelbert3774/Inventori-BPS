@extends('layouts.app')

@section('title', 'Riwayat Pengadaan')

@section('topbar-title')
  Riwayat <span>Permintaan Pengadaan</span>
@endsection

@section('content')
  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Riwayat Pengadaan</span>
    </div>
    <h2>Riwayat Permintaan Pengadaan</h2>
    <p>Daftar semua permintaan pengadaan barang yang pernah Anda ajukan.</p>
  </div>

  {{-- SUMMARY STATS --}}
  @php
    $allPengadaans  = \App\Models\Pengadaan::where('user_id', auth()->id());
    $totalSemua     = (clone $allPengadaans)->count();
    $totalPending   = (clone $allPengadaans)->where('status_level2', 'pending')->count();
    $totalApproved  = (clone $allPengadaans)->where('status_level2', 'approved')->count();
    $totalRejected  = (clone $allPengadaans)->where('status_level2', 'rejected')->count();
  @endphp
  <div class="stat-grid" style="margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-bag-check"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalSemua }}</div><div class="lbl">Total Pengadaan</div>
        <div class="change up">Semua permintaan</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-info">
        <div class="num" style="color:#D97706;">{{ $totalPending }}</div><div class="lbl">Menunggu</div>
        <div class="change" style="color:#D97706;">Belum diproses</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalApproved }}</div><div class="lbl">Disetujui</div>
        <div class="change up">Diteruskan ke PBJ</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalRejected }}</div><div class="lbl">Ditolak</div>
        <div class="change down">Tidak disetujui</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h3>Daftar Permintaan Pengadaan</h3>
        <div class="card-sub">{{ $pengadaans->total() }} permintaan ditemukan</div>
      </div>
      <div class="card-actions">
        <a href="{{ route('karyawan.pengadaan.create') }}" class="btn-action btn-orange">
          <i class="bi bi-plus"></i> Ajukan Baru
        </a>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Status Divisi Umum</th>
            <th>Status PBJ</th>
            <th style="width:70px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengadaans as $p)
            <tr>
              <td style="font-family:'DM Mono',monospace;font-size:12px;color:var(--text-secondary)">
                #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
              </td>
              <td style="font-size:13px;color:var(--text-secondary)">
                {{ $p->created_at->format('d M Y, H:i') }}
              </td>
              <td>
                @foreach($p->details as $d)
                  <div style="font-size:13px">
                    @if($d->tipe_item === 'baru' && !$d->barang_id)
                      <span class="item-name">{{ $d->nama_barang_baru }}</span>
                      <span style="color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->satuan_baru }}</span>
                      <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;background:#FEF3C7;color:#92400E;margin-left:4px;">BARU</span>
                    @else
                      <span class="item-name">{{ $d->barang->nama_barang ?? '-' }}</span>
                      <span style="color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->barang->satuan ?? '' }}</span>
                    @endif
                  </div>
                @endforeach
              </td>
              <td>
                @if($p->status_level2 === 'pending')
                  <span class="badge-status badge-pending">Menunggu</span>
                @elseif($p->status_level2 === 'approved')
                  <span class="badge-status badge-approved">Disetujui</span>
                @else
                  <span class="badge-status badge-rejected">Ditolak</span>
                @endif
              </td>
              <td>
                @if($p->status_level2 === 'rejected')
                  <span style="color:var(--text-secondary);font-size:12px;">—</span>
                @elseif($p->status_level2 === 'pending')
                  <span style="color:var(--text-secondary);font-size:12px;">—</span>
                @elseif($p->status_level3 === 'completed')
                  @if($p->status_admin_verifikasi === 'verified')
                    <span class="badge-status badge-approved">Terverifikasi</span>
                  @elseif($p->status_admin_verifikasi === 'rejected')
                    <span class="badge-status badge-rejected">Verifikasi Ditolak</span>
                  @else
                    <span class="badge-status badge-pending">Menunggu Verifikasi</span>
                  @endif
                @elseif($p->status_level3 === 'rejected')
                  <span class="badge-status badge-rejected">Ditolak PBJ</span>
                @else
                  <span class="badge-status badge-pending">Diproses PBJ</span>
                @endif
              </td>
              <td>
                <a href="{{ route('karyawan.pengadaan.print', $p->id) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:7px;font-size:11px;font-weight:600;background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;text-decoration:none;transition:all .15s ease;"
                   onmouseover="this.style.background='#BFDBFE'" onmouseout="this.style.background='#EFF6FF'"
                   title="Print permintaan ini">
                  🖨️
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="empty-state" style="padding:40px 24px;">
                  <div style="width:64px;height:64px;border-radius:50%;background:rgba(240,125,0,.06);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <i class="bi bi-bag-x" style="font-size:28px;opacity:.4;margin:0;display:block;"></i>
                  </div>
                  <h4 style="font-size:14px;">Belum Ada Permintaan Pengadaan</h4>
                  <p style="max-width:300px;margin:0 auto 14px;font-size:12.5px;">Anda belum pernah mengajukan permintaan pengadaan barang. Mulai dengan mengajukan permintaan baru.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($pengadaans->hasPages())
      <div class="card-footer">
        <span style="font-size:13px;color:var(--text-secondary)">
          Menampilkan {{ $pengadaans->firstItem() }}–{{ $pengadaans->lastItem() }} dari {{ $pengadaans->total() }}
        </span>
        <div class="pagination">
          @if($pengadaans->onFirstPage())<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
          @else<a href="{{ $pengadaans->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>@endif
          @foreach($pengadaans->getUrlRange(1, $pengadaans->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="pg-btn {{ $page == $pengadaans->currentPage() ? 'active' : '' }}">{{ $page }}</a>
          @endforeach
          @if($pengadaans->hasMorePages())<a href="{{ $pengadaans->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
          @else<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>@endif
        </div>
      </div>
    @endif
  </div>
@endsection
