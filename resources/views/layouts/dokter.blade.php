@php
  $user = auth()->user();
  $roleName = $user?->role?->role ?? $user?->role ?? '';
@endphp

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dashboard Dokter')</title>

  <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/puskesmas_theme.css') }}">

  {{-- ✅ Bootstrap Icons (untuk icon sidebar & tombol) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body { background:#f4f7fb; }
    .sidebar {
      width:260px; min-height:100vh;
      background: linear-gradient(180deg, #0b2a63 0%, #0a2b6d 100%);
      color:#fff; position:fixed; left:0; top:0;
      padding:18px;
    }
    .sidebar .brand {
      background: rgba(255,255,255,.08);
      border-radius: 14px;
      padding: 12px 14px;
      margin-bottom: 16px;
      display:flex; gap:10px; align-items:center;
    }
    .avatar {
      width:38px; height:38px; border-radius:12px;
      background: rgba(255,255,255,.18);
      display:flex; align-items:center; justify-content:center;
      font-weight:900;
    }
    .menu-title { font-size:12px; opacity:.7; margin: 14px 0 8px; }
    .nav-pill {
      display:flex; align-items:center; gap:10px;
      padding:10px 12px;
      border-radius: 12px;
      color:#dbe7ff;
      text-decoration:none;
      margin-bottom:8px;
      background: rgba(255,255,255,.06);
    }
    .nav-pill.active { background: rgba(255,255,255,.14); color:#fff; font-weight:800; }
    .nav-pill .bi { font-size: 16px; line-height: 1; }
    .logout-btn {
      position:absolute; left:18px; right:18px; bottom:18px;
      border-radius: 14px; font-weight:900;
    }
    .content {
      margin-left:260px;
      padding: 22px 26px;
    }
    .card-soft {
      border:0; border-radius:16px;
      box-shadow: 0 10px 30px rgba(17,24,39,.08);
    }
  </style>
</head>
<body>

  <aside class="sidebar">
    <div class="brand">
      <div class="avatar">{{ strtoupper(substr($user?->name ?? 'D', 0, 1)) }}</div>
      <div>
        <div style="font-weight:900; line-height:1.1;">Dashboard Dokter</div>
        <div style="opacity:.8; font-size:12px;">{{ $user?->name ?? '-' }}</div>
      </div>
    </div>

    <div class="menu-title">MENU</div>

    <a class="nav-pill {{ request()->routeIs('dokter.dashboard') ? 'active' : '' }}"
       href="{{ route('dokter.dashboard') }}">
      <i class="bi bi-house-door-fill"></i> <span>Dashboard</span>
    </a>

    {{-- ✅ Daftar Antrian --}}
    <a class="nav-pill {{ request()->routeIs('dokter.antrian.*') ? 'active' : '' }}"
       href="{{ route('dokter.antrian.index') }}">
      <i class="bi bi-card-checklist"></i> <span>Daftar Antrian</span>
    </a>

    {{-- ✅ Riwayat (sidebar) --}}
    <a class="nav-pill {{ request()->routeIs('dokter.riwayat.*') ? 'active' : '' }}"
       href="{{ route('dokter.riwayat.index') }}">
      <i class="bi bi-clock-history"></i> <span>Riwayat</span>
    </a>

    <a class="btn btn-danger logout-btn"
       href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="bi bi-box-arrow-right me-1"></i> Logout
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
      @csrf
    </form>
  </aside>

  <main class="content">
    @yield('content')
  </main>

  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
