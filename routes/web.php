<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// PUBLIC
use App\Http\Controllers\FrontAntrianController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TvAntrianController;
use App\Http\Livewire\Antrian\StatusAntrian;

// SUPER ADMIN
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardLaporanController;
use App\Http\Controllers\DashboardPasienController;
use App\Http\Controllers\DashboardAnalyticsController;
use App\Http\Controllers\DashboardRekapController;
use App\Http\Controllers\DashboardAuditLogController;

// DOKTER
use App\Http\Controllers\Dokter\DokterDashboardController;
use App\Http\Controllers\Dokter\DokterAntrianController;
use App\Http\Controllers\Dokter\RekamMedikController;

/*
|--------------------------------------------------------------------------
| PUBLIC (PASIEN)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('home'))->name('home');

Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');

// Cari NIK
Route::get('/antrian/cari', [FrontAntrianController::class, 'showCariAntrianForm'])->name('antrian.cari');
Route::post('/antrian/cari', [FrontAntrianController::class, 'searchByNik'])->name('antrian.cari.proses');

// Status (QR) - Livewire
Route::get('/antrian/status/{antrian}', StatusAntrian::class)->name('antrian.status');

// Tiket
Route::get('/antrian/tiket/{antrian}', [FrontAntrianController::class, 'tiketAntrian'])->name('antrian.tiket');

// Rekam Medik untuk pasien (lihat diagnosa/catatan/resep)
Route::get('/antrian/{antrian}/rekam-medik', [FrontAntrianController::class, 'rekamMedik'])
    ->name('antrian.rekam-medik');

// Profil pasien / kartu pasien
Route::get('/pasien/{patient}', [FrontAntrianController::class, 'profilPasien'])->name('pasien.profil');
Route::get('/pasien/kartu/{patient}', [FrontAntrianController::class, 'kartuPasien'])->name('pasien.kartu');

// Resource antrian
Route::resource('antrian', FrontAntrianController::class);

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Auth::routes(['register' => false]);

// default Laravel /home -> arahkan ke /dashboard sesuai role
Route::middleware('auth')->get('/home', function () {
    return redirect()->route('dashboard.redirect');
})->name('home.redirect');

/*
|--------------------------------------------------------------------------
| STAFF LOGIN ENTRY (Masuk Staff)
|--------------------------------------------------------------------------
| - Klik tombol "Masuk Staff" -> /staff
| - Jika belum login: set intended ke /staff/redirect -> buka login
| - Jika sudah login: langsung ke /staff/redirect
*/
Route::get('/staff', function () {
    if (Auth::check()) {
        return redirect()->route('staff.redirect');
    }

    // set tujuan setelah login (khusus jalur staff)
    session(['url.intended' => route('staff.redirect')]);

    // arahkan ke login bawaan laravel
    return redirect()->route('login');
})->name('staff.login');

Route::middleware('auth')->get('/staff/redirect', function () {
    $user = auth()->user();

    // ambil role paling aman (Spatie -> kolom -> relasi)
    $roleRaw = '';

    if ($user && method_exists($user, 'getRoleNames')) {
        $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
    }

    if ($roleRaw === '') {
        $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
    }

    $role = strtolower(str_replace(' ', '_', trim($roleRaw)));

    // ✅ mapping dashboard sesuai role project kamu
    if ($role === 'super_admin') return redirect()->route('admin.dashboard');
    if (str_starts_with($role, 'dokter_')) return redirect()->route('dokter.dashboard');

    // fallback (kalau staff role lain)
    return redirect()->route('dashboard.redirect');
})->name('staff.redirect');

/*
|--------------------------------------------------------------------------
| TV ANTRIAN (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/tv-antrian', [TvAntrianController::class, 'index'])->name('tv.antrian');
Route::get('/tv-antrian/data', [TvAntrianController::class, 'data'])->name('tv.data');

/*
|--------------------------------------------------------------------------
| SUPER ADMIN (ROLE: super_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [DashboardAnalyticsController::class, 'index'])->name('analytics');

        Route::get('/dashboard/pasien', [DashboardPasienController::class, 'index'])->name('pasien.index');
        Route::get('/dashboard/pasien/{patient}', [DashboardPasienController::class, 'show'])->name('pasien.show');

        Route::get('/dashboard/laporan/index', [DashboardLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/dashboard/laporan/cetak-pdf', [DashboardLaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/dashboard/laporan/export-excel', [DashboardLaporanController::class, 'exportExcelCsv'])->name('laporan.excel');

        Route::get('/dashboard/laporan/rekap-pdf', [DashboardRekapController::class, 'exportPdf'])->name('rekap.pdf');
        Route::get('/dashboard/laporan/rekap-excel', [DashboardRekapController::class, 'exportExcel'])->name('rekap.excel');

        Route::get('/audit', [DashboardAuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/{audit}', [DashboardAuditLogController::class, 'show'])->name('audit.show');
    });

/*
|--------------------------------------------------------------------------
| DOKTER (ROLE prefix: dokter_)
|--------------------------------------------------------------------------
| NOTE: kamu pakai middleware custom role_prefix:dokter_
*/
Route::middleware(['auth', 'role_prefix:dokter_'])
    ->prefix('dokter')
    ->name('dokter.')
    ->group(function () {

        Route::get('/dashboard', [DokterDashboardController::class, 'index'])->name('dashboard');

        Route::get('/antrian', [DokterAntrianController::class, 'index'])->name('antrian.index');

        Route::post('/antrian/{antrianId}/panggil', [DokterAntrianController::class, 'panggil'])->name('antrian.panggil');
        Route::post('/antrian/{antrianId}/panggil-ulang', [DokterAntrianController::class, 'panggilUlang'])->name('antrian.panggilUlang');
        Route::post('/antrian/{antrianId}/mulai', [DokterAntrianController::class, 'mulai'])->name('antrian.mulai');
        Route::post('/antrian/{antrianId}/selesai', [DokterAntrianController::class, 'selesai'])->name('antrian.selesai');
        Route::post('/antrian/{antrianId}/lewati', [DokterAntrianController::class, 'lewati'])->name('antrian.lewati');

        // ✅ FIX: route Tidak Hadir (TIDAK double prefix, TIDAK double name)
        Route::post('/antrian/{antrianId}/tidak-hadir', [DokterAntrianController::class, 'tidakHadir'])
  ->name('antrian.tidakHadir');

        Route::post('/antrian/{antrianId}/rekam-medik', [RekamMedikController::class, 'store'])->name('rekam-medik.store');

        Route::get('/riwayat/{noKtp}/modal', [RekamMedikController::class, 'modal'])->name('riwayat.modal');

        Route::get('/riwayat', [RekamMedikController::class, 'index'])->name('riwayat.index');
        Route::get('/riwayat/{noKtp}', [RekamMedikController::class, 'riwayat'])->name('riwayat.show');
    });

/*
|--------------------------------------------------------------------------
| /dashboard redirect sesuai role
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();

    $roleRaw = '';

    if ($user && method_exists($user, 'getRoleNames')) {
        $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
    }

    if ($roleRaw === '') {
        $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
    }

    $role = strtolower(str_replace(' ', '_', trim($roleRaw)));

    if ($role === 'super_admin') return redirect()->route('admin.dashboard');
    if (str_starts_with($role, 'dokter_')) return redirect()->route('dokter.dashboard');

    return redirect()->route('home');
})->name('dashboard.redirect');
