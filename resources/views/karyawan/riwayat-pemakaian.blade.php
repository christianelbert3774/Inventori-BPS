@extends('layouts.app')

@section('title', 'Riwayat Pemakaian')

@section('topbar-title')
  Riwayat <span>Permintaan Pemakaian</span>
@endsection

@section('content')
  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Riwayat Pemakaian</span>
    </div>
    <h2>Riwayat Permintaan Pemakaian</h2>
    <p>Daftar semua permintaan pemakaian barang yang pernah Anda ajukan.</p>
  </div>

  {{-- SUMMARY STATS --}}
  @php
    $allPemakaians = \App\Models\Pemakaian::where('user_id', auth()->id());
    $totalSemua = (clone $allPemakaians)->count();
    $totalPending = (clone $allPemakaians)->where('status', 'pending')->count();
    $totalApproved = (clone $allPemakaians)->where('status', 'approved')->count();
    $totalRejected = (clone $allPemakaians)->where('status', 'rejected')->count();
  @endphp
  <div class="stat-grid" style="margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="bi bi-cart-check"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalSemua }}</div>
        <div class="lbl">Total Permintaan</div>
        <div class="change up">Semua pemakaian</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-info">
        <div class="num" style="color:#D97706;">{{ $totalPending }}</div>
        <div class="lbl">Menunggu</div>
        <div class="change" style="color:#D97706;">Belum diproses</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalApproved }}</div>
        <div class="lbl">Disetujui</div>
        <div class="change up">Berhasil diproses</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-info">
        <div class="num">{{ $totalRejected }}</div>
        <div class="lbl">Ditolak</div>
        <div class="change down">Tidak disetujui</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h3>Daftar Permintaan Pemakaian</h3>
        <div class="card-sub">{{ $pemakaians->total() }} permintaan ditemukan</div>
      </div>
      <div class="card-actions">
        <a href="{{ route('karyawan.pemakaian.create') }}" class="btn-action btn-primary">
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
            <th>Status</th>
            <th>Diproses Oleh</th>
            <th style="width:70px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pemakaians as $p)
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
                    <span class="item-name">{{ $d->barang->nama_barang }}</span>
                    <span style="color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->barang->satuan }}</span>
                  </div>
                @endforeach
              </td>
              <td>
                @if($p->status === 'pending')
                  <span class="badge-status badge-pending">Menunggu</span>
                @elseif($p->status === 'approved')
                  <span class="badge-status badge-approved">Disetujui</span>
                @else
                  <span class="badge-status badge-rejected">Ditolak</span>
                @endif
              </td>
              <td style="font-size:13px;color:var(--text-secondary)">
                {{ $p->approvedBy?->name ?? '—' }}
                @if($p->approved_at)
                  <div style="font-size:11px">{{ \Carbon\Carbon::parse($p->approved_at)->format('d M Y') }}</div>
                @endif
              </td>
              <td>
                <a href="{{ route('karyawan.pemakaian.print', $p->id) }}" target="_blank"
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
                  <div
                    style="width:64px;height:64px;border-radius:50%;background:rgba(0,85,165,.06);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <i class="bi bi-cart-x" style="font-size:28px;opacity:.4;margin:0;display:block;"></i>
                  </div>
                  <h4 style="font-size:14px;">Belum Ada Permintaan</h4>
                  <p style="max-width:300px;margin:0 auto 14px;font-size:12.5px;">Anda belum pernah mengajukan permintaan
                    pemakaian barang. Mulai dengan mengajukan permintaan baru.</p>
                  <a href="{{ route('karyawan.pemakaian.create') }}" class="btn-action btn-primary"
                    style="padding:4px 10px;font-size:10.5px;gap:3px;border-radius:6px;">
                    <i class="bi bi-plus" style="font-size:12px;"></i> Ajukan Sekarang
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($pemakaians->hasPages())
      <div class="card-footer">
        <span style="font-size:13px;color:var(--text-secondary)">
          Menampilkan {{ $pemakaians->firstItem() }}–{{ $pemakaians->lastItem() }} dari {{ $pemakaians->total() }}
        </span>
        <div class="pagination">
          @if($pemakaians->onFirstPage())<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
          @else<a href="{{ $pemakaians->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>@endif
          @foreach($pemakaians->getUrlRange(1, $pemakaians->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="pg-btn {{ $page == $pemakaians->currentPage() ? 'active' : '' }}">{{ $page }}</a>
          @endforeach
          @if($pemakaians->hasMorePages())<a href="{{ $pemakaians->nextPageUrl() }}" class="pg-btn"><i
            class="bi bi-chevron-right"></i></a>
          @else<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>@endif
        </div>
      </div>
    @endif
  </div>
@endsection