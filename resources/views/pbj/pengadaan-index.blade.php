@extends('layouts.admin')

@section('title', 'Pengadaan - PBJ')

@section('topbar-title')
  Pengadaan <span>PBJ</span>
@endsection

@section('content')

  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <span class="current">Pengadaan PBJ</span>
    </div>
    <h2>Daftar Pengadaan untuk Diproses</h2>
    <p>Permintaan pengadaan yang sudah disetujui Divisi Umum dan perlu ditindaklanjuti PBJ.</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;padding:12px 16px;background:#D1FAE5;border-radius:8px;color:#065F46;border-left:4px solid #059669;">
      <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;background:#FEE2E2;border-radius:8px;color:#991B1B;border-left:4px solid #DC2626;">
      <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
    </div>
  @endif

  {{-- Filter Status --}}
  <div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;">
    @foreach([''=>'Semua', 'pending'=>'Menunggu', 'completed'=>'Selesai', 'rejected'=>'Ditolak'] as $val => $lbl)
      <a href="{{ route('pbj.pengadaan.index', ['status'=>$val]) }}"
         style="padding:6px 16px;border-radius:20px;text-decoration:none;font-size:13px;font-weight:600;
                {{ request('status')===$val ? 'background:#0055A5;color:#fff;' : 'background:#F1F5F9;color:#475569;' }}">
        {{ $lbl }}
      </a>
    @endforeach
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Pemohon</th>
            <th>Tanggal Pengajuan</th>
            <th>Jumlah Item</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengadaans as $p)
            <tr>
              <td style="color:#94A3B8;">{{ $p->id }}</td>
              <td style="font-weight:600;">{{ $p->user->name ?? '-' }}</td>
              <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $p->details->count() }} item</td>
              <td>
                @php
                  $map = ['pending'=>['Menunggu','#D97706'], 'completed'=>['Selesai','#059669'], 'rejected'=>['Ditolak','#DC2626']];
                  [$lbl, $clr] = $map[$p->status_level3] ?? ['-','#94A3B8'];
                @endphp
                <span style="font-weight:600;color:{{ $clr }};">{{ $lbl }}</span>
              </td>
              <td>
                <a href="{{ route('pbj.pengadaan.show', $p->id) }}"
                   style="font-size:12px;font-weight:600;color:#0055A5;text-decoration:none;">
                  <i class="bi bi-eye"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align:center;color:#94A3B8;padding:32px;">
                Tidak ada data pengadaan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div style="padding:16px 24px;">
      {{ $pengadaans->links() }}
    </div>
  </div>

@endsection
