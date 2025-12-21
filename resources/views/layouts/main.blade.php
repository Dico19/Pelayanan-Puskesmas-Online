<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Antrian Online Puskesmas Kaligandu')</title>

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
          rel="stylesheet">

    {{-- ✅ Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- Vendor CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">

    <!-- Main Template CSS -->
    <link href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}" rel="stylesheet">

    <!-- ✅ CSS ANTRIAN -->
    <link href="{{ asset('assets/css/antrian-status.css') }}?v={{ filemtime(public_path('assets/css/antrian-status.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/antrian-ui.css') }}?v={{ filemtime(public_path('assets/css/antrian-ui.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/antrian-page.css') }}?v={{ filemtime(public_path('assets/css/antrian-page.css')) }}" rel="stylesheet">

    <!-- ✅ DARK MODE GLOBAL -->
    <link href="{{ asset('assets/css/pk-dark.css') }}?v={{ filemtime(public_path('assets/css/pk-dark.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/css/pk-hasil-antrian.css') }}?v={{ filemtime(public_path('assets/css/pk-hasil-antrian.css')) }}" rel="stylesheet">
    
    <!-- ✅ CSS REKAM MEDIK (baru) -->
<link href="{{ asset('assets/css/antrian-rekam.css') }}?v={{ filemtime(public_path('assets/css/antrian-rekam.css')) }}" rel="stylesheet">

    {{-- ✅ FIX: Navbar selalu bisa diklik (z-index + anti overlay) --}}
    <style>
        /* Header/Topbar selalu paling atas */
        #topbar { z-index: 3000 !important; }
        #header { z-index: 2999 !important; }

        /* Kalau ada section overlay/pseudo element yang nutup header, jangan makan klik */
        .pk-queue-status,
        .pk-queue-status::before,
        .pk-queue-status::after {
            pointer-events: none;
        }
        /* Tapi konten di dalamnya tetap bisa diklik */
        .pk-queue-status .container,
        .pk-queue-status .container * {
            pointer-events: auto;
        }
    </style>

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- CSS khusus per halaman --}}
    @stack('styles')
</head>

<body class="@yield('body-class')">

    {{-- Navbar --}}
    @include('partials.navbar')

    <main id="main" class="main">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot }}
        @endif
    </main>

    {{-- Footer --}}
    @include('partials.footer')

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>

    <!-- Template JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- ✅ DARK MODE TOGGLE JS --}}
    <script src="{{ asset('assets/js/dark-mode.js') }}?v={{ filemtime(public_path('assets/js/dark-mode.js')) }}"></script>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- ✅ FIX: Backdrop/modal nyangkut bikin navbar ga bisa diklik --}}
    <script>
        // Bootstrap Modal: kalau backdrop nyangkut, klik navbar (termasuk toggle gelap/terang) jadi mati.
        document.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
        }, true);

        // Offcanvas (opsional) - jaga-jaga kalau suatu saat pakai offcanvas
        document.addEventListener('hidden.bs.offcanvas', function () {
            document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
            document.body.classList.remove('offcanvas-open');
            document.body.style.removeProperty('padding-right');
        }, true);

        // Livewire navigasi / render ulang kadang bikin backdrop tersisa:
        document.addEventListener('livewire:navigated', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open', 'offcanvas-open');
            document.body.style.removeProperty('padding-right');
        });
    </script>

    {{-- Script khusus per halaman --}}
    @stack('scripts')
    @yield('script')
</body>
</html>
