@php
  $user = auth()->user();

  // role name (opsional, buat backup deteksi poli)
  $roleName  = $user?->role?->role ?? $user?->role ?? '';
  $roleLower = strtolower(trim((string) $roleName));

  // ====== Poli detection (prioritas: field poli di user, fallback: dari roleName) ======
  $rawPoli = $user?->poli ?? $user?->poli_name ?? $user?->nama_poli ?? null;
  $poliKey = strtolower(trim((string) $rawPoli));

  // fallback kalau field poli di user kosong: ambil dari role name
  if ($poliKey === '') {
      if (str_contains($roleLower, 'gigi')) $poliKey = 'gigi';
      elseif (str_contains($roleLower, 'umum')) $poliKey = 'umum';
      elseif (str_contains($roleLower, 'tht')) $poliKey = 'tht';
      elseif (str_contains($roleLower, 'balita')) $poliKey = 'balita';
      elseif (str_contains($roleLower, 'kia') || str_contains($roleLower, 'kb')) $poliKey = 'kia & kb';
      elseif (str_contains($roleLower, 'nifas') || str_contains($roleLower, 'pnc')) $poliKey = 'nifas/pnc';
      elseif (str_contains($roleLower, 'lansia') || str_contains($roleLower, 'disabil')) $poliKey = 'lansia & disabilitas';
      else $poliKey = 'umum';
  }

  // ====== Poli meta ======
  $poliMeta = [
    'umum' => [
      'label'  => 'Poli Umum',
      'icon'   => 'bi-person-badge',
      'accent' => '#2b6fff',
    ],
    'gigi' => [
      // BI tidak punya tooth, jadi pakai icon medis yg aman
      'label'  => 'Poli Gigi',
      'icon'   => 'bi-bandaid',
      'accent' => '#ffb020',
    ],
    'tht' => [
      'label'  => 'Poli THT',
      'icon'   => 'bi-ear',
      'accent' => '#29b6f6',
    ],
    'balita' => [
      'label'  => 'Poli Balita',
      'icon'   => 'bi-people',
      'accent' => '#22c55e',
    ],
    'kia & kb' => [
      'label'  => 'Poli KIA & KB',
      'icon'   => 'bi-heart-pulse',
      'accent' => '#ef4444',
    ],
    'nifas/pnc' => [
      'label'  => 'Poli Nifas / PNC',
      'icon'   => 'bi-hospital',
      'accent' => '#8b5cf6',
    ],
    'lansia & disabilitas' => [
      'label'  => 'Poli Lansia & Disabilitas',
      'icon'   => 'bi-person-wheelchair',
      'accent' => '#111827',
    ],
  ];

  $meta = $poliMeta[$poliKey] ?? [
    'label'  => 'Poli',
    'icon'   => 'bi-person-badge',
    'accent' => '#2b6fff',
  ];
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

  {{-- Bootstrap Icons --}}
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
      border: 1px solid rgba(255,255,255,.10);
      box-shadow: 0 10px 22px rgba(0,0,0,.12);
      position: relative;
      overflow: hidden;
    }
    .sidebar .brand:before{
      content:'';
      position:absolute; inset:-40px;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), transparent 60%);
      transform: rotate(8deg);
      pointer-events:none;
    }
    .sidebar .brand:after{
      content:'';
      position:absolute; left:0; top:0; bottom:0;
      width: 4px;
      background: {{ $meta['accent'] }};
      opacity: .9;
    }

    /* avatar -> ikon poli */
    .avatar {
      width:40px; height:40px; border-radius:12px;
      background: rgba(255,255,255,.18);
      display:flex; align-items:center; justify-content:center;
      position: relative;
      overflow: hidden;
      flex: 0 0 auto;
      border: 1px solid rgba(255,255,255,.16);
    }
    .avatar:after{
      content:'';
      position:absolute; inset:-20px;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.25), transparent 55%);
      transform: rotate(10deg);
    }
    .avatar i{
      position: relative;
      z-index: 2;
      font-size: 18px;
      color: #fff;
    }

    .brand-sub {
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
      margin-top:6px;
      position: relative;
      z-index: 2;
    }

    .chip {
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      color: #eaf1ff;
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
      transition: .15s ease;
      border: 1px solid rgba(255,255,255,.08);
    }
    .nav-pill:hover { background: rgba(255,255,255,.10); color:#fff; transform: translateY(-1px); }
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
  </style>
</head>
<body>

  <aside class="sidebar">
    <div class="brand">
      <div class="avatar" title="{{ $meta['label'] }}">
        <i class="bi {{ $meta['icon'] }}"></i>
      </div>

      <div style="position: relative; z-index: 2;">
        <div style="font-weight:900; line-height:1.1;">Dashboard Dokter</div>
        <div style="opacity:.95; font-size:12px; font-weight:800;">
          {{ $meta['label'] }}
        </div>

        <div class="brand-sub">
          <span class="chip">
            <i class="bi bi-person-circle"></i>
            {{ $user?->name ?? '-' }}
          </span>
          <span class="chip">
            <i class="bi bi-calendar-check"></i>
            {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
          </span>
        </div>
      </div>
    </div>

    <div class="menu-title">MENU</div>

    <a class="nav-pill {{ request()->routeIs('dokter.dashboard') ? 'active' : '' }}"
       href="{{ route('dokter.dashboard') }}">
      <i class="bi bi-house-door-fill"></i> <span>Dashboard</span>
    </a>

    <a class="nav-pill {{ request()->routeIs('dokter.antrian.*') ? 'active' : '' }}"
       href="{{ route('dokter.antrian.index') }}">
      <i class="bi bi-card-checklist"></i> <span>Daftar Antrian</span>
    </a>

    <a class="nav-pill {{ request()->routeIs('dokter.statistik.*') ? 'active' : '' }}"
       href="{{ route('dokter.statistik.index') }}">
      <i class="bi bi-graph-up-arrow"></i> <span>Statistik Poli</span>
    </a>

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
