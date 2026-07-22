<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

// 1. Sisi User / Pegawai (Tanpa Login)
Route::get('/', [AttendanceController::class, 'home'])->name('home');
Route::get('/absensi', [AttendanceController::class, 'index'])->name('absensi.index');
Route::post('/absensi/simpan', [AttendanceController::class, 'store'])->name('absensi.store');

// Legacy redirects
Route::get('/absensi/form', [AttendanceController::class, 'showForm'])->name('absensi.form');
Route::get('/rekap', [AttendanceController::class, 'rekap'])->name('absensi.rekap');

// 2. Admin Authentication (Verifikasi PIN)
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// 3. Sisi Admin (Proteksi PIN Session)
Route::middleware(['admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // CRUD Kelola Pegawai (md_user)
    Route::post('/admin/pegawai/tambah', [AdminController::class, 'storeUser'])->name('admin.user.store');
    Route::post('/admin/pegawai/update/{id}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::get('/admin/pegawai/hapus/{id}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');
    
    // Export Data Rekap Bulanan
    Route::get('/admin/rekap/excel', [AdminController::class, 'exportExcel'])->name('admin.rekap.excel');
    Route::get('/admin/rekap/pdf', [AdminController::class, 'exportPdf'])->name('admin.rekap.pdf');
});
