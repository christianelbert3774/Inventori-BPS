<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>SIBAS — @yield('title', 'Sistem Inventori BPS')</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>
  <link href="{{ asset('css/tambahan.css') }}" rel="stylesheet"/>
  @stack('styles')
</head>
<body>

<div class="layout-wrapper">

  {{-- ── SIDEBAR ── --}}
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="{{ asset('images/logo-bps.png') }}" alt="BPS"
           onerror="this.style.display='none'"/>
      <div class="sidebar-brand">
        <div class="brand-name">SIBAS</div>
        <div class="brand-sub">Inventori BPS</div>
      </div>
    </div>

    <div class="sidebar-user">
      <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <div>
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
      </div>
    </div>

    <nav class="sidebar-menu">
      <div class="menu-section">Menu Utama</div>

      <a href="{{ route('karyawan.dashboard') }}"
         class="menu-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
      </a>

      <a href="{{ route('karyawan.pemakaian.create') }}"
         class="menu-item {{ request()->routeIs('karyawan.pemakaian.create') ? 'active' : '' }}">
        <i class="bi bi-cart-plus"></i> Permintaan Pemakaian
      </a>

      <a href="{{ route('karyawan.pengadaan.create') }}"
         class="menu-item {{ request()->routeIs('karyawan.pengadaan.create') ? 'active' : '' }}">
        <i class="bi bi-box-arrow-up"></i> Permintaan Pengadaan
      </a>

      <div class="menu-section">Riwayat</div>

      <a href="{{ route('karyawan.pemakaian.index') }}"
         class="menu-item {{ request()->routeIs('karyawan.pemakaian.index') ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i> Riwayat Pemakaian
      </a>

      <a href="{{ route('karyawan.pengadaan.index') }}"
         class="menu-item {{ request()->routeIs('karyawan.pengadaan.index') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text"></i> Riwayat Pengadaan
      </a>

      <div class="menu-section">Akun</div>

      <a href="{{ route('karyawan.notifikasi') }}"
         class="menu-item {{ request()->routeIs('karyawan.notifikasi') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifikasi
        @php $badge = \App\Http\Controllers\Karyawan\NotifikasiController::getBadgeCount(); @endphp
        @if($badge > 0)
          <span style="margin-left:auto;background:rgba(220,38,38,.85);color:#fff;font-size:10px;
                       font-weight:700;padding:2px 7px;border-radius:20px">{{ $badge }}</span>
        @endif
      </a>

      <a href="{{ route('karyawan.profil') }}"
         class="menu-item {{ request()->routeIs('karyawan.profil') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> Profil Saya
      </a>
    </nav>

    <div class="sidebar-footer">
      <form method="POST" action="{{ route('logout') }}" id="form-logout-sidebar">
        @csrf
        <button type="button" class="btn-logout" onclick="doLogout('form-logout-sidebar')">
          <i class="bi bi-box-arrow-left"></i> Keluar
        </button>
      </form>
    </div>
  </aside>

  {{-- ── MAIN AREA ── --}}
  <div class="main-area">

    {{-- TOPBAR --}}
    <div class="topbar">
      <div class="topbar-title">@yield('topbar-title')</div>
      <form action="{{ route('karyawan.dashboard') }}" method="GET" class="topbar-search" role="search" id="topbar-search-form">
        @if(request('filter'))
          <input type="hidden" name="filter" value="{{ request('filter') }}"/>
        @endif
        <i class="bi bi-search" style="cursor:pointer" onclick="document.getElementById('topbar-search-form').submit()"></i>
        <input type="text" name="q" placeholder="Cari barang..." value="{{ request('q') }}" autocomplete="off" id="topbar-search-input"/>
        @if(request('q'))
          <a href="{{ route('karyawan.dashboard', request('filter') ? ['filter' => request('filter')] : []) }}"
             class="search-clear-btn" title="Hapus pencarian">
            <i class="bi bi-x-lg"></i>
          </a>
        @endif
      </form>
      <div class="topbar-actions">

        {{-- Bell icon dengan badge --}}
        <a href="{{ route('karyawan.notifikasi') }}" class="topbar-icon-btn" title="Notifikasi">
          <i class="bi bi-bell"></i>
          @php $badge = \App\Http\Controllers\Karyawan\NotifikasiController::getBadgeCount(); @endphp
          @if($badge > 0)
            <span class="notif-badge">{{ $badge > 9 ? '9+' : $badge }}</span>
          @endif
        </a>

        {{-- Profil icon --}}
        <a href="{{ route('karyawan.profil') }}" class="topbar-icon-btn" title="Profil Saya">
          <i class="bi bi-person-circle"></i>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" id="form-logout-topbar" style="display:inline">
          @csrf
          <button type="button" class="topbar-icon-btn" title="Keluar" onclick="doLogout('form-logout-topbar')">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </form>
      </div>
    </div>

    {{-- CONTENT --}}
    <div class="content">
      @if(session('success'))
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill"></i>
          {{ session('success') }}
          <button class="alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error">
          <i class="bi bi-x-circle-fill"></i>
          {{ session('error') }}
          <button class="alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        </div>
      @endif

      @yield('content')
    </div>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')

{{-- ── GLOBAL CONFIRMATION MODAL ── --}}
<div id="confirm-modal-overlay" style="
  display:none;position:fixed;inset:0;z-index:9999;
  background:rgba(15,23,42,.45);backdrop-filter:blur(4px);
  align-items:center;justify-content:center;
" onclick="_confirmHandleOverlayClick(event)">
  <div id="confirm-modal-box" style="
    background:#fff;border-radius:20px;padding:36px 32px 28px;
    max-width:420px;width:90%;box-shadow:0 24px 60px rgba(15,23,42,.18);
    transform:scale(.92);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease;
    position:relative;
  ">
    {{-- Icon --}}
    <div id="confirm-modal-icon-wrap" style="
      width:64px;height:64px;border-radius:50%;display:flex;
      align-items:center;justify-content:center;margin:0 auto 18px;
      background:#FEF2F2;
    ">
      <i id="confirm-modal-icon" class="bi bi-question-circle-fill" style="font-size:28px;color:#DC2626"></i>
    </div>
    {{-- Title --}}
    <h3 id="confirm-modal-title" style="
      text-align:center;font-family:'Plus Jakarta Sans',sans-serif;
      font-size:1.1rem;font-weight:700;color:#1e293b;margin:0 0 8px;
    ">Konfirmasi</h3>
    {{-- Message --}}
    <p id="confirm-modal-message" style="
      text-align:center;font-size:.875rem;color:#64748b;
      line-height:1.6;margin:0 0 28px;
    ">Apakah Anda yakin?</p>
    {{-- Buttons --}}
    <div style="display:flex;gap:12px;justify-content:center;">
      <button onclick="hideConfirm()" style="
        flex:1;padding:11px 20px;border-radius:12px;
        border:1.5px solid #e2e8f0;background:#f8fafc;
        font-family:'Plus Jakarta Sans',sans-serif;font-size:.875rem;
        font-weight:600;color:#64748b;cursor:pointer;
        transition:all .15s ease;
      " onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
        <i class="bi bi-x-lg" style="margin-right:6px"></i>Batal
      </button>
      <button id="confirm-modal-confirm-btn" style="
        flex:1;padding:11px 20px;border-radius:12px;border:none;
        background:#DC2626;color:#fff;
        font-family:'Plus Jakarta Sans',sans-serif;font-size:.875rem;
        font-weight:600;cursor:pointer;transition:all .15s ease;
      ">
        <i id="confirm-modal-confirm-icon" class="bi bi-check-lg" style="margin-right:6px"></i>
        <span id="confirm-modal-confirm-text">Ya, Lanjutkan</span>
      </button>
    </div>
  </div>
</div>

<script>
// ── Global Confirm Modal ──────────────────────────────────────
var _confirmCallback = null;

function doLogout(formId) {
  showConfirm({
    title: 'Konfirmasi Keluar',
    message: 'Apakah Anda yakin ingin keluar dari sistem SIBAS?',
    icon: 'bi-box-arrow-right',
    iconColor: '#DC2626',
    confirmText: 'Ya, Keluar',
    confirmClass: 'confirm-btn-danger',
    onConfirm: function() { document.getElementById(formId).submit(); }
  });
}

function showConfirm(opts) {
  opts = opts || {};
  var overlay = document.getElementById('confirm-modal-overlay');
  var box     = document.getElementById('confirm-modal-box');
  var iconWrap= document.getElementById('confirm-modal-icon-wrap');
  var icon    = document.getElementById('confirm-modal-icon');
  var title   = document.getElementById('confirm-modal-title');
  var msg     = document.getElementById('confirm-modal-message');
  var btn     = document.getElementById('confirm-modal-confirm-btn');
  var btnIcon = document.getElementById('confirm-modal-confirm-icon');
  var btnText = document.getElementById('confirm-modal-confirm-text');

  // Apply options
  title.textContent   = opts.title   || 'Konfirmasi';
  msg.textContent     = opts.message || 'Apakah Anda yakin ingin melanjutkan aksi ini?';
  btnText.textContent = opts.confirmText || 'Ya, Lanjutkan';

  // Icon
  var iconClass = opts.icon || 'bi-question-circle-fill';
  icon.className = 'bi ' + iconClass;
  var iconColor = opts.iconColor || '#2563EB';
  icon.style.color   = iconColor;
  // icon bg = iconColor with low opacity
  var hex = iconColor;
  iconWrap.style.background = hex + '1a';

  // Confirm button style
  var confirmClass = opts.confirmClass || 'confirm-btn-primary';
  if (confirmClass === 'confirm-btn-danger') {
    btn.style.background = '#DC2626';
    btnIcon.className = 'bi bi-box-arrow-right';
  } else if (confirmClass === 'confirm-btn-warning') {
    btn.style.background = '#D97706';
    btnIcon.className = 'bi bi-check-lg';
  } else {
    btn.style.background = '#0055A5';
    btnIcon.className = 'bi bi-check-lg';
  }
  if (opts.confirmIcon) btnIcon.className = 'bi ' + opts.confirmIcon;

  _confirmCallback = opts.onConfirm || null;
  btn.onclick = function() {
    hideConfirm();
    if (_confirmCallback) setTimeout(_confirmCallback, 120);
  };

  overlay.style.display = 'flex';
  // Animate in
  requestAnimationFrame(function() {
    requestAnimationFrame(function() {
      box.style.transform = 'scale(1)';
      box.style.opacity   = '1';
    });
  });

  document.addEventListener('keydown', _confirmKeyHandler);
}

function hideConfirm() {
  var overlay = document.getElementById('confirm-modal-overlay');
  var box     = document.getElementById('confirm-modal-box');
  box.style.transform = 'scale(.92)';
  box.style.opacity   = '0';
  setTimeout(function() { overlay.style.display = 'none'; }, 200);
  document.removeEventListener('keydown', _confirmKeyHandler);
}

function _confirmKeyHandler(e) {
  if (e.key === 'Escape') hideConfirm();
}

function _confirmHandleOverlayClick(e) {
  if (e.target === document.getElementById('confirm-modal-overlay')) hideConfirm();
}
</script>

</body>
</html>
