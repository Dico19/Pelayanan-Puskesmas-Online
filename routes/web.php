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
use App\Http\Controllers\DashboardAuditLogController; // ✅ FIX

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

// Rekam Medik untuk pasien (lihat diagnosa/catatan/resep) - hanya jika sudah dipanggil
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

Route::middleware(['auth'])->get('/home', function () {
    return redirect()->route('dashboard.redirect');
})->name('home.redirect');

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

        // ✅ AUDIT LOG (FINAL)
        Route::get('/audit', [DashboardAuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/{audit}', [DashboardAuditLogController::class, 'show'])->name('audit.show');
    });

/*
|--------------------------------------------------------------------------
| DOKTER (ROLE: dokter_*)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dokter_*'])
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
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    $roleRaw = $user?->role?->role ?? $user?->role ?? '';
    $role = strtolower(str_replace(' ', '_', trim((string) $roleRaw)));

    if ($role === 'super_admin') return redirect()->route('admin.dashboard');
    if (str_starts_with($role, 'dokter_')) return redirect()->route('dokter.dashboard');

    return redirect()->route('home');
})->name('dashboard.redirect');
