@extends('layouts.pbj')

@section('title', 'Pengadaan - PBJ')

@section('topbar-title')
  Pengadaan <span>PBJ</span>
@endsection

@section('content')

  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('pbj.dashboard') }}" style="color:inherit;text-decoration:none;">SIBAS</a>
      <span class="sep">/</span>
      <span class="current">Pengadaan PBJ</span>
    </div>
    <h2>Daftar Pengadaan untuk Diproses</h2>
    <p>Permintaan pengadaan yang sudah disetujui Divisi Umum dan perlu ditindaklanjuti PBJ.</p>
  </div>

  {{-- Filter Status --}}
  <div class="filter-bar" style="margin-bottom:16px;">
    @foreach(['' => 'Semua', 'pending' => 'Menunggu', 'completed' => 'Selesai', 'rejected' => 'Ditolak'] as $val => $lbl)
      <a href="{{ route('pbj.pengadaan.index', ['status' => $val]) }}"
         class="filter-pill {{ request('status') === $val ? 'active' : '' }} {{ $val === 'pending' ? 'pending' : ($val === 'completed' ? 'approved' : ($val === 'rejected' ? 'rejected' : '')) }}">
        {{ $lbl }}
      </a>
    @endforeach
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h3>Permintaan Pengadaan</h3>
        <div class="card-sub">{{ $pengadaans->total() }} total pengadaan</div>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Pemohon</th>
            <th>Tanggal Pengajuan</th>
            <th>Jumlah Item</th>
            <th>Status PBJ</th>
            <th>Verifikasi Admin</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengadaans as $p)
            <tr>
              <td style="color:#94A3B8;">{{ $p->id }}</td>
              <td><div style="font-weight:600;color:#1E293B;">{{ $p->user->name ?? '-' }}</div></td>
              <td style="font-size:13px;color:#64748B;">{{ $p->created_at->format('d/m/Y H:i') }}</td>
              <td>
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;background:rgba(0,85,165,.06);color:var(--bps-blue);padding:3px 10px;border-radius:20px;">
                  <i class="bi bi-box-seam" style="font-size:11px;"></i> {{ $p->details->count() }} item
                </span>
              </td>
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
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <h4>Tidak Ada Data Pengadaan</h4>
                  <p>Belum ada permintaan pengadaan yang perlu diproses.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($pengadaans->total() > 0)
    <div class="card-footer">
      <span style="font-size:13px;color:var(--text-secondary)">Menampilkan {{ $pengadaans->firstItem() }}–{{ $pengadaans->lastItem() }} dari {{ $pengadaans->total() }} pengadaan</span>
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
