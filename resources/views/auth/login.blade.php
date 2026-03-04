<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — SIBAS Inventori BPS</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>
</head>
<body>

<div class="login-page">
  {{-- LEFT PANEL --}}
  <div class="login-left">
    <div class="login-logo-wrap">
      <img src="{{ asset('images/logo-bps.png') }}" alt="BPS Logo"
           onerror="this.style.display='none';document.getElementById('logo-fallback').style.display='flex'"/>
      <div id="logo-fallback" style="display:none;align-items:center;gap:8px;color:#fff">
        <i class="bi bi-bar-chart-fill" style="font-size:30px"></i>
        <span style="font-size:17px;font-weight:800">BPS</span>
      </div>
    </div>

    <h1>Sistem Inventori Barang<br/>BPS Republik Indonesia</h1>
    <p>Platform pengelolaan inventori terpadu untuk<br/>mendukung operasional BPS secara efisien.</p>

    <div class="login-stats">
      <div class="login-stat">
        <div class="num">{{ \App\Models\Barang::count() }}</div>
        <div class="lbl">Total Barang</div>
      </div>
      <div class="login-stat">
        <div class="num">{{ \App\Models\User::count() }}</div>
        <div class="lbl">Pengguna</div>
      </div>
      <div class="login-stat">
        <div class="num">{{ \App\Models\Pemakaian::where('status','pending')->count() }}</div>
        <div class="lbl">Permintaan</div>
      </div>
    </div>
  </div>

  {{-- RIGHT PANEL --}}
  <div class="login-right">
    <div class="login-card">
      <h2>Selamat Datang 👋</h2>
      <p class="sub">Masuk ke akun Anda untuk melanjutkan</p>

      @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:18px">
          <i class="bi bi-x-circle-fill"></i>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="form-group" style="margin-bottom:14px">
          <label class="form-group label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Email</label>
          <div class="input-icon-wrap">
            <i class="bi bi-envelope icon"></i>
            <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   type="email" name="email" placeholder="Masukkan email"
                   value="{{ old('email') }}" required autocomplete="email"/>
          </div>
          @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:20px">
          <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Password</label>
          <div class="input-icon-wrap">
            <i class="bi bi-lock icon"></i>
            <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   type="password" name="password" id="pwd"
                   placeholder="Masukkan password" required autocomplete="current-password"/>
            <i class="bi bi-eye eye-toggle" onclick="togglePassword('pwd', this)"></i>
          </div>
          @error('password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right" style="margin-right:6px"></i>
          Masuk ke Sistem
        </button>
      </form>

      <p class="login-footer">© {{ date('Y') }} Badan Pusat Statistik · SIBAS v1.0</p>
    </div>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
