@extends('layouts.app')

@section('title', 'Form Permintaan Pengadaan')

@section('topbar-title')
  Form <span>Permintaan Pengadaan</span>
@endsection

@section('content')
  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
      <span class="sep">/</span>
      <span class="current">Permintaan Pengadaan</span>
    </div>
    <h2>Form Permintaan Pengadaan Barang</h2>
    <p>Gunakan formulir ini untuk mengajukan restock barang habis atau pengadaan barang baru yang belum tersedia.</p>
  </div>

  <div class="info-banner orange">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <p>Permintaan akan diteruskan ke <strong>Divisi Umum</strong>, kemudian diproses oleh <strong>Pejabat Pengadaan Barang & Jasa (PBJ)</strong>. Pilih jenis pengadaan yang sesuai di bawah ini.</p>
  </div>

  <form method="POST" action="{{ route('karyawan.pengadaan.store') }}">
    @csrf
    <input type="hidden" id="pengadaan-type-input" name="tipe_pengadaan" value="{{ old('tipe_pengadaan', 'restock') }}"/>

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

    {{-- JENIS PENGADAAN --}}
    <div class="form-card">
      <div class="form-card-header">
        <h3><i class="bi bi-bag-plus"></i> Jenis Pengadaan</h3>
        <p>Pilih apakah Anda ingin menambah stok barang yang sudah ada, atau mengajukan barang jenis baru.</p>
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

        {{-- TOGGLE --}}
        <div class="type-toggle">
          <button type="button" id="btn-type-restock"
                  class="type-btn {{ old('tipe_pengadaan','restock') === 'restock' ? 'active' : '' }}"
                  onclick="setPengadaanType('restock')">
            <i class="bi bi-arrow-repeat"></i>
            <div>
              <div style="font-weight:700">Tambah Stok Barang yang Ada</div>
              <div style="font-size:11.5px;font-weight:400;opacity:.8">Barang sudah terdaftar di inventori, stok perlu diisi ulang</div>
            </div>
          </button>
          <button type="button" id="btn-type-baru"
                  class="type-btn {{ old('tipe_pengadaan') === 'baru' ? 'active-orange' : '' }}"
                  onclick="setPengadaanType('baru')">
            <i class="bi bi-plus-square"></i>
            <div>
              <div style="font-weight:700">Pengadaan Barang Baru</div>
              <div style="font-size:11.5px;font-weight:400;opacity:.8">Barang belum terdaftar di inventori, perlu pengadaan jenis baru</div>
            </div>
          </button>
        </div>

        {{-- FORM RESTOCK --}}
        <div id="form-restock">
          <div class="form-grid-2">
            <div class="form-group">
              <label>Pilih Barang <span class="req">*</span></label>
              <select class="form-control" name="barang_id" data-required>
                <option value="">-- Pilih Barang --</option>
                @foreach($barangs as $barang)
                  <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }}>
                    {{ $barang->nama_barang }}
                    (Stok saat ini: {{ $barang->stok }} {{ $barang->satuan }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>Jumlah yang Dibutuhkan <span class="req">*</span></label>
              <input class="form-control" type="number" name="jumlah_restock"
                     min="1" placeholder="Masukkan jumlah" data-required
                     value="{{ old('jumlah_restock') }}"/>
            </div>
            <div class="form-group" style="grid-column:1/-1">
              <label>Alasan Pengadaan <span class="req">*</span></label>
              <textarea class="form-control" name="alasan_restock" data-required
                        placeholder="Jelaskan alasan penambahan stok...">{{ old('alasan_restock') }}</textarea>
            </div>
          </div>
        </div>

        {{-- FORM BARANG BARU --}}
        <div id="form-baru" style="display:none">
          <div class="form-grid-2">
            <div class="form-group">
              <label>Nama Barang Baru <span class="req">*</span></label>
              <input class="form-control" type="text" name="nama_barang_baru"
                     placeholder="Contoh: Printer Canon MG2570S" data-required
                     value="{{ old('nama_barang_baru') }}"/>
            </div>
            <div class="form-group">
              <label>Jumlah yang Dibutuhkan <span class="req">*</span></label>
              <input class="form-control" type="number" name="jumlah_baru"
                     min="1" placeholder="Masukkan jumlah" data-required
                     value="{{ old('jumlah_baru') }}"/>
            </div>
            <div class="form-group">
              <label>Satuan <span class="req">*</span></label>
              <select class="form-control" name="satuan_baru" data-required>
                <option value="">-- Pilih Satuan --</option>
                @foreach(['Pcs','Unit','Rim','Botol','Pak','Dus','Roll','Lusin','Lembar','Set','Lain-lain'] as $s)
                  <option value="{{ $s }}" {{ old('satuan_baru') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>Kategori <span class="req">*</span></label>
              <select class="form-control" name="kategori_baru" data-required>
                <option value="">-- Pilih Kategori --</option>
                @foreach(['ATK (Alat Tulis Kantor)','Elektronik & Komputer','Kebersihan & Sanitasi','Peralatan Kantor','Perlengkapan Lapangan','Lain-lain'] as $k)
                  <option value="{{ $k }}" {{ old('kategori_baru') === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group" style="grid-column:1/-1">
              <label>Alasan Pengadaan <span class="req">*</span></label>
              <textarea class="form-control" name="alasan_baru" data-required
                        placeholder="Jelaskan mengapa barang ini perlu diadakan dan untuk keperluan apa...">{{ old('alasan_baru') }}</textarea>
            </div>
          </div>
        </div>

      </div>

      <div class="form-actions">
        <a href="{{ route('karyawan.dashboard') }}" class="btn-action btn-outline">
          <i class="bi bi-x"></i> Batal
        </a>
        <button type="submit" class="btn-action btn-orange btn-lg">
          <i class="bi bi-send"></i> Kirim Permintaan Pengadaan
        </button>
      </div>
    </div>
  </form>
@endsection
