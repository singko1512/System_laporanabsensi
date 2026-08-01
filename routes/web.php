<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectTimelineController;

Route::get('/', [AttendanceController::class, 'home'])->name('home');
Route::get('/sertifikat/{slug}', [AttendanceController::class, 'sertifikat'])->name('sertifikat.show');

// Satu pintu login akun untuk peserta, admin, dan superadmin.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Legacy login names tetap diarahkan ke auth baru.
Route::get('/admin/login', [AdminController::class, 'showAdminLogin'])->name('admin.login.form');
Route::post('/admin/login', [AdminController::class, 'loginAdmin'])->name('admin.login');
Route::get('/superadmin/login', [AdminController::class, 'showSuperAdminLogin'])->name('superadmin.login.form');
Route::post('/superadmin/login', [AdminController::class, 'loginSuperAdmin'])->name('superadmin.login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware(['role:user'])->group(function () {
    Route::get('/absensi', [AttendanceController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/simpan', [AttendanceController::class, 'store'])->name('absensi.store');
    Route::post('/timeline/task/submit/{participant}', [ProjectTimelineController::class, 'submitTask'])->name('timeline.task.submit');
    Route::post('/timeline/submission/reply/{submission}', [ProjectTimelineController::class, 'replySubmission'])->name('timeline.submission.reply');
    Route::post('/absensi/task/ambil/{task}', [ProjectTimelineController::class, 'selfAssignTask'])->name('absensi.task.ambil');
    Route::post('/absensi/task/selesai/{task}', [ProjectTimelineController::class, 'submitWorkTask'])->name('absensi.task.submit_work');
    Route::post('/absensi/module/ambil/{module}', [ProjectTimelineController::class, 'selfAssignModule'])->name('absensi.module.ambil');

    // Legacy redirects
    Route::get('/absensi/form', [AttendanceController::class, 'showForm'])->name('absensi.form');
    Route::get('/rekap', [AttendanceController::class, 'rekap'])->name('absensi.rekap');
});

Route::middleware(['role:user,admin,superadmin'])->group(function () {
    Route::get('/absensi/lampiran/{absensi}', [AttendanceController::class, 'lampiran'])->name('absensi.lampiran');
    Route::get('/absensi/kamera/{absensi}', [AttendanceController::class, 'kamera'])->name('absensi.kamera');
    Route::post('/timeline/note/selesai/{note}', [ProjectTimelineController::class, 'completeNote'])->name('timeline.note.complete');
});

Route::middleware(['admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // CRUD Kelola Magang (md_user)
    Route::post('/admin/pegawai/tambah', [AdminController::class, 'storeUser'])->name('admin.user.store');
    Route::post('/admin/pegawai/update/{id}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::get('/admin/pegawai/hapus/{id}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');
    Route::post('/admin/sertifikat/upload/{user}', [AdminController::class, 'uploadSertifikat'])->name('admin.sertifikat.upload');
    Route::get('/admin/sertifikat/file/{user}', [AdminController::class, 'viewSertifikat'])->name('admin.sertifikat.view');
    Route::post('/admin/sertifikat/hapus/{user}', [AdminController::class, 'destroySertifikat'])->name('admin.sertifikat.destroy');
});

Route::middleware(['admin:superadmin'])->group(function () {
    Route::post('/admin/absensi/hapus/{absensi}', [AdminController::class, 'destroyAbsensi'])->name('admin.absensi.destroy');

    // CRUD Kelola Bidang (md_bidang)
    Route::post('/admin/bidang/tambah', [AdminController::class, 'storeBidang'])->name('admin.bidang.store');
    Route::post('/admin/bidang/update/{id}', [AdminController::class, 'updateBidang'])->name('admin.bidang.update');
    Route::get('/admin/bidang/hapus/{id}', [AdminController::class, 'destroyBidang'])->name('admin.bidang.destroy');
    Route::post('/admin/pembimbing/tambah', [AdminController::class, 'storePembimbing'])->name('admin.pembimbing.store');
    Route::post('/admin/pembimbing/update/{id}', [AdminController::class, 'updatePembimbing'])->name('admin.pembimbing.update');
    Route::get('/admin/pembimbing/hapus/{id}', [AdminController::class, 'destroyPembimbing'])->name('admin.pembimbing.destroy');

    Route::post('/admin/jadwal/simpan', [AdminController::class, 'updateSchedules'])->name('admin.jadwal.update');
    Route::post('/admin/jadwal/acak', [AdminController::class, 'randomizeSchedules'])->name('admin.jadwal.random');

    Route::post('/admin/project/tambah', [ProjectTimelineController::class, 'storeProject'])->name('admin.project.store');
    Route::post('/admin/project/update/{project}', [ProjectTimelineController::class, 'updateProject'])->name('admin.project.update');
    Route::post('/admin/project/hapus/{project}', [ProjectTimelineController::class, 'destroyProject'])->name('admin.project.destroy');
    Route::post('/admin/project/timeline/tambah', [ProjectTimelineController::class, 'storeTimeline'])->name('admin.project.timeline.store');
    Route::post('/admin/project/timeline/update/{timeline}', [ProjectTimelineController::class, 'updateTimeline'])->name('admin.project.timeline.update');
    Route::post('/admin/project/timeline/hapus/{timeline}', [ProjectTimelineController::class, 'destroyTimeline'])->name('admin.project.timeline.destroy');
    Route::post('/admin/project/module/tambah', [ProjectTimelineController::class, 'storeModule'])->name('admin.project.module.store');
    Route::post('/admin/project/module/update/{module}', [ProjectTimelineController::class, 'updateModule'])->name('admin.project.module.update');
    Route::post('/admin/project/module/hapus/{module}', [ProjectTimelineController::class, 'destroyModule'])->name('admin.project.module.destroy');
    Route::post('/admin/project/module/urutkan', [ProjectTimelineController::class, 'reorderModules'])->name('admin.project.module.reorder');
    Route::post('/admin/project/task/tambah', [ProjectTimelineController::class, 'storeTask'])->name('admin.project.task.store');
    Route::post('/admin/project/task/lock/{task}', [ProjectTimelineController::class, 'lockTask'])->name('admin.project.task.lock');
    Route::post('/admin/project/task/reopen/{task}', [ProjectTimelineController::class, 'reopenTask'])->name('admin.project.task.reopen');
    Route::post('/admin/project/task/assign/{task}', [ProjectTimelineController::class, 'assignTaskPIC'])->name('admin.project.task.assign_pic');
    Route::post('/admin/project/task/unassign/{task}', [ProjectTimelineController::class, 'unassignTaskPIC'])->name('admin.project.task.unassign_pic');
    Route::post('/admin/project/task/hapus/{task}', [ProjectTimelineController::class, 'destroyTask'])->name('admin.project.task.destroy');
    Route::post('/admin/project/submission/approve/{submission}', [ProjectTimelineController::class, 'approveSubmission'])->name('admin.project.submission.approve');
    Route::post('/admin/project/submission/revision/{submission}', [ProjectTimelineController::class, 'revisionSubmission'])->name('admin.project.submission.revision');
    Route::post('/admin/project/task/approve/{task}', [ProjectTimelineController::class, 'approveTask'])->name('admin.project.task.approve');
    Route::post('/admin/project/task/revision/{task}', [ProjectTimelineController::class, 'revisionTask'])->name('admin.project.task.revision');
    Route::post('/admin/project/note/tambah', [ProjectTimelineController::class, 'storeNote'])->name('admin.project.note.store');
    Route::post('/admin/project/assignment/simpan', [ProjectTimelineController::class, 'assignDay'])->name('admin.project.assignment.store');
    Route::post('/admin/project/assignment/hapus/{assignment}', [ProjectTimelineController::class, 'removeDayAssignment'])->name('admin.project.assignment.destroy');
    
    // Export Data Rekap Bulanan
    Route::get('/admin/rekap/excel', [AdminController::class, 'exportExcel'])->name('admin.rekap.excel');
    Route::get('/admin/rekap/pdf', [AdminController::class, 'exportPdf'])->name('admin.rekap.pdf');
});
