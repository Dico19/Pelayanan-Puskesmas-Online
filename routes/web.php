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
use App\Http\Controllers\Dokter\DokterStatistikController;

/*
|--------------------------------------------------------------------------
| Helper: ambil role string (biar gak duplikat)
|--------------------------------------------------------------------------
*/
$resolveRoleKey = function ($user): string {
    if (!$user) return '';

    $roleRaw = '';

    // kalau pakai Spatie
    if (method_exists($user, 'getRoleNames')) {
        $roleRaw = (string) ($user->getRoleNames()->first() ?? '');
    }

    // fallback kalau bukan spatie
    if ($roleRaw === '') {
        $roleRaw = (string) (data_get($user, 'role.role') ?? data_get($user, 'role') ?? '');
    }

    return strtolower(str_replace(' ', '_', trim($roleRaw)));
};

/*
|--------------------------------------------------------------------------
| PUBLIC (PASIEN)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('home'))->name('home');

Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');

/**
 * Antrian - cari/status/tiket/rekam medik
 * ✅ routes spesifik HARUS di atas resource('antrian')
 */
Route::get('/antrian/cari', [FrontAntrianController::class, 'showCariAntrianForm'])
    ->name('antrian.cari');

Route::post('/antrian/cari', [FrontAntrianController::class, 'searchByNik'])
    ->name('antrian.cari.proses');

Route::get('/antrian/status/{antrian}', StatusAntrian::class)
    ->whereNumber('antrian')
    ->name('antrian.status');

Route::get('/antrian/tiket/{antrian}', [FrontAntrianController::class, 'tiketAntrian'])
    ->whereNumber('antrian')
    ->name('antrian.tiket');

Route::get('/antrian/{antrian}/rekam-medik', [FrontAntrianController::class, 'rekamMedik'])
    ->whereNumber('antrian')
    ->name('antrian.rekam-medik');

Route::get('/pasien/{patient}', [FrontAntrianController::class, 'profilPasien'])
    ->whereNumber('patient')
    ->name('pasien.profil');

Route::get('/pasien/kartu/{patient}', [FrontAntrianController::class, 'kartuPasien'])
    ->whereNumber('patient')
    ->name('pasien.kartu');

/**
 * Resource antrian (CRUD)
 * ✅ kunci parameter angka biar aman
 */
Route::resource('antrian', FrontAntrianController::class)
    ->where(['antrian' => '[0-9]+']);

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Auth::routes(['register' => false]);

Route::middleware('auth')->get('/home', function () {
    return redirect()->route('dashboard.redirect');
})->name('home.redirect');

/*
|--------------------------------------------------------------------------
| STAFF LOGIN ENTRY (Masuk Staff)
|--------------------------------------------------------------------------
*/
Route::get('/staff', function () {
    if (Auth::check()) {
        return redirect()->route('staff.redirect');
    }

    session(['url.intended' => route('staff.redirect')]);
    return redirect()->route('login');
})->name('staff.login');

Route::middleware('auth')->get('/staff/redirect', function () use ($resolveRoleKey) {
    $user = auth()->user();
    $role = $resolveRoleKey($user);

    if ($role === 'super_admin') return redirect()->route('admin.dashboard');
    if (str_starts_with($role, 'dokter_')) return redirect()->route('dokter.dashboard');

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

        // Pasien
        Route::get('/dashboard/pasien', [DashboardPasienController::class, 'index'])->name('pasien.index');

        Route::get('/dashboard/pasien/{patient}', [DashboardPasienController::class, 'show'])
            ->whereNumber('patient')
            ->name('pasien.show');

        /**
         * ✅ UNBLOCK NIK (tombol di halaman riwayat pasien)
         * Controller: DashboardPasienController@unblock
         * Route name: admin.pasien.unblock  (INI YANG DIPANGGIL DI BLADE)
         */
        Route::post('/dashboard/pasien/{patient}/unblock', [DashboardPasienController::class, 'unblock'])
            ->whereNumber('patient')
            ->name('pasien.unblock');

        // Laporan
        Route::get('/dashboard/laporan/index', [DashboardLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/dashboard/laporan/cetak-pdf', [DashboardLaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/dashboard/laporan/export-excel', [DashboardLaporanController::class, 'exportExcelCsv'])->name('laporan.excel');

        Route::get('/dashboard/laporan/rekap-pdf', [DashboardRekapController::class, 'exportPdf'])->name('rekap.pdf');
        Route::get('/dashboard/laporan/rekap-excel', [DashboardRekapController::class, 'exportExcel'])->name('rekap.excel');

        // Audit
        Route::get('/audit', [DashboardAuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/{audit}', [DashboardAuditLogController::class, 'show'])
            ->whereNumber('audit')
            ->name('audit.show');
    });

/*
|--------------------------------------------------------------------------
| DOKTER (ROLE prefix: dokter_)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role_prefix:dokter_'])
    ->prefix('dokter')
    ->name('dokter.')
    ->group(function () {

        Route::get('/dashboard', [DokterDashboardController::class, 'index'])->name('dashboard');

        Route::get('/statistik', [DokterStatistikController::class, 'index'])->name('statistik.index');
        Route::get('/statistik/data', [DokterStatistikController::class, 'data'])->name('statistik.data');

        Route::get('/antrian', [DokterAntrianController::class, 'index'])->name('antrian.index');

        Route::post('/antrian/{antrianId}/panggil', [DokterAntrianController::class, 'panggil'])
            ->whereNumber('antrianId')
            ->name('antrian.panggil');

        Route::post('/antrian/{antrianId}/panggil-ulang', [DokterAntrianController::class, 'panggilUlang'])
            ->whereNumber('antrianId')
            ->name('antrian.panggilUlang');

        Route::post('/antrian/{antrianId}/mulai', [DokterAntrianController::class, 'mulai'])
            ->whereNumber('antrianId')
            ->name('antrian.mulai');

        Route::post('/antrian/{antrianId}/selesai', [DokterAntrianController::class, 'selesai'])
            ->whereNumber('antrianId')
            ->name('antrian.selesai');

        Route::post('/antrian/{antrianId}/lewati', [DokterAntrianController::class, 'lewati'])
            ->whereNumber('antrianId')
            ->name('antrian.lewati');

        Route::post('/antrian/{antrianId}/tidak-hadir', [DokterAntrianController::class, 'tidakHadir'])
            ->whereNumber('antrianId')
            ->name('antrian.tidakHadir');

        Route::get('/antrian/stats-hari-ini', [DokterAntrianController::class, 'statsHariIni'])
            ->name('antrian.statsHariIni');

        Route::post('/antrian/reset-hari-ini', [DokterAntrianController::class, 'resetHariIni'])
            ->name('antrian.resetHariIni');

        Route::post('/antrian/{antrianId}/rekam-medik', [RekamMedikController::class, 'store'])
            ->whereNumber('antrianId')
            ->name('rekam-medik.store');

        // ✅ urutan modal dulu baru show supaya tidak ketangkep {noKtp}
        Route::get('/riwayat/{noKtp}/modal', [RekamMedikController::class, 'modal'])
            ->where('noKtp', '[0-9]{8,25}')
            ->name('riwayat.modal');

        Route::get('/riwayat', [RekamMedikController::class, 'index'])->name('riwayat.index');

        Route::get('/riwayat/{noKtp}', [RekamMedikController::class, 'riwayat'])
            ->where('noKtp', '[0-9]{8,25}')
            ->name('riwayat.show');
    });

/*
|--------------------------------------------------------------------------
| /dashboard redirect sesuai role
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', function () use ($resolveRoleKey) {
    $user = auth()->user();
    $role = $resolveRoleKey($user);

    if ($role === 'super_admin') return redirect()->route('admin.dashboard');
    if (str_starts_with($role, 'dokter_')) return redirect()->route('dokter.dashboard');

    return redirect()->route('home');
})->name('dashboard.redirect');
