@extends('layouts.admin')
@section('title', 'Data Karyawan')
@section('topbar-title') Manajemen <span>Karyawan</span> @endsection

@section('content')
{{--
  BARU — admin/karyawan.blade.php
  Daftar semua karyawan Level 1 beserta statistik permintaan mereka.
  Admin dapat menambah akun baru, lihat history, dan toggle aktif/nonaktif.
--}}
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span><span class="current">Data Karyawan</span>
  </div>
  <h2>Manajemen Karyawan</h2>
  <p>Kelola akun karyawan dan lihat riwayat permintaan mereka.</p>
</div>

{{-- SEARCH + TAMBAH --}}
<div class="card" style="margin-bottom:20px;">
<div class="card-header" style="padding:16px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">    
<form method="GET"
      action="{{ route('admin.karyawan.index') }}"
      style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;flex:1;">
      <div style="flex:1;min-width:200px;position:relative;">
        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, NIP, atau bagian..."
               style="width:100%;padding:8px 12px 8px 36px;border:1.5px solid #E2E8F0;border-radius:8px;font-size:13px;font-family:inherit;outline:none;"
               onfocus="this.style.borderColor='#0055A5'" onblur="this.style.borderColor='#E2E8F0'"/>
      </div>
      <button type="submit" style="padding:8px 18px;background:#0055A5;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
        <i class="bi bi-search"></i> Cari
      </button>
    </form>
    <a href="{{ route('admin.karyawan.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#059669;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
      <i class="bi bi-person-plus-fill"></i> Tambah Karyawan
    </a>
  </div>
</div>

{{-- TABEL KARYAWAN --}}
<div class="card">
  <div class="card-header">
    <div><h3>Daftar Karyawan</h3><div class="card-sub">Total {{ $karyawans->total() }} karyawan terdaftar</div></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Nama Karyawan</th><th>NIP</th><th>Bagian / Jabatan</th><th>Pemakaian</th><th>Pengadaan</th><th>Pending</th><th>Status</th><th style="text-align:center;">Aksi</th></tr>
      </thead>
      <tbody>
        @forelse($karyawans as $idx => $karyawan)
          <tr>
            <td style="color:#94A3B8;font-size:12px;">{{ $karyawans->firstItem() + $idx }}</td>
            <td>
              <div style="font-weight:600;color:#1E293B;">{{ $karyawan->name }}</div>
              <div style="font-size:11px;color:#94A3B8;">{{ $karyawan->email }}</div>
            </td>
            <td style="font-family:'DM Mono',monospace;font-size:12px;color:#0055A5;">{{ $karyawan->nip ?? '-' }}</td>
            <td>
              <div style="font-size:13px;color:#475569;">{{ $karyawan->bagian ?? '-' }}</div>
              <div style="font-size:11px;color:#94A3B8;">{{ $karyawan->jabatan ?? '-' }}</div>
            </td>
            <td>
              <span style="font-weight:700;color:#0055A5;">{{ $stats[$karyawan->id]['pemakaian'] ?? 0 }}</span>
              <span style="font-size:11px;color:#94A3B8;"> total</span>
            </td>
            <td>
              <span style="font-weight:700;color:#0055A5;">{{ $stats[$karyawan->id]['pengadaan'] ?? 0 }}</span>
              <span style="font-size:11px;color:#94A3B8;"> total</span>
            </td>
            <td>
              @php $pending = $stats[$karyawan->id]['pending'] ?? 0; @endphp
              @if($pending > 0)
                <span style="background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;">{{ $pending }} pending</span>
              @else
                <span style="color:#94A3B8;font-size:12px;">—</span>
              @endif
            </td>
            <td>
              @if($karyawan->is_active)
                <span class="status-badge badge-approved"><i class="bi bi-check-circle"></i> Aktif</span>
              @else
                <span class="status-badge badge-rejected"><i class="bi bi-x-circle"></i> Nonaktif</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('admin.karyawan.history', $karyawan->id) }}" class="btn-detail">
                  <i class="bi bi-clock-history"></i> Riwayat
                </a>
                <button type="button"
                  onclick="showConfirm({
                    title: '{{ $karyawan->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Akun?',
                    message: '{{ $karyawan->is_active ? 'Akun karyawan ini akan dinonaktifkan.' : 'Akun karyawan ini akan diaktifkan kembali.' }}',
                    icon: '{{ $karyawan->is_active ? 'bi-person-x-fill' : 'bi-person-check-fill' }}',
                    iconColor: '{{ $karyawan->is_active ? '#DC2626' : '#059669' }}',
                    confirmText: '{{ $karyawan->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}',
                    confirmClass: '{{ $karyawan->is_active ? 'confirm-btn-danger' : 'confirm-btn-success' }}',
                    onConfirm: function(){ document.getElementById('form-toggle-{{ $karyawan->id }}').submit(); }
                  })"
                  style="
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    gap:5px;
                    padding:6px 14px;
                    min-width:120px;
                    border-radius:8px;
                    font-size:12px;
                    font-weight:600;
                    cursor:pointer;
                    border:1px solid;
                    background:{{ $karyawan->is_active ? '#FEE2E2' : '#D1FAE5' }};
                    color:{{ $karyawan->is_active ? '#991B1B' : '#065F46' }};
                    border-color:{{ $karyawan->is_active ? '#FCA5A5' : '#A7F3D0' }};
                  ">
                    <i class="bi {{ $karyawan->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                    {{ $karyawan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                <form id="form-toggle-{{ $karyawan->id }}" method="POST"
                  action="{{ route('admin.karyawan.toggleActive', $karyawan->id) }}"
                  style="display:none;">
                  @csrf
                  @method('PATCH')
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><i class="bi bi-people"></i><h4>Tidak Ada Karyawan</h4><p>Belum ada karyawan terdaftar atau tidak ada yang cocok dengan pencarian.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($karyawans->total() > 0)
  <div class="card-footer">
    <span style="font-size:13px;color:var(--text-secondary)">Menampilkan {{ $karyawans->firstItem() }}–{{ $karyawans->lastItem() }} dari {{ $karyawans->total() }} karyawan</span>
    <div class="pagination">
      @if($karyawans->onFirstPage())<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-left"></i></span>
      @else<a href="{{ $karyawans->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>@endif
      @foreach($karyawans->getUrlRange(1,$karyawans->lastPage()) as $page=>$url)
        <a href="{{ $url }}" class="pg-btn {{ $page==$karyawans->currentPage()?'active':'' }}">{{ $page }}</a>
      @endforeach
      @if($karyawans->hasMorePages())<a href="{{ $karyawans->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
      @else<span class="pg-btn" style="opacity:.4"><i class="bi bi-chevron-right"></i></span>@endif
    </div>
  </div>
  @endif
</div>
@endsection
