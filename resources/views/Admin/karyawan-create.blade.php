@extends('layouts.admin')
@section('title', 'Tambah Karyawan Baru')
@section('topbar-title') Tambah <span>Karyawan</span> @endsection

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span>
    <a href="{{ route('admin.karyawan.index') }}" style="color:inherit;text-decoration:none;">Karyawan</a>
    <span class="sep">/</span><span class="current">Tambah Baru</span>
  </div>
  <h2>Tambah Akun Karyawan</h2>
  <p>Buat akun karyawan baru. Karyawan dapat langsung login setelah akun dibuat.</p>
</div>

{{-- CONTAINER DIPERLEBAR AGAR TIDAK ADA SPACE KOSONG --}}
<div style="max-width:1500px;width:100%;">
  <div class="form-card">

    <div class="form-card-header">
      <h3><i class="bi bi-person-plus-fill"></i> Data Karyawan Baru</h3>
      <p>Isi informasi di bawah. Email dan password akan digunakan untuk login.</p>
    </div>

    <form method="POST" action="{{ route('admin.karyawan.store') }}" id="form-create-karyawan">
      @csrf

      <div class="form-card-body">

        {{-- GRID FORM --}}
        <div class="form-grid-2">

          <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('name')?'is-invalid':'' }}"
                   type="text" name="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Budi Santoso"/>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('email')?'is-invalid':'' }}"
                   type="email" name="email" value="{{ old('email') }}" required
                   placeholder="email@bps.go.id"/>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Password <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control {{ $errors->has('password')?'is-invalid':'' }}"
                     type="password" name="password" id="pwd-new"
                     placeholder="Minimal 8 karakter"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-new',this)"></i>
            </div>
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Konfirmasi Password <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control"
                     type="password"
                     name="password_confirmation"
                     id="pwd-confirm"
                     placeholder="Ulangi password"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-confirm',this)"></i>
            </div>
          </div>

          <div class="form-group">
            <label>NIP</label>
            <input class="form-control {{ $errors->has('nip')?'is-invalid':'' }}"
                   type="text"
                   name="nip"
                   value="{{ old('nip') }}"
                   placeholder="Nomor Induk Pegawai"/>
            @error('nip')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>No. Telepon</label>
            <input class="form-control"
                   type="text"
                   name="no_telp"
                   value="{{ old('no_telp') }}"
                   placeholder="Contoh: 08123456789"/>
          </div>

          <div class="form-group">
            <label>Bagian / Unit Kerja</label>
            <input class="form-control"
                   type="text"
                   name="bagian"
                   value="{{ old('bagian') }}"
                   placeholder="Contoh: Seksi Distribusi"/>
          </div>

          <div class="form-group">
            <label>Jabatan</label>
            <input class="form-control"
                   type="text"
                   name="jabatan"
                   value="{{ old('jabatan') }}"
                   placeholder="Contoh: Staf Statistisi"/>
          </div>

        </div>

        <div class="info-banner blue" style="margin-top:14px">
          <i class="bi bi-info-circle-fill"></i>
          <p>Role akun baru ini otomatis di-set sebagai <strong>Karyawan</strong>.</p>
        </div>

      </div>

      {{-- ACTION BUTTON --}}
      <div class="form-actions"
           style="display:flex;gap:12px;margin-top:20px;">

        <button type="button"
          class="btn-action btn-primary btn-lg"
          onclick="showConfirm({
            title:'Buat Akun Karyawan?',
            message:'Akun karyawan baru akan dibuat dan langsung dapat digunakan untuk login.',
            icon:'bi-person-check-fill',
            iconColor:'#059669',
            confirmText:'Ya, Buat Akun',
            confirmClass:'confirm-btn-success',
            onConfirm:function(){
              document.getElementById('form-create-karyawan').submit();
            }
          })">

          <i class="bi bi-person-plus-fill"></i> Buat Akun
        </button>

        <a href="{{ route('admin.karyawan.index') }}"
           style="display:inline-flex;
                  align-items:center;
                  gap:6px;
                  padding:12px 20px;
                  border-radius:10px;
                  border:1.5px solid #E2E8F0;
                  background:#F8FAFC;
                  color:#64748B;
                  font-size:14px;
                  font-weight:600;
                  text-decoration:none;">
          <i class="bi bi-arrow-left"></i> Batal
        </a>

      </div>

    </form>
  </div>
</div>

@endsection


@push('scripts')
<script>
function togglePwd(id,icon){
  const el=document.getElementById(id);
  if(!el)return;
  el.type=el.type==='password'?'text':'password';
  icon.className=el.type==='password'
      ?'bi bi-eye pwd-eye'
      :'bi bi-eye-slash pwd-eye';
}
</script>
@endpush


{{-- GRID STYLE UNTUK RESPONSIVE --}}
<style>
.form-grid-2{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:18px 20px;
}
</style>