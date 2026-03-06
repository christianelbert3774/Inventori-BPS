@extends('layouts.app')

@section('title', 'Profil Saya')

@section('topbar-title')
  Profil <span>Saya</span>
@endsection

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Profil Saya</span>
  </div>
  <h2>Profil Saya</h2>
  <p>Kelola informasi akun dan keamanan Anda.</p>
</div>

{{-- PROFIL HEADER CARD --}}
<div class="profil-hero">
  <div class="profil-hero-left">
    <div class="profil-avatar-wrap">
      <div class="profil-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
      <div class="profil-avatar-badge">
        <i class="bi bi-check-circle-fill"></i>
      </div>
    </div>
    <div class="profil-hero-info">
      <h2>{{ $user->name }}</h2>
      <div class="profil-role-badge">
        <i class="bi bi-shield-check"></i>
        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
      </div>
      <div class="profil-meta">
        @if($user->nip)
          <span><i class="bi bi-credit-card"></i> NIP {{ $user->nip }}</span>
        @endif
        @if($user->bagian)
          <span><i class="bi bi-building"></i> {{ $user->bagian }}</span>
        @endif
        @if($user->jabatan)
          <span><i class="bi bi-briefcase"></i> {{ $user->jabatan }}</span>
        @endif
      </div>
    </div>
  </div>
  <div class="profil-hero-stats">
    <div class="profil-stat">
      <div class="pstat-num">{{ $totalPemakaian }}</div>
      <div class="pstat-lbl">Total Pemakaian</div>
    </div>
    <div class="profil-stat">
      <div class="pstat-num">{{ $totalPengadaan }}</div>
      <div class="pstat-lbl">Total Pengadaan</div>
    </div>
    <div class="profil-stat">
      <div class="pstat-num orange">{{ $pendingPemakaian + $pendingPengadaan }}</div>
      <div class="pstat-lbl">Menunggu Proses</div>
    </div>
  </div>
</div>

{{-- TAB NAV --}}
<div class="profil-tabs">
  <button class="profil-tab active" onclick="switchTab('info', this)">
    <i class="bi bi-person"></i> Informasi Pribadi
  </button>
  <button class="profil-tab" onclick="switchTab('password', this)">
    <i class="bi bi-lock"></i> Ubah Password
  </button>
  <button class="profil-tab" onclick="switchTab('aktivitas', this)">
    <i class="bi bi-activity"></i> Aktivitas Terbaru
  </button>
</div>

{{-- SESSION ALERTS --}}
@if(session('success'))
  <div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i>
    {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
  </div>
@endif

{{-- ── TAB: INFORMASI PRIBADI ── --}}
<div id="tab-info" class="tab-pane active">
  <div class="form-card">
    <div class="form-card-header">
      <h3><i class="bi bi-person-lines-fill"></i> Data Diri</h3>
      <p>Perbarui informasi profil Anda. Email digunakan untuk login.</p>
    </div>
    <form method="POST" action="{{ route('karyawan.profil.update') }}">
      @csrf
      @method('PATCH')
      <div class="form-card-body">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   type="text" name="name" value="{{ old('name', $user->name) }}" required/>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   type="email" name="email" value="{{ old('email', $user->email) }}" required/>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>NIP</label>
            <input class="form-control {{ $errors->has('nip') ? 'is-invalid' : '' }}"
                   type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                   placeholder="Nomor Induk Pegawai"/>
            @error('nip')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>No. Telepon</label>
            <input class="form-control {{ $errors->has('no_telp') ? 'is-invalid' : '' }}"
                   type="text" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                   placeholder="Contoh: 08123456789"/>
            @error('no_telp')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Bagian / Unit Kerja</label>
            <input class="form-control {{ $errors->has('bagian') ? 'is-invalid' : '' }}"
                   type="text" name="bagian" value="{{ old('bagian', $user->bagian) }}"
                   placeholder="Contoh: Seksi Distribusi"/>
            @error('bagian')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Jabatan</label>
            <input class="form-control {{ $errors->has('jabatan') ? 'is-invalid' : '' }}"
                   type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}"
                   placeholder="Contoh: Staf Statistisi"/>
            @error('jabatan')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Info role (readonly) --}}
        <div class="info-banner blue" style="margin-top:10px">
          <i class="bi bi-info-circle-fill"></i>
          <p>Role akun Anda adalah <strong>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</strong>.
             Hubungi administrator untuk mengubah role.</p>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn-action btn-primary btn-lg">
          <i class="bi bi-check-lg"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── TAB: UBAH PASSWORD ── --}}
<div id="tab-password" class="tab-pane" style="display:none">
  <div class="form-card">
    <div class="form-card-header">
      <h3><i class="bi bi-shield-lock"></i> Ubah Password</h3>
      <p>Pastikan password baru Anda kuat dan mudah diingat. Minimal 8 karakter.</p>
    </div>
    <form method="POST" action="{{ route('karyawan.profil.password') }}">
      @csrf
      @method('PATCH')
      <div class="form-card-body">
        <div style="max-width:480px">
          <div class="form-group" style="margin-bottom:16px">
            <label>Password Lama <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                     type="password" name="current_password" id="pwd-old"
                     placeholder="Masukkan password lama"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-old', this)"></i>
            </div>
            @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group" style="margin-bottom:16px">
            <label>Password Baru <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                     type="password" name="password" id="pwd-new"
                     placeholder="Minimal 8 karakter"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-new', this)"></i>
            </div>
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group" style="margin-bottom:20px">
            <label>Konfirmasi Password Baru <span class="req">*</span></label>
            <div class="input-pwd-wrap">
              <input class="form-control"
                     type="password" name="password_confirmation" id="pwd-confirm"
                     placeholder="Ulangi password baru"/>
              <i class="bi bi-eye pwd-eye" onclick="togglePwd('pwd-confirm', this)"></i>
            </div>
          </div>

          <div class="pwd-strength-wrap" id="pwd-strength-wrap" style="display:none;margin-bottom:16px">
            <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px">Kekuatan Password</div>
            <div class="pwd-strength-bar">
              <div class="pwd-strength-fill" id="pwd-strength-fill"></div>
            </div>
            <div id="pwd-strength-label" style="font-size:12px;margin-top:4px"></div>
          </div>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn-action btn-primary btn-lg">
          <i class="bi bi-lock-fill"></i> Perbarui Password
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── TAB: AKTIVITAS TERBARU ── --}}
<div id="tab-aktivitas" class="tab-pane" style="display:none">

  {{-- PRINT BAR --}}
  <div class="print-bar">
    <div class="print-bar-left">
      <i class="bi bi-calendar3"></i>
      <span>Filter bulan:</span>
      <input type="month" id="input-bulan"
             value="{{ now()->format('Y-m') }}"
             max="{{ now()->format('Y-m') }}"
             style="padding:7px 11px;border:1.5px solid var(--border);border-radius:8px;
                    font-family:inherit;font-size:13px;color:var(--text-primary);
                    background:#F7FAFE;outline:none;cursor:pointer"/>
    </div>
    <div class="print-bar-right">
      <a id="btn-print-aktivitas"
         href="{{ route('karyawan.profil.print') }}?bulan={{ now()->format('Y-m') }}"
         target="_blank"
         class="btn-action btn-primary"
         style="gap:8px">
        <i class="bi bi-printer-fill"></i>
        Print / Simpan PDF
      </a>
    </div>
  </div>

  <div class="form-grid-2" style="align-items:start">

    {{-- Pemakaian terbaru --}}
    <div class="card" style="margin-bottom:0">
      <div class="card-header">
        <div>
          <h3>Pemakaian Terbaru</h3>
          <div class="card-sub">3 permintaan terakhir</div>
        </div>
        <a href="{{ route('karyawan.pemakaian.index') }}" class="btn-action btn-outline" style="font-size:12px">
          Lihat Semua
        </a>
      </div>
      @forelse($recentPemakaian as $p)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <span style="font-family:'DM Mono',monospace;font-size:11.5px;color:var(--text-secondary)">
              #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
            </span>
            @if($p->status === 'approved')
              <span class="badge-status badge-approved">Disetujui</span>
            @elseif($p->status === 'rejected')
              <span class="badge-status badge-rejected">Ditolak</span>
            @else
              <span class="badge-status badge-pending">Menunggu</span>
            @endif
          </div>
          @foreach($p->details as $d)
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">
              {{ $d->barang->nama_barang }}
              <span style="font-weight:400;color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->barang->satuan }}</span>
            </div>
          @endforeach
          <div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">
            <i class="bi bi-clock"></i> {{ $p->created_at->diffForHumans() }}
          </div>
        </div>
      @empty
        <div class="empty-state" style="padding:32px">
          <i class="bi bi-cart-x" style="font-size:32px"></i>
          <p style="margin-top:8px">Belum ada permintaan pemakaian</p>
        </div>
      @endforelse
    </div>

    {{-- Pengadaan terbaru --}}
    <div class="card" style="margin-bottom:0">
      <div class="card-header">
        <div>
          <h3>Pengadaan Terbaru</h3>
          <div class="card-sub">3 permintaan terakhir</div>
        </div>
        <a href="{{ route('karyawan.pengadaan.index') }}" class="btn-action btn-outline" style="font-size:12px">
          Lihat Semua
        </a>
      </div>
      @forelse($recentPengadaan as $p)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <span style="font-family:'DM Mono',monospace;font-size:11.5px;color:var(--text-secondary)">
              #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
            </span>
            @if($p->status_level2 === 'approved')
              <span class="badge-status badge-approved">Disetujui</span>
            @elseif($p->status_level2 === 'rejected')
              <span class="badge-status badge-rejected">Ditolak</span>
            @else
              <span class="badge-status badge-pending">Menunggu</span>
            @endif
          </div>
          @foreach($p->details as $d)
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">
              {{ $d->barang->nama_barang }}
              <span style="font-weight:400;color:var(--text-secondary)"> × {{ $d->jumlah }} {{ $d->barang->satuan }}</span>
            </div>
          @endforeach
          <div style="font-size:11.5px;color:var(--text-secondary);margin-top:4px">
            <i class="bi bi-clock"></i> {{ $p->created_at->diffForHumans() }}
          </div>
        </div>
      @empty
        <div class="empty-state" style="padding:32px">
          <i class="bi bi-bag-x" style="font-size:32px"></i>
          <p style="margin-top:8px">Belum ada permintaan pengadaan</p>
        </div>
      @endforelse
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.profil-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).style.display = 'block';
  btn.classList.add('active');
}

function togglePwd(id, icon) {
  const el = document.getElementById(id);
  if (!el) return;
  el.type = el.type === 'password' ? 'text' : 'password';
  icon.className = el.type === 'password' ? 'bi bi-eye pwd-eye' : 'bi bi-eye-slash pwd-eye';
}

// Password strength meter
const pwdNew = document.getElementById('pwd-new');
if (pwdNew) {
  pwdNew.addEventListener('input', function() {
    const val = this.value;
    const wrap = document.getElementById('pwd-strength-wrap');
    const fill = document.getElementById('pwd-strength-fill');
    const lbl  = document.getElementById('pwd-strength-label');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
      { pct: '20%', color: '#DC2626', text: 'Sangat Lemah', textColor: '#DC2626' },
      { pct: '40%', color: '#F07D00', text: 'Lemah',        textColor: '#F07D00' },
      { pct: '60%', color: '#F59E0B', text: 'Sedang',       textColor: '#F59E0B' },
      { pct: '80%', color: '#3DAA35', text: 'Kuat',         textColor: '#3DAA35' },
      { pct: '100%',color: '#1E6B1A', text: 'Sangat Kuat',  textColor: '#1E6B1A' },
    ];
    const lvl = levels[Math.max(0, score - 1)];
    fill.style.width = lvl.pct;
    fill.style.background = lvl.color;
    lbl.textContent = lvl.text;
    lbl.style.color = lvl.textColor;
    lbl.style.fontWeight = '600';
    lbl.style.fontSize = '12px';
  });
}

// Sync input bulan ke URL tombol print
const inputBulan = document.getElementById('input-bulan');
const btnPrint   = document.getElementById('btn-print-aktivitas');
if (inputBulan && btnPrint) {
  inputBulan.addEventListener('change', function() {
    const base = btnPrint.href.split('?')[0];
    btnPrint.href = base + '?bulan=' + this.value;
  });
}

// Auto-switch ke tab password jika ada error password
@if(session('tab') === 'password' || $errors->has('current_password') || $errors->has('password'))
  document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelectorAll('.profil-tab')[1];
    if (btn) switchTab('password', btn);
  });
@endif
</script>
@endpush
