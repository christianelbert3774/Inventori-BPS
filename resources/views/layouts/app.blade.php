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
      <a href="#" class="menu-item">
        <i class="bi bi-person-circle"></i> Profil Saya
      </a>
    </nav>

    <div class="sidebar-footer">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">
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
      <div class="topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari barang..."/>
      </div>
      <div class="topbar-actions">
        <div class="topbar-icon-btn">
          <i class="bi bi-bell"></i>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button type="submit" class="topbar-icon-btn" title="Keluar">
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
</body>
</html>
