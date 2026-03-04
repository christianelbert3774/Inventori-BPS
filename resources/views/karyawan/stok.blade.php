@extends('layouts.app')

@section('title', 'Stok Barang')
@section('page-title', 'Stok Barang')

@section('content')

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bps-breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('karyawan.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a></li>
        <li class="breadcrumb-item active">Stok Barang</li>
    </ol>
</nav>

<div class="page-header">
    <h1>Daftar Stok Barang</h1>
    <p>Lihat ketersediaan semua barang inventori yang tersedia di gudang</p>
</div>

{{-- Data dari DashboardController::stok() --}}

{{-- Filter Bar --}}
<div class="bps-card p-3 mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="input-group" style="max-width:280px;">
            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-radius:9px 0 0 9px;">
                <i class="bi bi-search" style="color:#94a3b8;"></i>
            </span>
            <input type="text" id="filter-nama" class="form-control"
                placeholder="Cari nama barang..."
                style="border-color:#e2e8f0;font-size:.875rem;border-radius:0 9px 9px 0;"
                onkeyup="filterTabel()">
        </div>
        <select id="filter-kategori" class="form-select" style="width:auto;border-radius:9px;border-color:#e2e8f0;font-size:.875rem;" onchange="filterTabel()">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriList as $kat)
            <option value="{{ $kat->nama }}" {{ request('kategori') == $kat->nama ? 'selected' : '' }}>{{ $kat->nama }}</option>
            @endforeach
        </select>
        <select id="filter-status" class="form-select" style="width:auto;border-radius:9px;border-color:#e2e8f0;font-size:.875rem;" onchange="filterTabel()">
            <option value="">Semua Status</option>
            <option value="tersedia">Tersedia</option>
            <option value="hampir_habis">Hampir Habis</option>
            <option value="habis">Habis</option>
        </select>
        <div class="ms-auto" style="font-size:.8rem;color:#64748b;">
            Menampilkan <strong>{{ $stokBarang->count() }}</strong> dari <strong>{{ $stokBarang->total() }}</strong> barang
        </div>
    </div>
</div>

<div class="bps-card">
    <div class="table-responsive">
        <table class="table bps-table mb-0" id="tabel-stok-full">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stokBarang as $barang)
                <tr>
                    <td style="color:#94a3b8;font-size:.78rem;">{{ $barang->kode_barang }}</td>
                    <td>
                        <div style="font-weight:600;color:#1a2340;">{{ $barang->nama }}</div>
                    </td>
                    <td>
                        <span style="font-size:.78rem;background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:20px;font-weight:500;">
                            {{ $barang->kategori->nama ?? '-' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span style="font-weight:700;">{{ $barang->stok }}</span>
                        <span style="font-size:.72rem;color:#94a3b8;"> {{ $barang->satuan }}</span>
                    </td>
                    <td class="text-center">
                        @if ($barang->status_stok === 'tersedia')
                            <span class="badge badge-tersedia" style="font-size:.72rem;padding:4px 10px;border-radius:20px;">Tersedia</span>
                        @elseif ($barang->status_stok === 'hampir_habis')
                            <span class="badge badge-hampir" style="font-size:.72rem;padding:4px 10px;border-radius:20px;">Hampir Habis</span>
                        @else
                            <span class="badge badge-habis" style="font-size:.72rem;padding:4px 10px;border-radius:20px;">Habis</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($barang->isTersedia())
                        <a href="{{ route('karyawan.form-permintaan') }}?barang_id={{ $barang->id }}"
                           class="btn btn-sm" style="background:#e0edff;color:var(--bps-blue);border-radius:7px;font-size:.78rem;font-weight:600;padding:5px 12px;">
                            <i class="bi bi-cart-plus-fill me-1"></i>Minta
                        </a>
                        @else
                        <a href="{{ route('karyawan.form-pengadaan') }}?nama={{ urlencode($barang->nama) }}"
                           class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border-radius:7px;font-size:.78rem;font-weight:600;padding:5px 12px;">
                            <i class="bi bi-clipboard2-plus me-1"></i>Adakan
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:40px;color:#94a3b8;font-size:.875rem;">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        Tidak ada barang ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">
        {{ $stokBarang->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Filter dengan server-side (redirect ke controller)
    let filterTimeout;
    function filterTabel() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            const params = new URLSearchParams({
                search:   document.getElementById('filter-nama').value,
                kategori: document.getElementById('filter-kategori').value,
                status:   document.getElementById('filter-status').value,
            });
            window.location.href = '{{ route("karyawan.stok") }}?' + params.toString();
        }, 500);
    }

    // Set nilai filter dari URL
    document.getElementById('filter-nama').value   = '{{ request("search") }}';
    document.getElementById('filter-status').value = '{{ request("status") }}';
</script>
@endpush
