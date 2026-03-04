@extends('layouts.app')

@section('title', 'Riwayat Permintaan')
@section('page-title', 'Riwayat <span>Permintaan Saya</span>')

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Riwayat Permintaan</span>
  </div>
  <h2>Riwayat Permintaan Saya</h2>
  <p>Daftar semua permintaan pemakaian yang pernah Anda ajukan.</p>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <h3>Permintaan Pemakaian Barang</h3>
      <div class="card-sub">{{ $pemakaians->total() }} total permintaan</div>
    </div>
    <a href="{{ route('karyawan.pemakaian.create') }}" class="btn-action btn-primary">
      <i class="bi bi-plus"></i> Ajukan Baru
    </a>
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
            <td class="text-muted text-sm font-mono">{{ $p->id }}</td>
            <td class="text-sm">{{ $p->created_at->format('d M Y H:i') }}</td>
            <td>
              @foreach($p->details as $d)
                <div class="text-sm">{{ $d->barang->nama_barang }} <span class="text-muted">× {{ $d->jumlah }}</span></div>
              @endforeach
            </td>
            <td>
              @switch($p->status)
                @case('pending')
                  <span class="badge-status badge-low">Menunggu</span> @break
                @case('approved')
                  <span class="badge-status badge-available">Disetujui</span> @break
                @case('rejected')
                  <span class="badge-status badge-empty">Ditolak</span> @break
              @endswitch
            </td>
            <td class="text-sm text-muted">
              {{ $p->approvedBy?->name ?? '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty-state">
              <i class="bi bi-inbox" style="font-size:36px;color:var(--border)"></i>
              <p>Belum ada riwayat permintaan.</p>
              <a href="{{ route('karyawan.pemakaian.create') }}" class="btn-action btn-primary" style="margin-top:12px">
                <i class="bi bi-plus"></i> Ajukan Sekarang
              </a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($pemakaians->hasPages())
    <div class="card-footer">
      {{ $pemakaians->links('vendor.pagination.simple') }}
    </div>
  @endif
</div>
@endsection
