<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>SIBAS Admin — @yield('title', 'Divisi Umum')</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>
  <link href="{{ asset('css/tambahan.css') }}" rel="stylesheet"/>
  {{--
    DIMODIFIKASI — layouts/admin.blade.php
    Perubahan:
     1. Tambah menu: Notifikasi (+ badge), Profil, Manajemen Karyawan
     2. Label role diubah dari 'Admin Gudang' → 'Divisi Umum'
     3. Badge notifikasi admin menggunakan Admin\NotifikasiController::getBadgeCount()
     4. Badge hilang setelah halaman notifikasi dibuka (fix bug)
  --}}
  <style>
    .badge-pending  { background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; }
    .badge-approved { background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; }
    .badge-rejected { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; }
    .badge-forwarded{ background:#DBEAFE; color:#1E40AF; border:1px solid #BFDBFE; }
    .status-badge {
      display:inline-flex; align-items:center; gap:5px;
      font-size:11px; font-weight:600; padding:4px 10px;
      border-radius:20px; white-space:nowrap;
    }
    .btn-approve {
      display:inline-flex; align-items:center; gap:5px;
      padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600;
      background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;
      cursor:pointer; transition:all .15s ease; text-decoration:none;
    }
    .btn-approve:hover { background:#A7F3D0; color:#065F46; }
    .btn-reject {
      display:inline-flex; align-items:center; gap:5px;
      padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600;
      background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5;
      cursor:pointer; transition:all .15s ease; text-decoration:none;
    }
    .btn-reject:hover { background:#FCA5A5; color:#991B1B; }
    .btn-detail {
      display:inline-flex; align-items:center; gap:5px;
      padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600;
      background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;
      cursor:pointer; transition:all .15s ease; text-decoration:none;
    }
    .btn-detail:hover { background:#BFDBFE; color:#1D4ED8; }
    .role-badge-admin {
      display:inline-block; background:rgba(255,255,255,.18);
      color:#fff; font-size:10px; font-weight:700;
      padding:2px 8px; border-radius:20px; margin-top:2px;
    }
    .stat-card .pending-dot {
      width:8px; height:8px; border-radius:50%;
      background:#F59E0B; display:inline-block; margin-right:4px;
      animation: pulse-dot 1.5s ease-in-out infinite;
    }
    @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.3} }
    .detail-section { background:#F8FAFC; border-radius:12px; padding:20px 24px; margin-bottom:16px; }
    .detail-label { font-size:11px; font-weight:600; color:#94A3B8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
    .detail-value { font-size:14px; font-weight:500; color:#1E293B; }
    .filter-bar { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .filter-pill {
      padding:6px 16px; border-radius:20px; font-size:12px; font-weight:600;
      border:1.5px solid #E2E8F0; background:#fff; color:#64748B;
      text-decoration:none; transition:all .15s ease;
    }
    .filter-pill:hover, .filter-pill.active { background:#0055A5; color:#fff; border-color:#0055A5; }
    .filter-pill.pending.active  { background:#D97706; border-color:#D97706; color:#fff; }
    .filter-pill.approved.active { background:#059669; border-color:#059669; color:#fff; }
    .filter-pill.rejected.active { background:#DC2626; border-color:#DC2626; color:#fff; }
  </style>
  @stack('styles')
</head>
<body>

<div class="layout-wrapper">

  {{-- ── SIDEBAR ADMIN ── --}}
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="{{ asset('images/logo-bps.png') }}" alt="BPS" onerror="this.style.display='none'"/>
      <div class="sidebar-brand">
        <div class="brand-name">SIBAS</div>
        <div class="brand-sub">INVENTORI BPS</div>
      </div>
    </div>

    <div class="sidebar-user">
      <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
      <div>
        <div class="user-name">{{ auth()->user()->name }}</div>
        <span class="role-badge-admin"><i class="bi bi-shield-check"></i> Divisi Umum</span>
      </div>
    </div>

    <nav class="sidebar-menu">
      <div class="menu-section">Menu Utama</div>

      <a href="{{ route('admin.dashboard') }}"
         class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
      </a>

      <a href="{{ route('admin.pemakaian.index') }}"
         class="menu-item {{ request()->routeIs('admin.pemakaian.*') ? 'active' : '' }}">
        <i class="bi bi-cart-check"></i> Permintaan Pemakaian
        @php $badgePemakaian = \App\Models\Pemakaian::where('status','pending')->count(); @endphp
        @if($badgePemakaian > 0)
          <span style="margin-left:auto;background:rgba(245,158,11,.9);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px">{{ $badgePemakaian }}</span>
        @endif
      </a>

      <a href="{{ route('admin.pengadaan.index') }}"
         class="menu-item {{ request()->routeIs('admin.pengadaan.*') ? 'active' : '' }}">
        <i class="bi bi-bag-check"></i> Permintaan Pengadaan
        @php $badgePengadaan = \App\Models\Pengadaan::where('status_level2','pending')->count(); @endphp
        @if($badgePengadaan > 0)
          <span style="margin-left:auto;background:rgba(245,158,11,.9);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px">{{ $badgePengadaan }}</span>
        @endif
      </a>

      <div class="menu-section">Manajemen</div>

      <a href="{{ route('admin.karyawan.index') }}"
         class="menu-item {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Data Karyawan
      </a>

      <div class="menu-section">Akun</div>

      <a href="{{ route('admin.notifikasi') }}"
         class="menu-item {{ request()->routeIs('admin.notifikasi') ? 'active' : '' }}">
        <i class="bi bi-bell"></i> Notifikasi
        @php $badgeNotif = \App\Http\Controllers\Admin\NotifikasiController::getBadgeCount(); @endphp
        @if($badgeNotif > 0)
          <span style="margin-left:auto;background:rgba(220,38,38,.85);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px">{{ $badgeNotif }}</span>
        @endif
      </a>

      <a href="{{ route('admin.profil') }}"
         class="menu-item {{ request()->routeIs('admin.profil') ? 'active' : '' }}">
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
    <div class="topbar">
      <div class="topbar-title">@yield('topbar-title')</div>
      <div class="topbar-actions">
        <div style="font-size:12px;color:#64748B;background:#F1F5F9;padding:6px 14px;border-radius:8px;">
          <i class="bi bi-person-badge" style="margin-right:4px;color:#0055A5"></i>
          <strong style="color:#0055A5">Divisi Umum</strong>
        </div>
        {{-- Bell notifikasi --}}
        <a href="{{ route('admin.notifikasi') }}" class="topbar-icon-btn" title="Notifikasi">
          <i class="bi bi-bell"></i>
          @php $badgeNotif = \App\Http\Controllers\Admin\NotifikasiController::getBadgeCount(); @endphp
          @if($badgeNotif > 0)
            <span class="notif-badge">{{ $badgeNotif > 9 ? '9+' : $badgeNotif }}</span>
          @endif
        </a>
        {{-- Profil --}}
        <a href="{{ route('admin.profil') }}" class="topbar-icon-btn" title="Profil Saya">
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

<div id="confirm-modal-overlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.45);backdrop-filter:blur(4px);align-items:center;justify-content:center;" onclick="_confirmHandleOverlayClick(event)">
  <div id="confirm-modal-box" style="background:#fff;border-radius:20px;padding:36px 32px 28px;max-width:420px;width:90%;box-shadow:0 24px 60px rgba(15,23,42,.18);transform:scale(.92);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease;position:relative;">
    <div id="confirm-modal-icon-wrap" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;background:#FEF2F2;">
      <i id="confirm-modal-icon" class="bi bi-question-circle-fill" style="font-size:28px;color:#DC2626"></i>
    </div>
    <h3 id="confirm-modal-title" style="text-align:center;font-family:'Plus Jakarta Sans',sans-serif;font-size:1.1rem;font-weight:700;color:#1e293b;margin:0 0 8px;">Konfirmasi</h3>
    <p id="confirm-modal-message" style="text-align:center;font-size:.875rem;color:#64748b;line-height:1.6;margin:0 0 28px;">Apakah Anda yakin?</p>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button onclick="hideConfirm()" style="flex:1;padding:11px 20px;border-radius:12px;border:1.5px solid #e2e8f0;background:#f8fafc;font-family:'Plus Jakarta Sans',sans-serif;font-size:.875rem;font-weight:600;color:#64748b;cursor:pointer;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
        <i class="bi bi-x-lg" style="margin-right:6px"></i>Batal
      </button>
      <button id="confirm-modal-confirm-btn" style="flex:1;padding:11px 20px;border-radius:12px;border:none;background:#DC2626;color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:.875rem;font-weight:600;cursor:pointer;">
        <i id="confirm-modal-confirm-icon" class="bi bi-check-lg" style="margin-right:6px"></i>
        <span id="confirm-modal-confirm-text">Ya, Lanjutkan</span>
      </button>
    </div>
  </div>
</div>
<script>
var _confirmCallback=null;
function doLogout(formId){showConfirm({title:'Konfirmasi Keluar',message:'Apakah Anda yakin ingin keluar dari sistem SIBAS?',icon:'bi-box-arrow-right',iconColor:'#DC2626',confirmText:'Ya, Keluar',confirmClass:'confirm-btn-danger',onConfirm:function(){document.getElementById(formId).submit();}});}
function showConfirm(opts){opts=opts||{};var overlay=document.getElementById('confirm-modal-overlay');var box=document.getElementById('confirm-modal-box');var iconWrap=document.getElementById('confirm-modal-icon-wrap');var icon=document.getElementById('confirm-modal-icon');var title=document.getElementById('confirm-modal-title');var msg=document.getElementById('confirm-modal-message');var btn=document.getElementById('confirm-modal-confirm-btn');var btnIcon=document.getElementById('confirm-modal-confirm-icon');var btnText=document.getElementById('confirm-modal-confirm-text');title.textContent=opts.title||'Konfirmasi';msg.textContent=opts.message||'Apakah Anda yakin?';btnText.textContent=opts.confirmText||'Ya, Lanjutkan';icon.className='bi '+(opts.icon||'bi-question-circle-fill');var iconColor=opts.iconColor||'#2563EB';icon.style.color=iconColor;iconWrap.style.background=iconColor+'1a';var cc=opts.confirmClass||'confirm-btn-primary';if(cc==='confirm-btn-danger'){btn.style.background='#DC2626';btnIcon.className='bi bi-x-lg';}else if(cc==='confirm-btn-success'){btn.style.background='#059669';btnIcon.className='bi bi-check-lg';}else if(cc==='confirm-btn-warning'){btn.style.background='#D97706';btnIcon.className='bi bi-check-lg';}else{btn.style.background='#0055A5';btnIcon.className='bi bi-check-lg';}if(opts.confirmIcon)btnIcon.className='bi '+opts.confirmIcon;_confirmCallback=opts.onConfirm||null;btn.onclick=function(){hideConfirm();if(_confirmCallback)setTimeout(_confirmCallback,120);};overlay.style.display='flex';requestAnimationFrame(function(){requestAnimationFrame(function(){box.style.transform='scale(1)';box.style.opacity='1';});});document.addEventListener('keydown',_confirmKeyHandler);}
function hideConfirm(){var overlay=document.getElementById('confirm-modal-overlay');var box=document.getElementById('confirm-modal-box');box.style.transform='scale(.92)';box.style.opacity='0';setTimeout(function(){overlay.style.display='none';},200);document.removeEventListener('keydown',_confirmKeyHandler);}
function _confirmKeyHandler(e){if(e.key==='Escape')hideConfirm();}
function _confirmHandleOverlayClick(e){if(e.target===document.getElementById('confirm-modal-overlay'))hideConfirm();}
</script>
</body>
</html>
