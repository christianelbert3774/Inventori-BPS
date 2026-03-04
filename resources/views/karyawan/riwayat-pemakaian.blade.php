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
            </tr>
          @empty
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-cart-x"></i>
                  <h4>Belum Ada Permintaan</h4>
                  <p>Anda belum pernah mengajukan permintaan pemakaian barang.</p>
                  <a href="{{ route('karyawan.pemakaian.create') }}" class="btn-action btn-primary" style="margin-top:12px;display:inline-flex">
                    <i class="bi bi-plus"></i> Ajukan Sekarang
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
          @if(!$pemakaians->onFirstPage())
            <a href="{{ $pemakaians->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
          @endif
          @foreach($pemakaians->getUrlRange(1, $pemakaians->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="pg-btn {{ $page == $pemakaians->currentPage() ? 'active' : '' }}">{{ $page }}</a>
          @endforeach
          @if($pemakaians->hasMorePages())
            <a href="{{ $pemakaians->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
          @endif
        </div>
      </div>
    @endif
  </div>
@endsection
