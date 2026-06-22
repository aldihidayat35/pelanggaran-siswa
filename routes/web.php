<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\PelanggaranSiswa\DashboardController as PelanggaranDashboardController;
use App\Http\Controllers\Admin\PelanggaranSiswa\SiswaController;
use App\Http\Controllers\Admin\PelanggaranSiswa\KategoriPelanggaranController;
use App\Http\Controllers\Admin\PelanggaranSiswa\PelanggaranController;
use App\Http\Controllers\Admin\PelanggaranSiswa\PelanggaranSiswaController;
use App\Http\Controllers\Admin\PelanggaranSiswa\LaporanPelanggaranController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PelanggaranDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // App Settings
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update');

    // WhatsApp API Settings
    Route::post('/settings/whatsapp/health', [AppSettingController::class, 'checkWaHealth'])->name('settings.whatsapp.health');
    Route::post('/settings/whatsapp/test-send', [AppSettingController::class, 'testWaSend'])->name('settings.whatsapp.test-send');
    Route::get('/settings/whatsapp/logs', [AppSettingController::class, 'waLogs'])->name('settings.whatsapp.logs');
});

/*
|--------------------------------------------------------------------------
| Pelanggaran Siswa Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('pelanggaran-siswa')->name('pelanggaran-siswa.')->group(function () {


    // Siswa
    Route::resource('siswa', SiswaController::class);
    Route::post('/siswa/{siswa}/kirim-laporan', [SiswaController::class, 'kirimLaporan'])->name('siswa.kirim-laporan');

    // Kategori Pelanggaran
    Route::resource('kategori', KategoriPelanggaranController::class)->except(['show']);

    // Jenis Pelanggaran
    Route::resource('pelanggaran', PelanggaranController::class);

    // Riwayat Pelanggaran Siswa
    Route::resource('riwayat', PelanggaranSiswaController::class)->parameters([
        'riwayat' => 'pelanggaranSiswa',
    ]);

    // Laporan
    Route::get('/laporan', [LaporanPelanggaranController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf/{tipe}', [LaporanPelanggaranController::class, 'printPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel/{tipe}', [LaporanPelanggaranController::class, 'exportExcel'])->name('laporan.excel');
});

// Public Laporan Siswa (WhatsApp link targets this)
Route::get('/laporan-siswa/{token}', [\App\Http\Controllers\PublicLaporanController::class, 'show'])->name('pelanggaran-siswa.public-laporan');

// Face Recognition (LBPH) Routes for Guru/Admin
Route::middleware(['auth'])->group(function () {
    Route::get('/guru/attendance', [\App\Http\Controllers\Guru\FaceRecognitionController::class, 'index'])->name('guru.attendance');
    Route::post('/guru/face-recognition/scan', [\App\Http\Controllers\Guru\FaceRecognitionController::class, 'scan'])->name('guru.face-recognition.scan');
    Route::post('/guru/pelanggaran-siswa/store-from-face', [\App\Http\Controllers\Guru\FaceRecognitionController::class, 'storeFromFace'])->name('guru.pelanggaran-siswa.store-from-face');
});


