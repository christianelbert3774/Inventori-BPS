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
                    <span class="item-name">{{ $d->barang->nama_barang }}</span>
                    <span style="color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->barang->satuan }}</span>
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
                @if($p->status_level3 === 'completed')
                  <span class="badge-status badge-approved">Selesai</span>
                @else
                  <span class="badge-status badge-pending">Menunggu</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-bag-x"></i>
                  <h4>Belum Ada Permintaan Pengadaan</h4>
                  <p>Anda belum pernah mengajukan permintaan pengadaan barang.</p>
                  <a href="{{ route('karyawan.pengadaan.create') }}" class="btn-action btn-orange" style="margin-top:12px;display:inline-flex">
                    <i class="bi bi-plus"></i> Ajukan Sekarang
                  </a>
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
          @if(!$pengadaans->onFirstPage())
            <a href="{{ $pengadaans->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
          @endif
          @foreach($pengadaans->getUrlRange(1, $pengadaans->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="pg-btn {{ $page == $pengadaans->currentPage() ? 'active' : '' }}">{{ $page }}</a>
          @endforeach
          @if($pengadaans->hasMorePages())
            <a href="{{ $pengadaans->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
          @endif
        </div>
      </div>
    @endif
  </div>
@endsection
