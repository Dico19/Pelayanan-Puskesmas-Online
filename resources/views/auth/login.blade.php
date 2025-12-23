<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login Admin - Antrian Online Puskesmas</title>

    <!-- Bootstrap & Icons (sesuaikan jika foldermu beda) -->
    <link href="/assetsDashboard/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assetsDashboard/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assetsDashboard/css/style.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top left, #4da3ff 0, #1f4690 35%, #0f172a 80%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ✅ tombol kembali */
        .pk-login-back{
            position: fixed;
            top: 18px;
            left: 18px;
            z-index: 9999;

            display: inline-flex;
            align-items: center;
            gap: 10px;

            padding: 10px 14px;
            border-radius: 999px;

            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
            text-decoration: none;

            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);

            box-shadow: 0 12px 30px rgba(0,0,0,.25);
            transition: .2s ease;
        }
        .pk-login-back:hover{
            transform: translateY(-1px);
            background: rgba(255,255,255,.26);
            color: #fff;
        }
        .pk-login-back i{
            font-size: 18px;
            line-height: 1;
        }
        .pk-login-back span{
            font-weight: 700;
            font-size: 13px;
            letter-spacing: .2px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .auth-header {
            text-align: center;
            padding: 24px 24px 12px;
        }

        .auth-logo {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.22);
        }

        .auth-logo img.login-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .auth-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        .auth-subtitle {
            font-size: 13px;
            color: #6b7280;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
        }

        .form-control {
            border-radius: 10px;
            border-color: #d1d5db;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.3);
        }

        .btn-login {
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.3px;
            font-size: 14px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border: none;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.45);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .auth-footer {
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            padding: 10px 18px 18px;
        }

        .auth-footer span {
            color: #0f172a;
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            font-size: 13px;
        }

        /* Password eye icon */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #7d8da5;
        }

        .password-wrapper .toggle-password:hover {
            color: #4b5563;
        }

        @media (max-width: 576px) {
            body {
                background: #0f172a;
            }
            .auth-wrapper {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

@php
    $backUrl = url()->previous();
    if (!$backUrl || str_contains($backUrl, '/login')) {
        $backUrl = url('/');
    }
@endphp

<a href="{{ $backUrl }}" class="pk-login-back">
    <i class="bi bi-arrow-left"></i>
    <span>Kembali</span>
</a>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-logo">
                {{-- Logo Puskesmas --}}
                <img src="/assets/img/logo-puskesmas.png" alt="Logo Puskesmas" class="login-logo">
            </div>
            <div class="auth-title">Masuk ke Akun Anda</div>
            <div class="auth-subtitle">Staff Pelayanan Online Puskesmas Kaligandu</div>
        </div>

        <div class="px-4 pb-2 pt-1">
            @if (session('status'))
                <div class="alert alert-success mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@puskesmas.com">
                </div>

                {{-- Password + toggle eye --}}
                <div class="mb-3">
                    <label for="password" class="form-label d-flex justify-content-between">
                        <span>Password</span>
                    </label>

                    <div class="password-wrapper">
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••">
                        <span class="toggle-password" onclick="togglePassword()" role="button" aria-label="Tampilkan/Sembunyikan password">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-grid mt-4 mb-2">
                    <button type="submit" class="btn btn-primary btn-login">
                        Masuk
                    </button>
                </div>

            </form>
        </div>

        <div class="auth-footer">
            Pelayanan Online <span>Puskesmas Kaligandu</span>
        </div>
    </div>
</div>

<script src="/assetsDashboard/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            passwordField.type = "password";
            icon.classList.add("bi-eye-slash");
            icon.classList.remove("bi-eye");
        }
    }
</script>

</body>
</html>
