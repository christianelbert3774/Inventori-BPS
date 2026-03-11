@extends('layouts.admin')
@section('title', 'Riwayat Karyawan')
@section('topbar-title') Riwayat <span>Permintaan Karyawan</span> @endsection

@section('content')
{{--
  BARU — admin/karyawan-history.blade.php
  Halaman riwayat permintaan per karyawan.
  Filter: jenis (pemakaian/pengadaan) + status.
--}}
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span>
    <a href="{{ route('admin.karyawan.index') }}" style="color:inherit;text-decoration:none;">Karyawan</a>
    <span class="sep">/</span><span class="current">Riwayat {{ $karyawan->name }}</span>
  </div>
  <h2>Riwayat: {{ $karyawan->name }}</h2>
  <p>
    {{ $karyawan->bagian ?? '-' }} · {{ $karyawan->jabatan ?? '-' }}
    @if($karyawan->nip) · NIP {{ $karyawan->nip }} @endif
  </p>
</div>

{{-- FILTER --}}
<div class="card" style="margin-bottom:20px;">
  <div class="card-header" style="padding:16px 20px;">
    <form method="GET" action="{{ route('admin.karyawan.history', $karyawan->id) }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;width:100%;">
      <div class="filter-bar">
        <span style="font-size:12px;font-weight:600;color:#64748B;">Jenis:</span>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('status'))) }}"
           class="filter-pill {{ !request('jenis') || request('jenis')==='semua' ? 'active' : '' }}">Semua</a>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('status'), ['jenis'=>'pemakaian'])) }}"
           class="filter-pill {{ request('jenis')==='pemakaian' ? 'active' : '' }}">Pemakaian</a>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('status'), ['jenis'=>'pengadaan'])) }}"
           class="filter-pill {{ request('jenis')==='pengadaan' ? 'active' : '' }}">Pengadaan</a>
      </div>
      <div class="filter-bar" style="margin-left:16px;">
        <span style="font-size:12px;font-weight:600;color:#64748B;">Status:</span>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('jenis'))) }}"
           class="filter-pill {{ !request('status') ? 'active' : '' }}">Semua</a>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('jenis'), ['status'=>'pending'])) }}"
           class="filter-pill pending {{ request('status')==='pending' ? 'active' : '' }}">Menunggu</a>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('jenis'), ['status'=>'approved'])) }}"
           class="filter-pill approved {{ request('status')==='approved' ? 'active' : '' }}">Disetujui</a>
        <a href="{{ route('admin.karyawan.history', array_merge([$karyawan->id], request()->only('jenis'), ['status'=>'rejected'])) }}"
           class="filter-pill rejected {{ request('status')==='rejected' ? 'active' : '' }}">Ditolak</a>
      </div>
    </form>
  </div>
</div>

{{-- TABEL PEMAKAIAN --}}
@if($jenis === 'semua' || $jenis === 'pemakaian')
<div class="card" style="margin-bottom:20px;">
  <div class="card-header">
    <div><h3><i class="bi bi-cart-check" style="color:#C2410C;margin-right:6px;"></i>Riwayat Pemakaian</h3>
    <div class="card-sub">{{ $pemakaians->count() }} permintaan pemakaian</div></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Tanggal</th><th>Barang</th><th>Status</th><th>Diproses Oleh</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        @forelse($pemakaians as $idx => $p)
          <tr>
            <td style="color:#94A3B8;font-size:12px;">{{ $idx+1 }}</td>
            <td style="font-size:12px;color:#64748B;white-space:nowrap;">
              {{ $p->created_at->locale('id')->isoFormat('D MMM Y') }}<br>
              <span style="color:#94A3B8;">{{ $p->created_at->format('H:i') }}</span>
            </td>
            <td>
              @foreach($p->details as $d)
                <div style="font-size:12px;display:flex;align-items:center;gap:5px;">
                  <i class="bi bi-box" style="color:#0055A5;font-size:10px;"></i>
                  {{ $d->barang->nama_barang ?? '-' }}
                  <span style="background:#EFF6FF;color:#1D4ED8;border-radius:5px;padding:1px 5px;font-size:10px;font-weight:600;">× {{ $d->jumlah }}</span>
                </div>
              @endforeach
            </td>
            <td>
              @php $sm=['pending'=>['Menunggu','badge-pending'],'approved'=>['Disetujui','badge-approved'],'rejected'=>['Ditolak','badge-rejected']];[$l,$c]=$sm[$p->status]??['-',''];@endphp
              <span class="status-badge {{ $c }}">{{ $l }}</span>
            </td>
            <td style="font-size:12px;color:#64748B;">{{ $p->approvedBy->name ?? '—' }}</td>
            <td><a href="{{ route('admin.pemakaian.show', $p->id) }}" class="btn-detail"><i class="bi bi-eye"></i> Detail</a></td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state" style="padding:24px;"><i class="bi bi-inbox"></i><p>Tidak ada riwayat pemakaian</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- TABEL PENGADAAN --}}
@if($jenis === 'semua' || $jenis === 'pengadaan')
<div class="card">
  <div class="card-header">
    <div><h3><i class="bi bi-bag-check" style="color:#1D4ED8;margin-right:6px;"></i>Riwayat Pengadaan</h3>
    <div class="card-sub">{{ $pengadaans->count() }} permintaan pengadaan</div></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Tanggal</th><th>Barang</th><th>Jumlah</th><th>Status L2</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        @forelse($pengadaans as $idx => $p)
          <tr>
            <td style="color:#94A3B8;font-size:12px;">{{ $idx+1 }}</td>
            <td style="font-size:12px;color:#64748B;white-space:nowrap;">
              {{ $p->created_at->locale('id')->isoFormat('D MMM Y') }}<br>
              <span style="color:#94A3B8;">{{ $p->created_at->format('H:i') }}</span>
            </td>
            <td>
              @foreach($p->details as $d)
                <div style="font-size:12px;display:flex;align-items:center;gap:5px;">
                  <i class="bi bi-box-arrow-up" style="color:#0055A5;font-size:10px;"></i>
                  {{ $d->barang->nama_barang ?? '-' }}
                </div>
              @endforeach
            </td>
            <td>
              @foreach($p->details as $d)
                <span style="background:#EFF6FF;color:#1D4ED8;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:600;">{{ $d->jumlah }} {{ $d->barang->satuan??'' }}</span>
              @endforeach
            </td>
            <td>
              @php $sm=['pending'=>['Menunggu','badge-pending'],'approved'=>['Diteruskan','badge-forwarded'],'rejected'=>['Ditolak','badge-rejected']];[$l,$c]=$sm[$p->status_level2]??['-',''];@endphp
              <span class="status-badge {{ $c }}">{{ $l }}</span>
            </td>
            <td><a href="{{ route('admin.pengadaan.show', $p->id) }}" class="btn-detail"><i class="bi bi-eye"></i> Detail</a></td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state" style="padding:24px;"><i class="bi bi-inbox"></i><p>Tidak ada riwayat pengadaan</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection
