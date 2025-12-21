<!-- resources/views/partials/navbar.blade.php -->

<!-- ======= Top Bar ======= -->
<div id="topbar" class="d-flex align-items-center fixed-top">
    <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">

            <i class="bi bi-envelope"></i>
            <a href="mailto:puskesmaskaligandu@gmail.com" class="contact-email">
                puskesmaskaligandu@gmail.com
            </a>

            <i class="bi bi-phone ms-4"></i>
            <a href="tel:+62895404905070" class="contact-phone">
                +62 8954 0490 5070
            </a>

        </div>
    </div>
</div>

<!-- ======= Header ======= -->
<header id="header" class="fixed-top">
    <div class="container d-flex align-items-center">

        <h1 class="logo me-auto">
            <a href="/">PUSKESMAS Kaligandu</a>
        </h1>

        <nav id="navbar" class="navbar order-last order-lg-0">
            <ul>
                <li>
                    <a class="nav-link scrollto {{ request()->is('/') ? 'active' : '' }}" href="/#hero">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>

                <li>
                    <a class="nav-link scrollto {{ request()->is('antrian') ? 'active' : '' }}" href="{{ url('/antrian') }}">
                        <i class="bi bi-journal-text me-1"></i> Antrian
                    </a>
                </li>

                <li>
                    <a class="nav-link scrollto {{ request()->is('antrian/cari') || request()->is('antrian/cari/*') ? 'active' : '' }}"
                       href="{{ route('antrian.cari') }}">
                        <i class="bi bi-person-badge me-1"></i> Antrianku
                    </a>
                </li>

                <li>
                    <a class="nav-link scrollto" href="/#contact">
                        <i class="bi bi-telephone me-1"></i> Contact
                    </a>
                </li>

                {{-- ✅ Tombol Masuk Staff (di dalam navbar, ikut mobile menu) --}}
                @guest
                    <li class="d-lg-none mt-2">
                        <a class="nav-link scrollto" href="{{ route('staff.login') }}">
                            <i class="bi bi-person-workspace me-1"></i> Masuk Staff
                        </a>
                    </li>
                @endguest
            </ul>

            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>

        {{-- ✅ Tombol Masuk Staff (di kanan navbar, desktop) --}}
        @guest
            <a href="{{ route('staff.login') }}"
               class="btn btn-sm btn-primary rounded-pill ms-3 ms-lg-3 d-none d-lg-inline-flex align-items-center gap-1"
               style="white-space:nowrap;">
                <i class="bi bi-person-workspace"></i>
                <span>Masuk Staff</span>
            </a>
        @endguest

        {{-- ✅ Dark/Light Toggle Button (posisi rapih kanan) --}}
        <button
            type="button"
            id="darkModeToggle"
            class="btn btn-sm btn-outline-secondary rounded-pill ms-3 ms-lg-3"
            style="white-space:nowrap;"
        >
            <i class="bi bi-moon-stars me-1"></i>
            <span class="dm-text">Gelap</span>
        </button>

        {{-- Dropdown Admin --}}
        @if (request()->is('admin*'))
            @auth
                @if (auth()->user()->role_id == 1)
                    <div class="dropdown ms-3">
                        <button class="btn btn-success dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </button>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/admin/dashboard">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>

                            <li>
                                <form action="/logout" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            @endauth
        @endif

    </div>
</header>
