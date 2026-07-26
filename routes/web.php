<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectTimelineController;

// 1. Sisi Peserta Magang (Tanpa Login)
Route::get('/', [AttendanceController::class, 'home'])->name('home');
Route::get('/absensi', [AttendanceController::class, 'index'])->name('absensi.index');
Route::post('/absensi/simpan', [AttendanceController::class, 'store'])->name('absensi.store');
Route::get('/absensi/lampiran/{absensi}', [AttendanceController::class, 'lampiran'])->name('absensi.lampiran');
Route::get('/absensi/kamera/{absensi}', [AttendanceController::class, 'kamera'])->name('absensi.kamera');
Route::post('/timeline/note/selesai/{note}', [ProjectTimelineController::class, 'completeNote'])->name('timeline.note.complete');
Route::get('/sertifikat/{slug}', [AttendanceController::class, 'sertifikat'])->name('sertifikat.show');

// Legacy redirects
Route::get('/absensi/form', [AttendanceController::class, 'showForm'])->name('absensi.form');
Route::get('/rekap', [AttendanceController::class, 'rekap'])->name('absensi.rekap');

// 2. Admin Authentication (Verifikasi PIN)
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// 3. Sisi Admin (Proteksi PIN Session)
Route::middleware(['admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // CRUD Kelola Magang (md_user)
    Route::post('/admin/pegawai/tambah', [AdminController::class, 'storeUser'])->name('admin.user.store');
    Route::post('/admin/pegawai/update/{id}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::get('/admin/pegawai/hapus/{id}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');
    Route::post('/admin/absensi/hapus/{absensi}', [AdminController::class, 'destroyAbsensi'])->name('admin.absensi.destroy');

    Route::post('/admin/jadwal/simpan', [AdminController::class, 'updateSchedules'])->name('admin.jadwal.update');
    Route::post('/admin/jadwal/acak', [AdminController::class, 'randomizeSchedules'])->name('admin.jadwal.random');

    Route::post('/admin/project/tambah', [ProjectTimelineController::class, 'storeProject'])->name('admin.project.store');
    Route::post('/admin/project/update/{project}', [ProjectTimelineController::class, 'updateProject'])->name('admin.project.update');
    Route::post('/admin/project/hapus/{project}', [ProjectTimelineController::class, 'destroyProject'])->name('admin.project.destroy');
    Route::post('/admin/project/note/tambah', [ProjectTimelineController::class, 'storeNote'])->name('admin.project.note.store');
    Route::post('/admin/project/assignment/simpan', [ProjectTimelineController::class, 'assignDay'])->name('admin.project.assignment.store');
    Route::post('/admin/project/assignment/hapus/{assignment}', [ProjectTimelineController::class, 'removeDayAssignment'])->name('admin.project.assignment.destroy');
    
    // Export Data Rekap Bulanan
    Route::get('/admin/rekap/excel', [AdminController::class, 'exportExcel'])->name('admin.rekap.excel');
    Route::get('/admin/rekap/pdf', [AdminController::class, 'exportPdf'])->name('admin.rekap.pdf');
});
