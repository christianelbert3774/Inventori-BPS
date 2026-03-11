@extends('layouts.admin')
@section('title', 'Profil Saya')
@section('topbar-title') Profil <span>Saya</span> @endsection

@section('content')
{{--
  BARU — admin/profil.blade.php
  Profil untuk Level 2 (Divisi Umum). Fitur sama dengan Level 1:
  edit info pribadi, ubah password, aktivitas yang sudah diproses.
--}}
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span><span class="current">Profil Saya</span>
  </div>
  <h2>Profil Saya</h2>
  <p>Kelola informasi akun dan keamanan Anda.</p>
</div>

{{-- PROFIL HERO --}}
<div class="profil-hero">
  <div class="profil-hero-left">
    <div class="profil-avatar-wrap">
      <div class="profil-avatar">{{ strtoupper(substr($user->name,0,2)) }}</div>
      <div class="profil-avatar-badge"><i class="bi bi-check-circle-fill"></i></div>
    </div>
    <div class="profil-hero-info">
      <h2>{{ $user->name }}</h2>
      <div class="profil-role-badge">
        <i class="bi bi-shield-check"></i> Divisi Umum
      </div>
      <div class="profil-meta">
        @if($user->nip)<span><i class="bi bi-credit-card"></i> NIP {{ $user->nip }}</span>@endif
        @if($user->bagian)<span><i class="bi bi-building"></i> {{ $user->bagian }}</span>@endif
        @if($user->jabatan)<span><i class="bi bi-briefcase"></i> {{ $user->jabatan }}</span>@endif
      </div>
    </div>
  </div>
  <div class="profil-hero-stats">
    <div class="profil-stat">
      <div class="pstat-num">{{ $totalApproved }}</div>
      <div class="pstat-lbl">Pemakaian Diproses</div>
    </div>
    <div class="profil-stat">
      <div class="pstat-num">{{ $totalForwarded }}</div>
      <div class="pstat-lbl">Pengadaan Diproses</div>
    </div>
    <div class="profil-stat">
      <div class="pstat-num orange">{{ $pendingPemakaian + $pendingPengadaan }}</div>
      <div class="pstat-lbl">Menunggu Proses</div>
    </div>
  </div>
</div>

{{-- TAB NAV --}}
<div class="profil-tabs">
  <button class="profil-tab active" onclick="switchTab('info',this)"><i class="bi bi-person"></i> Informasi Pribadi</button>
  <button class="profil-tab" onclick="switchTab('password',this)"><i class="bi bi-lock"></i> Ubah Password</button>
  
</div>

@if(session('success'))
  <div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
  </div>
@endif

{{-- TAB: INFO --}}
<div id="tab-info" class="tab-pane active">
  <div class="form-card">
    <div class="form-card-header">
      <h3><i class="bi bi-person-lines-fill"></i> Data Diri</h3>
      <p>Perbarui informasi profil Anda. Email digunakan untuk login.</p>
    </div>
    <form method="POST" action="{{ route('admin.profil.update') }}" id="form-profil-info">
      @csrf @method('PATCH')
      <div class="form-card-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('name')?'is-invalid':'' }}" type="text" name="name" value="{{ old('name',$user->name) }}" required/>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('email')?'is-invalid':'' }}" type="email" name="email" value="{{ old('email',$user->email) }}" required/>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>NIP</label>
            <input class="form-control" type="text" name="nip" value="{{ old('nip',$user->nip) }}" placeholder="Nomor Induk Pegawai"/>
            @error('nip')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>No. Telepon</label>
            <input class="form-control" type="text" name="no_telp" value="{{ old('no_telp',$user->no_telp) }}" placeholder="Contoh: 08123456789"/>
          </div>
          <div class="form-group">
            <label>Bagian / Unit Kerja</label>
            <input class="form-control" type="text" name="bagian" value="{{ old('bagian',$user->bagian) }}" placeholder="Divisi Umum"/>
          </div>
          <div class="form-group">
            <label>Jabatan</label>
            <input class="form-control" type="text" name="jabatan" value="{{ old('jabatan',$user->jabatan) }}" placeholder="Pengelola Barang"/>
          </div>
        </div>
        <div class="info-banner blue" style="margin-top:10px">
          <i class="bi bi-info-circle-fill"></i>
          <p>Role akun Anda adalah <strong>Divisi Umum</strong>. Hubungi administrator untuk mengubah role.</p>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-action btn-primary btn-lg"
          onclick="showConfirm({title:'Simpan Perubahan Profil',message:'Simpan perubahan informasi profil Anda?',icon:'bi-person-check-fill',iconColor:'#0055A5',confirmText:'Ya, Simpan',confirmClass:'confirm-btn-primary',onConfirm:function(){document.getElementById('form-profil-info').submit();}})">
          <i class="bi bi-check-lg"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- TAB: PASSWORD --}}
<div id="tab-password" class="tab-pane" style="display:none">
  <div class="form-card">
    <div class="form-card-header">
      <h3><i class="bi bi-shield-lock"></i> Ubah Password</h3>
      <p>Minimal 8 karakter. Pastikan Anda mengingat password baru.</p>
    </div>
    <form method="POST" action="{{ route('admin.profil.password') }}" id="form-profil-password">
      @csrf @method('PATCH')
      <div class="form-card-body">
        <div style="max-width:480px">
          <div class="form-group" style="margin-bottom:16px">
            <label>Password Lama <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control {{ $errors->has('current_password')?'is-invalid':'' }}" type="password" name="current_password" id="pwd-old" placeholder="Masukkan password lama"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-old',this)"></i>
            </div>
            @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group" style="margin-bottom:16px">
            <label>Password Baru <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control {{ $errors->has('password')?'is-invalid':'' }}" type="password" name="password" id="pwd-new" placeholder="Minimal 8 karakter"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-new',this)"></i>
            </div>
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group" style="margin-bottom:20px">
            <label>Konfirmasi Password Baru <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control" type="password" name="password_confirmation" id="pwd-confirm" placeholder="Ulangi password baru"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-confirm',this)"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-action btn-primary btn-lg"
          onclick="showConfirm({title:'Perbarui Password',message:'Apakah Anda yakin ingin mengubah password akun Anda?',icon:'bi-shield-lock-fill',iconColor:'#0055A5',confirmText:'Ya, Perbarui',confirmClass:'confirm-btn-primary',onConfirm:function(){document.getElementById('form-profil-password').submit();}})">
          <i class="bi bi-lock-fill"></i> Perbarui Password
        </button>
      </div>
    </form>
  </div>
</div>



@endsection

@push('scripts')
<script>
function switchTab(name,btn){
  document.querySelectorAll('.tab-pane').forEach(p=>p.style.display='none');
  document.querySelectorAll('.profil-tab').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).style.display='block';
  btn.classList.add('active');
}
function togglePwd(id,icon){const el=document.getElementById(id);if(!el)return;el.type=el.type==='password'?'text':'password';icon.className=el.type==='password'?'bi bi-eye pwd-eye':'bi bi-eye-slash pwd-eye';}
@if(session('tab')==='password'||$errors->has('current_password')||$errors->has('password'))
document.addEventListener('DOMContentLoaded',function(){const btn=document.querySelectorAll('.profil-tab')[1];if(btn)switchTab('password',btn);});
@endif
</script>
@endpush
