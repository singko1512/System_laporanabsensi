<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidang;
use App\Models\Absensi;
use App\Models\Pengaturan;
use App\Models\JadwalMingguan;
use App\Models\MasterData;
use App\Models\PembimbingMagang;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\ProjectTask;
use App\Models\ActivityLog;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function showAdminLogin()
    {
        return redirect()->route('login.form', ['role' => 'admin']);
    }

    public function showSuperAdminLogin()
    {
        return redirect()->route('login.form', ['role' => 'superadmin']);
    }

    public function loginAdmin(Request $request)
    {
        return $this->loginWithRole($request, 'admin');
    }

    public function loginSuperAdmin(Request $request)
    {
        return $this->loginWithRole($request, 'superadmin');
    }

    private function loginWithRole(Request $request, string $role)
    {
        $request->merge([
            'login' => $request->input('login', $request->input('username')),
            'expected_role' => $role,
        ]);

        return app(AuthController::class)->login($request);
    }

    /**
     * Log out Admin session.
     */
    public function logout()
    {
        return app(AuthController::class)->logout(request());
    }

    /**
     * Display the Admin Dashboard.
     */
    public function dashboard(Request $request)
    {
        $adminRole = Auth::user()->role;
        $isSuperAdmin = $adminRole === 'superadmin';
        $activeAdminTab = (string) $request->input('tab', $isSuperAdmin ? 'rekap' : 'pegawai');
        $allowedTabs = $isSuperAdmin
            ? ['rekap', 'pegawai', 'jadwal', 'timeline', 'sertifikat', 'bidang']
            : ['pegawai', 'sertifikat'];

        if (! in_array($activeAdminTab, $allowedTabs, true)) {
            return redirect()
                ->route('admin.dashboard', ['tab' => $allowedTabs[0]])
                ->with('error_swal', 'Role Anda tidak memiliki akses ke menu tersebut.');
        }

        $users = User::with(['jadwalMingguan', 'bidang', 'pembimbingMagang'])->where('role', 'user')->orderBy('nama', 'asc')->get();
        $magangSearch = trim((string) $request->input('magang_search', ''));
        $pembimbingMagang = trim((string) $request->input('pembimbing_magang', ''));

        $pembimbingOptions = PembimbingMagang::with('bidang')->orderBy('nama', 'asc')->get();

        $magangQuery = User::with(['bidang', 'pembimbingMagang'])->where('role', 'user')->orderBy('pembimbing_magang')->orderBy('nama');

        if ($magangSearch !== '') {
            $magangQuery->where(function ($query) use ($magangSearch) {
                $query->where('nama', 'like', '%' . $magangSearch . '%')
                    ->orWhere('email', 'like', '%' . $magangSearch . '%')
                    ->orWhere('bidang_magang', 'like', '%' . $magangSearch . '%')
                    ->orWhere('pembimbing_magang', 'like', '%' . $magangSearch . '%');
            });
        }

        if ($pembimbingMagang !== '') {
            $magangQuery->where('pembimbing_magang', $pembimbingMagang);
        }

        $magangUsers = $magangQuery->get();
        $magangGroups = $magangUsers->groupBy(fn (User $user) => $user->pembimbing_magang ?: 'Belum ada pembimbing');
        $absensiStatuses = MasterData::options(MasterData::ABSENSI_STATUS);
        $jadwalStatuses = MasterData::options(MasterData::JADWAL_STATUS);
        $projectStatuses = MasterData::options(MasterData::PROJECT_STATUS);
        $noteCategories = MasterData::options(MasterData::NOTE_KATEGORI);
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $absensiQuery = Absensi::with(['user', 'statusMaster'])
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $absensiQuery->whereHas('user', function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '' && $status !== 'all') {
            $statusId = MasterData::idFor(MasterData::ABSENSI_STATUS, $status);
            $absensiQuery->where('status_id', $statusId);
        }

        $absensiRecords = $absensiQuery->get();
        $selesaiProjectStatusId = MasterData::idFor(MasterData::PROJECT_STATUS, 'selesai');
        $projects = Project::with([
                'user',
                'members',
                'statusMaster',
                'notes.user',
                'notes.kategoriMaster',
                'dayAssignments.user',
                'modules.tasks.user',
                'tasks.user',
            ])
            ->orderByRaw('status_id = ? asc', [$selesaiProjectStatusId])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $bidangs = Bidang::orderBy('nama', 'asc')->get();
        $pembimbingMagangs = $pembimbingOptions;
        $sertifikatUsers = User::query()
            ->where('role', 'user')
            ->orderByRaw('tanggal_selesai_magang is null asc')
            ->orderBy('tanggal_selesai_magang', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        // 1. Jumlah Project, Module, Task
        $projectCount = Project::count();
        $moduleCount = ProjectModule::count();
        $taskCount = ProjectTask::count();

        // 2. Project Aktif & Selesai
        $selesaiStatusId = \App\Models\MasterData::idFor(\App\Models\MasterData::PROJECT_STATUS, 'selesai');
        $projectAktifCount = Project::where(function($q) use ($selesaiStatusId) {
            $q->whereNull('status_id')->orWhere('status_id', '!=', $selesaiStatusId);
        })->count();

        $projectSelesaiCount = Project::where('status_id', $selesaiStatusId)->count();

        // 3. Task Menunggu Review & Terlambat
        $taskReviewCount = ProjectTask::where('status', 'review')->count();
        $taskTerlambatCount = ProjectTask::where('status', '!=', 'selesai')
            ->where('tanggal_selesai', '<', now()->toDateString())
            ->count();

        // 4. Peserta Magang Aktif
        $pesertaAktifCount = User::where('role', 'user')->where('status_akun', 'aktif')->count();

        // 5. Statistik Kehadiran Hari Ini
        $todayAbsens = Absensi::with('statusMaster')->where('tanggal', now()->toDateString())->get();
        $hadirCount = 0;
        $wfhCount = 0;
        $sakitCount = 0;
        $izinCount = 0;
        foreach ($todayAbsens as $abs) {
            $code = strtolower($abs->statusMaster?->kode ?? $abs->status ?? '');
            if (in_array($code, ['hadir', 'wfo', 'wfo (hadir)'], true)) {
                $hadirCount++;
            } elseif ($code === 'wfh') {
                $wfhCount++;
            } elseif ($code === 'sakit') {
                $sakitCount++;
            } elseif ($code === 'izin') {
                $izinCount++;
            }
        }
        $belumAbsenCount = max(0, $pesertaAktifCount - $todayAbsens->count());

        // 6. Activity Log
        $activityLogs = ActivityLog::with(['user', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return view('admin.dashboard', compact(
            'adminRole',
            'isSuperAdmin',
            'activeAdminTab',
            'users',
            'magangUsers',
            'magangGroups',
            'pembimbingOptions',
            'absensiStatuses',
            'jadwalStatuses',
            'projectStatuses',
            'noteCategories',
            'absensiRecords',
            'projects',
            'bidangs',
            'pembimbingMagangs',
            'sertifikatUsers',
            'month',
            'year',
            'search',
            'status',
            'magangSearch',
            'pembimbingMagang',
            // Data statistik baru
            'projectCount',
            'moduleCount',
            'taskCount',
            'projectAktifCount',
            'projectSelesaiCount',
            'taskReviewCount',
            'taskTerlambatCount',
            'pesertaAktifCount',
            'hadirCount',
            'wfhCount',
            'sakitCount',
            'izinCount',
            'belumAbsenCount',
            'activityLogs'
        ));
    }

    /**
     * Store new internship participant (User).
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'nullable|string|max:100|unique:md_user,username',
            'email' => 'required|email|max:100|unique:md_user,email',
            'password' => 'required|string|min:6',
            'pembimbing_magang_id' => [
                'required',
                Rule::exists('md_pembimbing_magang', 'id')
                    ->where(fn ($query) => $query->where('bidang_id', $request->input('bidang_id'))),
            ],
            'bidang_id' => 'required|exists:md_bidang,id',
            'tanggal_mulai_magang' => 'nullable|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
            'status_akun' => 'required|in:aktif,nonaktif',
        ], [
            'nama.required' => 'Nama peserta magang wajib diisi.',
            'username.unique' => 'Username sudah dipakai.',
            'email.required' => 'Email peserta magang wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password peserta magang wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'pembimbing_magang_id.required' => 'Pembimbing magang wajib dipilih.',
            'pembimbing_magang_id.exists' => 'Pembimbing magang tidak sesuai dengan bidang yang dipilih.',
            'bidang_id.required' => 'Bidang magang wajib dipilih.',
            'bidang_id.exists' => 'Bidang magang tidak valid.',
            'tanggal_mulai_magang.date' => 'Tanggal mulai magang tidak valid.',
            'tanggal_selesai_magang.date' => 'Tanggal selesai magang tidak valid.',
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama atau setelah tanggal mulai.',
        ]);

        $bidang = Bidang::findOrFail($request->input('bidang_id'));
        $pembimbing = PembimbingMagang::findOrFail($request->input('pembimbing_magang_id'));

        User::create([
            'nama' => $request->input('nama'),
            'username' => $request->input('username') ?: $this->generateUsername($request->input('nama'), $request->input('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'pembimbing_magang_id' => $pembimbing->id,
            'pembimbing_magang' => $pembimbing->nama,
            'bidang_id' => $bidang->id,
            'bidang_magang' => $bidang->nama,
            'tanggal_mulai_magang' => $request->input('tanggal_mulai_magang'),
            'tanggal_selesai_magang' => $request->input('tanggal_selesai_magang'),
            'role' => 'user',
            'status_akun' => $request->input('status_akun', 'aktif'),
        ])->jadwalMingguan()->create(JadwalMingguan::defaultSchedule());

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Peserta magang baru berhasil ditambahkan!');
    }

    /**
     * Update internship participant (User) details.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'nullable|string|max:100|unique:md_user,username,' . $id,
            'email' => 'required|email|max:100|unique:md_user,email,' . $id,
            'password' => 'nullable|string|min:6',
            'pembimbing_magang_id' => [
                'required',
                Rule::exists('md_pembimbing_magang', 'id')
                    ->where(fn ($query) => $query->where('bidang_id', $request->input('bidang_id'))),
            ],
            'bidang_id' => 'required|exists:md_bidang,id',
            'tanggal_mulai_magang' => 'nullable|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
            'status_akun' => 'required|in:aktif,nonaktif',
        ], [
            'nama.required' => 'Nama peserta magang wajib diisi.',
            'username.unique' => 'Username sudah dipakai.',
            'email.required' => 'Email peserta magang wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'pembimbing_magang_id.required' => 'Pembimbing magang wajib dipilih.',
            'pembimbing_magang_id.exists' => 'Pembimbing magang tidak sesuai dengan bidang yang dipilih.',
            'bidang_id.required' => 'Bidang magang wajib dipilih.',
            'bidang_id.exists' => 'Bidang magang tidak valid.',
            'tanggal_mulai_magang.date' => 'Tanggal mulai magang tidak valid.',
            'tanggal_selesai_magang.date' => 'Tanggal selesai magang tidak valid.',
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama atau setelah tanggal mulai.',
        ]);

        $bidang = Bidang::findOrFail($request->input('bidang_id'));
        $pembimbing = PembimbingMagang::findOrFail($request->input('pembimbing_magang_id'));

        $payload = [
            'nama' => $request->input('nama'),
            'username' => $request->input('username') ?: ($user->username ?: $this->generateUsername($request->input('nama'), $request->input('email'), $user->id)),
            'email' => $request->input('email'),
            'pembimbing_magang_id' => $pembimbing->id,
            'pembimbing_magang' => $pembimbing->nama,
            'bidang_id' => $bidang->id,
            'bidang_magang' => $bidang->nama,
            'tanggal_mulai_magang' => $request->input('tanggal_mulai_magang'),
            'tanggal_selesai_magang' => $request->input('tanggal_selesai_magang'),
            'status_akun' => $request->input('status_akun', 'aktif'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->input('password');
        }

        $user->update($payload);

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Data peserta magang berhasil diperbarui!');
    }

    /**
     * Delete internship participant (User).
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // automatically cascades absensi deletion due to DB schema constraint

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Peserta magang berhasil dihapus!');
    }

    /**
     * Delete an incorrect attendance record and its attachment.
     */
    public function destroyAbsensi(Request $request, Absensi $absensi)
    {
        $foto = $absensi->foto;
        $fotoKamera = $absensi->foto_kamera;
        $absensi->delete();

        foreach ([$foto, $fotoKamera] as $path) {
            if (! $path) {
                continue;
            }

            $uploadsRoot = realpath(public_path('uploads'));
            $filePath = realpath(public_path($path));

            if ($uploadsRoot && $filePath && str_starts_with($filePath, $uploadsRoot . DIRECTORY_SEPARATOR) && File::isFile($filePath)) {
                File::delete($filePath);
            }
        }

        return redirect()->route('admin.dashboard', [
            'tab' => 'rekap',
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ])->with('success_swal', 'Data absensi berhasil dihapus.');
    }

    public function uploadSertifikat(Request $request, User $user)
    {
        $request->validate([
            'sertifikat_file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'sertifikat_file.required' => 'File sertifikat wajib dipilih.',
            'sertifikat_file.mimes' => 'Sertifikat harus berupa PDF, JPG, JPEG, PNG, atau WEBP.',
            'sertifikat_file.max' => 'Ukuran sertifikat maksimal 10 MB.',
        ]);

        if ($user->sertifikat_file_path) {
            Storage::disk('local')->delete($user->sertifikat_file_path);
        }

        $file = $request->file('sertifikat_file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $filename = 'sertifikat_' . $user->id . '_' . now(config('app.timezone'))->format('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $path = $file->storeAs('sertifikat', $filename, 'local');

        $user->update([
            'sertifikat_file_path' => $path,
            'sertifikat_file_name' => $file->getClientOriginalName(),
            'sertifikat_file_mime' => $file->getMimeType(),
            'sertifikat_diunggah_pada' => now(config('app.timezone')),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'sertifikat'])
            ->with('success_swal', 'Sertifikat ' . $user->nama . ' berhasil diunggah.');
    }

    public function viewSertifikat(User $user)
    {
        if (! $user->sertifikat_file_path || ! Storage::disk('local')->exists($user->sertifikat_file_path)) {
            abort(404);
        }

        $filePath = Storage::disk('local')->path($user->sertifikat_file_path);
        $fileName = $user->sertifikat_file_name ?: basename($user->sertifikat_file_path);
        $mime = $user->sertifikat_file_mime ?: 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($fileName) . '"',
        ]);
    }

    public function destroySertifikat(User $user)
    {
        if ($user->sertifikat_file_path) {
            Storage::disk('local')->delete($user->sertifikat_file_path);
        }

        $user->update([
            'sertifikat_file_path' => null,
            'sertifikat_file_name' => null,
            'sertifikat_file_mime' => null,
            'sertifikat_diunggah_pada' => null,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'sertifikat'])
            ->with('success_swal', 'File sertifikat berhasil dihapus.');
    }

    /**
     * Save weekly schedules for all employees.
     */
    public function updateSchedules(Request $request)
    {
        $weekdays = ['senin', 'selasa', 'rabu', 'kamis'];
        $allowedStatuses = MasterData::codes(MasterData::JADWAL_STATUS);
        $schedules = $request->input('schedules', []);

        foreach ($schedules as $userId => $schedule) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $data = ['jumat' => 'wfh'];
            foreach ($weekdays as $day) {
                $data[$day] = in_array($schedule[$day] ?? 'wfo', $allowedStatuses, true)
                    ? $schedule[$day]
                    : 'wfo';
            }

            $user->jadwalMingguan()->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        }

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal'])->with('success_swal', 'Jadwal mingguan berhasil disimpan!');
    }

    /**
     * Randomly assign WFO/WFH patterns to all employees.
     */
    public function randomizeSchedules()
    {
        $users = User::orderBy('nama', 'asc')->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal'])->with('error_swal', 'Belum ada peserta magang untuk diacak jadwalnya.');
        }

        $shuffled = $users->shuffle();
        $half = (int) ceil($shuffled->count() / 2);

        $shuffled->each(function (User $user, int $index) use ($half) {
            $pattern = $index < $half ? JadwalMingguan::grupA() : JadwalMingguan::grupB();
            $user->jadwalMingguan()->updateOrCreate(
                ['user_id' => $user->id],
                $pattern
            );
        });

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal'])->with('success_swal', 'Jadwal berhasil diacak! Jumat tetap WFH untuk semua peserta magang.');
    }

    /**
     * Export Monthly Rekap to Excel.
     */
    public function exportExcel(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        return Excel::download(new AbsensiExport($month, $year), "Rekap_Absensi_{$month}_{$year}.xlsx");
    }

    /**
     * Export Monthly Rekap to PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Calculate weekdays
        $totalWorkdays = 0;
        $tempDate = $startDate->copy();
        $today = Carbon::today();
        $maxCalcDate = $endDate->gt($today) ? $today : $endDate;

        while ($tempDate->lte($maxCalcDate)) {
            if (!$tempDate->isWeekend()) {
                $totalWorkdays++;
            }
            $tempDate->addDay();
        }

        if ($totalWorkdays == 0) {
            $totalWorkdays = 1;
        }

        $employeesWithAbsensi = User::with(['absensi' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->with('statusMaster')
                  ->orderBy('tanggal', 'asc');
        }])->orderBy('nama', 'asc')->get();

        $rekapData = [];
        foreach ($employeesWithAbsensi as $emp) {
            $hadir = $emp->absensi->where('status', 'hadir')->count();
            $wfh = $emp->absensi->where('status', 'wfh')->count();
            $sakit = $emp->absensi->where('status', 'sakit')->count();
            $izin = $emp->absensi->where('status', 'izin')->count();

            $attended = $hadir + $wfh;
            $persentase = round(($attended / $totalWorkdays) * 100, 1);

            $rekapData[] = (object)[
                'user' => $emp,
                'hadir' => $hadir,
                'wfh' => $wfh,
                'sakit' => $sakit,
                'izin' => $izin,
                'persentase' => $persentase,
            ];
        }

        $namaBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        $pdf = Pdf::loadView('admin.rekap_pdf', compact('rekapData', 'month', 'year', 'namaBulan', 'totalWorkdays'));
        return $pdf->download("Rekap_Absensi_{$namaBulan}_{$year}.pdf");
    }

    public function storeBidang(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:md_bidang,nama',
        ], [
            'nama.required' => 'Nama bidang wajib diisi.',
            'nama.unique' => 'Nama bidang sudah ada.',
        ]);

        Bidang::create([
            'nama' => trim($request->input('nama')),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Bidang baru berhasil ditambahkan!');
    }

    public function updateBidang(Request $request, $id)
    {
        $bidang = Bidang::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100|unique:md_bidang,nama,' . $id,
        ], [
            'nama.required' => 'Nama bidang wajib diisi.',
            'nama.unique' => 'Nama bidang sudah ada.',
        ]);

        $oldName = $bidang->nama;
        $newName = trim($request->input('nama'));

        $bidang->update([
            'nama' => $newName,
        ]);

        User::where('bidang_magang', $oldName)->update([
            'bidang_magang' => $newName,
            'bidang_id' => $bidang->id,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Nama bidang berhasil diperbarui!');
    }

    public function destroyBidang($id)
    {
        $bidang = Bidang::findOrFail($id);

        User::where('bidang_magang', $bidang->nama)->update([
            'bidang_id' => null,
            'bidang_magang' => null,
        ]);

        $bidang->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Bidang berhasil dihapus!');
    }

    public function storePembimbing(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:md_pembimbing_magang,nama',
            'bidang_id' => 'required|exists:md_bidang,id',
        ], [
            'nama.required' => 'Nama pembimbing wajib diisi.',
            'nama.unique' => 'Nama pembimbing sudah ada.',
            'bidang_id.required' => 'Bidang magang wajib dipilih.',
            'bidang_id.exists' => 'Bidang magang tidak valid.',
        ]);

        PembimbingMagang::create([
            'nama' => trim($request->input('nama')),
            'bidang_id' => $request->input('bidang_id'),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Pembimbing magang baru berhasil ditambahkan!');
    }

    public function updatePembimbing(Request $request, $id)
    {
        $pembimbing = PembimbingMagang::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100|unique:md_pembimbing_magang,nama,' . $id,
            'bidang_id' => 'required|exists:md_bidang,id',
        ], [
            'nama.required' => 'Nama pembimbing wajib diisi.',
            'nama.unique' => 'Nama pembimbing sudah ada.',
            'bidang_id.required' => 'Bidang magang wajib dipilih.',
            'bidang_id.exists' => 'Bidang magang tidak valid.',
        ]);

        $oldName = $pembimbing->nama;
        $newName = trim($request->input('nama'));

        $pembimbing->update([
            'nama' => $newName,
            'bidang_id' => $request->input('bidang_id'),
        ]);

        User::where('pembimbing_magang', $oldName)->update([
            'pembimbing_magang_id' => $pembimbing->id,
            'pembimbing_magang' => $newName,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Nama pembimbing berhasil diperbarui!');
    }

    public function destroyPembimbing($id)
    {
        $pembimbing = PembimbingMagang::findOrFail($id);

        User::where('pembimbing_magang', $pembimbing->nama)->update([
            'pembimbing_magang_id' => null,
            'pembimbing_magang' => null,
        ]);

        $pembimbing->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'bidang'])->with('success_swal', 'Pembimbing magang berhasil dihapus!');
    }

    private function generateUsername(string $name, string $email, ?int $ignoreUserId = null): string
    {
        $base = Str::of(Str::before($email, '@') ?: $name)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '.')
            ->trim('.-_')
            ->value();

        $base = $base !== '' ? $base : 'peserta';
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)
            ->when($ignoreUserId, fn ($query) => $query->where('id', '<>', $ignoreUserId))
            ->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
