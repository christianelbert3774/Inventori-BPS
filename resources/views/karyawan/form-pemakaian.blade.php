@extends('layouts.app')

@section('title', 'Form Permintaan Pemakaian')

@section('topbar-title')
  Form <span>Permintaan Pemakaian</span>
@endsection

@section('content')
  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Permintaan Pemakaian</span>
    </div>
    <h2>Form Permintaan Pemakaian Barang</h2>
    <p>Isi formulir berikut untuk mengajukan permintaan pemakaian barang dari gudang inventori.</p>
  </div>

  <div class="info-banner blue">
    <i class="bi bi-info-circle-fill"></i>
    <p>Permintaan akan dikirimkan ke <strong>Admin Gudang (Divisi Umum)</strong> untuk diproses dan disetujui. Pastikan barang dan jumlah yang diminta sesuai kebutuhan.</p>
  </div>

  {{-- Template options for JS --}}
  <template id="barang-options-template">
    @foreach($barangs as $barang)
      <option value="{{ $barang->id }}" {{ $barang->stok == 0 ? 'disabled' : '' }}>
        {{ $barang->nama_barang }} — Stok: {{ $barang->stok }} {{ $barang->satuan }}
        {{ $barang->stok == 0 ? '(Habis)' : '' }}
      </option>
    @endforeach
  </template>

  <form method="POST" action="{{ route('karyawan.pemakaian.store') }}" id="form-pemakaian">
    @csrf

    {{-- INFO PEMOHON --}}
    <div class="form-card">
      <div class="form-card-header">
        <h3><i class="bi bi-person-badge"></i> Informasi Pemohon</h3>
        <p>Data berikut diisi otomatis berdasarkan akun yang sedang masuk.</p>
      </div>
      <div class="form-card-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input class="form-control" type="text" value="{{ auth()->user()->name }}" readonly/>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="text" value="{{ auth()->user()->email }}" readonly/>
          </div>
        </div>
      </div>
    </div>

    {{-- DAFTAR BARANG --}}
    <div class="form-card">
      <div class="form-card-header">
        <h3><i class="bi bi-cart3"></i> Daftar Barang yang Diminta</h3>
        <p>Tambahkan satu atau lebih barang. Klik "Tambah Barang" untuk menambah baris baru.</p>
      </div>
      <div class="form-card-body">

        @if($errors->any())
          <div class="alert alert-error" style="margin-bottom:16px">
            <i class="bi bi-x-circle-fill"></i>
            <div>
              @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          </div>
        @endif

        <div id="barang-list">
          {{-- ROW PERTAMA (default, tidak bisa dihapus) --}}
          <div class="barang-item" id="barang-row-1">
            <div class="barang-item-header">
              <span class="barang-item-num">Barang #1</span>
            </div>
            <div class="form-grid-2">
              <div class="form-group">
                <label>Pilih Barang <span class="req">*</span></label>
                <select class="form-control" name="barang_id[]" required>
                  <option value="">-- Pilih Barang --</option>
                  @foreach($barangs as $barang)
                    <option value="{{ $barang->id }}"
                      {{ request('barang_id') == $barang->id ? 'selected' : '' }}
                      {{ $barang->stok == 0 ? 'disabled' : '' }}>
                      {{ $barang->nama_barang }} — Stok: {{ $barang->stok }} {{ $barang->satuan }}
                      {{ $barang->stok == 0 ? '(Habis)' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label>Jumlah <span class="req">*</span></label>
                <input class="form-control" type="number" name="jumlah[]"
                       min="1" placeholder="Masukkan jumlah" required
                       value="{{ old('jumlah.0') }}"/>
              </div>
            </div>
          </div>
        </div>

        <button type="button" class="btn-add-barang" onclick="addBarang()">
          <i class="bi bi-plus-circle"></i> Tambah Barang
        </button>
      </div>

      <div class="form-actions">
        <a href="{{ route('karyawan.dashboard') }}" class="btn-action btn-outline">
          <i class="bi bi-x"></i> Batal
        </a>
        <button type="button" class="btn-action btn-primary btn-lg"
          onclick="showConfirm({
            title: 'Konfirmasi Kirim Permintaan',
            message: 'Pastikan barang dan jumlah yang diminta sudah benar. Permintaan akan dikirim ke Admin Gudang untuk diproses.',
            icon: 'bi-send-fill',
            iconColor: '#0055A5',
            confirmText: 'Ya, Kirim',
            confirmClass: 'confirm-btn-primary',
            confirmIcon: 'bi-send-fill',
            onConfirm: function() { document.getElementById('form-pemakaian').submit(); }
          })">
          <i class="bi bi-send"></i> Kirim Permintaan
        </button>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  // Inject options from template into JS addBarang()
  const tpl = document.getElementById('barang-options-template');
  if (tpl) {
    const placeholder = document.createElement('div');
    placeholder.id = 'barang-options-template';
    placeholder.style.display = 'none';
    placeholder.innerHTML = tpl.innerHTML;
    document.body.appendChild(placeholder);
  }
</script>
@endpush
