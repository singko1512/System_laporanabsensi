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
use App\Support\CertificatePayload;
use App\Support\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Exports\AbsensiExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
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
        $currentAdmin = Auth::user();
        $adminRole = $currentAdmin->role;
        $isSuperAdmin = $adminRole === 'superadmin';
        $activeAdminTab = (string) $request->input('tab', $isSuperAdmin ? 'rekap' : 'pegawai');
        $allowedTabs = $isSuperAdmin
            ? ['rekap', 'pegawai', 'jadwal', 'timeline', 'sertifikat', 'bidang']
            : ['pegawai', 'timeline', 'sertifikat'];

        if (! in_array($activeAdminTab, $allowedTabs, true)) {
            return redirect()
                ->route('admin.dashboard', ['tab' => $allowedTabs[0]])
                ->with('error_swal', 'Role Anda tidak memiliki akses ke menu tersebut.');
        }

        $adminBidangScope = $this->resolveAdminBidangScope($request, $currentAdmin, $isSuperAdmin);
        $activeBidangId = $adminBidangScope?->id;
        $adminBidangOptions = Bidang::orderBy('nama', 'asc')->get();

        $usersQuery = User::with(['jadwalMingguan', 'bidang', 'pembimbingMagang'])
            ->where('role', 'user')
            ->orderBy('nama', 'asc');
        $this->applyUserBidangScope($usersQuery, $adminBidangScope);
        $users = $usersQuery->get();

        $magangSearch = trim((string) $request->input('magang_search', ''));
        $pembimbingMagang = trim((string) $request->input('pembimbing_magang', ''));

        $pembimbingOptionsQuery = PembimbingMagang::with('bidang')->orderBy('nama', 'asc');
        if ($adminBidangScope) {
            $pembimbingOptionsQuery->where('bidang_id', $adminBidangScope->id);
        }
        $pembimbingOptions = $pembimbingOptionsQuery->get();

        $magangQuery = User::with(['bidang', 'pembimbingMagang'])->where('role', 'user')->orderBy('pembimbing_magang')->orderBy('nama');
        $this->applyUserBidangScope($magangQuery, $adminBidangScope);

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

        if ($adminBidangScope) {
            $absensiQuery->whereHas('user', function ($query) use ($adminBidangScope) {
                $this->applyUserBidangScope($query, $adminBidangScope);
            });
        }

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
        $projectsQuery = Project::with([
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
            ->orderBy('tanggal_mulai', 'desc');
        $this->applyProjectBidangScope($projectsQuery, $adminBidangScope);
        $projects = $projectsQuery->get();

        $bidangs = Bidang::orderBy('nama', 'asc')->get();
        $manageableBidangs = $adminBidangScope && ! $isSuperAdmin
            ? $bidangs->where('id', $adminBidangScope->id)->values()
            : $bidangs;
        $pembimbingMagangsQuery = PembimbingMagang::with('bidang')->orderBy('nama', 'asc');
        if ($adminBidangScope) {
            $pembimbingMagangsQuery->where('bidang_id', $adminBidangScope->id);
        }
        $pembimbingMagangs = $pembimbingMagangsQuery->get();
        $sertifikatUsersQuery = User::query()
            ->where('role', 'user')
            ->orderByRaw('tanggal_selesai_magang is null asc')
            ->orderBy('tanggal_selesai_magang', 'desc')
            ->orderBy('nama', 'asc');
        $this->applyUserBidangScope($sertifikatUsersQuery, $adminBidangScope);
        $sertifikatUsers = $sertifikatUsersQuery->get();

        // 1. Jumlah Project, Module, Task
        $projectCountQuery = Project::query();
        $this->applyProjectBidangScope($projectCountQuery, $adminBidangScope);
        $projectCount = $projectCountQuery->count();

        $moduleCountQuery = ProjectModule::query();
        if ($adminBidangScope) {
            $moduleCountQuery->whereHas('project', function ($query) use ($adminBidangScope) {
                $this->applyProjectBidangScope($query, $adminBidangScope);
            });
        }
        $moduleCount = $moduleCountQuery->count();

        $taskCountQuery = ProjectTask::query();
        if ($adminBidangScope) {
            $taskCountQuery->whereHas('project', function ($query) use ($adminBidangScope) {
                $this->applyProjectBidangScope($query, $adminBidangScope);
            });
        }
        $taskCount = $taskCountQuery->count();

        // 2. Project Aktif & Selesai
        $selesaiStatusId = \App\Models\MasterData::idFor(\App\Models\MasterData::PROJECT_STATUS, 'selesai');
        $projectAktifQuery = Project::where(function($q) use ($selesaiStatusId) {
            $q->whereNull('status_id')->orWhere('status_id', '!=', $selesaiStatusId);
        });
        $this->applyProjectBidangScope($projectAktifQuery, $adminBidangScope);
        $projectAktifCount = $projectAktifQuery->count();

        $projectSelesaiQuery = Project::where('status_id', $selesaiStatusId);
        $this->applyProjectBidangScope($projectSelesaiQuery, $adminBidangScope);
        $projectSelesaiCount = $projectSelesaiQuery->count();

        // 3. Task Menunggu Review & Terlambat
        $taskReviewQuery = ProjectTask::where('status', 'review');
        if ($adminBidangScope) {
            $taskReviewQuery->whereHas('project', function ($query) use ($adminBidangScope) {
                $this->applyProjectBidangScope($query, $adminBidangScope);
            });
        }
        $taskReviewCount = $taskReviewQuery->count();

        $taskTerlambatQuery = ProjectTask::where('status', '!=', 'selesai')
            ->where('tanggal_selesai', '<', now()->toDateString())
            ->whereNotNull('tanggal_selesai');
        if ($adminBidangScope) {
            $taskTerlambatQuery->whereHas('project', function ($query) use ($adminBidangScope) {
                $this->applyProjectBidangScope($query, $adminBidangScope);
            });
        }
        $taskTerlambatCount = $taskTerlambatQuery->count();

        // 4. Peserta Magang Aktif
        $pesertaAktifQuery = User::where('role', 'user')->where('status_akun', 'aktif');
        $this->applyUserBidangScope($pesertaAktifQuery, $adminBidangScope);
        $pesertaAktifCount = $pesertaAktifQuery->count();

        // 5. Statistik Kehadiran Hari Ini
        $todayAbsensQuery = Absensi::with('statusMaster')->where('tanggal', now()->toDateString());
        if ($adminBidangScope) {
            $todayAbsensQuery->whereHas('user', function ($query) use ($adminBidangScope) {
                $this->applyUserBidangScope($query, $adminBidangScope);
            });
        }
        $todayAbsens = $todayAbsensQuery->get();
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
        $activityLogsQuery = ActivityLog::with(['user', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(50);
        if ($adminBidangScope) {
            $activityLogsQuery->where(function ($query) use ($adminBidangScope) {
                $query->whereHas('user', function ($userQuery) use ($adminBidangScope) {
                    $this->applyUserBidangScope($userQuery, $adminBidangScope);
                })->orWhereHas('project', function ($projectQuery) use ($adminBidangScope) {
                    $this->applyProjectBidangScope($projectQuery, $adminBidangScope);
                });
            });
        }
        $activityLogs = $activityLogsQuery->get();

        $pendingTasksQuery = ProjectTask::with(['user', 'project', 'module'])
            ->where('status', 'review')
            ->latest('updated_at');
        if ($adminBidangScope) {
            $pendingTasksQuery->whereHas('project', function ($query) use ($adminBidangScope) {
                $this->applyProjectBidangScope($query, $adminBidangScope);
            });
        }
        $pendingTasks = $pendingTasksQuery->get();

        // 7. Available Teams & Landing Schedule View Setting
        $availableTeams = self::getAvailableTeams();
        $jadwalLandingView = DB::table('md_pengaturan')->where('kunci', 'jadwal_landing_view')->value('nilai') ?? 'individual';
        $certificateTemplate = CertificateTemplate::current();

        return view('admin.dashboard', compact(
            'adminRole',
            'isSuperAdmin',
            'adminBidangScope',
            'activeBidangId',
            'adminBidangOptions',
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
            'manageableBidangs',
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
            'activityLogs',
            'pendingTasks',
            'availableTeams',
            'jadwalLandingView',
            'certificateTemplate'
        ));
    }

    /**
     * Get all available teams (default A, B, plus saved custom teams, plus any assigned user teams).
     */
    public static function getAvailableTeams(): array
    {
        $defaultTeams = ['A', 'B'];

        $saved = DB::table('md_pengaturan')->where('kunci', 'daftar_tim')->value('nilai');
        $customTeams = [];
        if ($saved) {
            $decoded = json_decode($saved, true);
            if (is_array($decoded)) {
                $customTeams = $decoded;
            }
        }

        $userTeams = User::where('role', 'user')
            ->whereNotNull('grup')
            ->where('grup', '!=', '')
            ->distinct()
            ->pluck('grup')
            ->toArray();

        $allTeams = array_unique(array_merge($defaultTeams, $customTeams, $userTeams));
        natsort($allTeams);

        return array_values($allTeams);
    }

    private function resolveAdminBidangScope(Request $request, User $admin, bool $isSuperAdmin): ?Bidang
    {
        if ($isSuperAdmin) {
            $bidangId = (int) $request->input('bidang_id', 0);

            return $bidangId > 0 ? Bidang::find($bidangId) : null;
        }

        return $admin->bidang_id ? Bidang::find($admin->bidang_id) : null;
    }

    private function applyUserBidangScope(Builder $query, ?Bidang $bidang): Builder
    {
        if ($bidang) {
            $query->where('bidang_id', $bidang->id);
        }

        return $query;
    }

    private function applyProjectBidangScope(Builder $query, ?Bidang $bidang): Builder
    {
        if ($bidang) {
            $query->where(function (Builder $projectQuery) use ($bidang): void {
                $projectQuery
                    ->whereHas('members', function (Builder $memberQuery) use ($bidang): void {
                        $this->applyUserBidangScope($memberQuery, $bidang);
                    })
                    ->orWhereHas('user', function (Builder $ownerQuery) use ($bidang): void {
                        $this->applyUserBidangScope($ownerQuery, $bidang);
                    });
            });
        }

        return $query;
    }

    private function currentAdminBidangScope(): ?Bidang
    {
        $admin = Auth::user();

        if (! $admin || $admin->role === 'superadmin' || ! $admin->bidang_id) {
            return null;
        }

        return Bidang::find($admin->bidang_id);
    }

    private function ensureCanUseBidang(?int $bidangId): void
    {
        $scope = $this->currentAdminBidangScope();

        if ($scope && (int) $bidangId !== (int) $scope->id) {
            throw ValidationException::withMessages([
                'bidang_id' => 'Admin bidang hanya dapat mengelola peserta pada ' . $scope->nama . '.',
            ]);
        }
    }

    private function ensureCanManageUser(User $user): void
    {
        $scope = $this->currentAdminBidangScope();

        if ($scope && (int) $user->bidang_id !== (int) $scope->id) {
            abort(403, 'Admin bidang hanya dapat mengelola peserta pada ' . $scope->nama . '.');
        }
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
            'grup' => 'nullable|string|max:20',
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

        $this->ensureCanUseBidang((int) $request->input('bidang_id'));

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
            'grup' => $request->input('grup') ?: 'A',
        ])->jadwalMingguan()->create(JadwalMingguan::defaultSchedule());

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Peserta magang baru berhasil ditambahkan!');
    }

    /**
     * Update internship participant (User) details.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->ensureCanManageUser($user);

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
            'grup' => 'nullable|string|max:20',
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
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama or setelah tanggal mulai.',
        ]);

        $this->ensureCanUseBidang((int) $request->input('bidang_id'));

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
            'grup' => $request->input('grup', $user->grup ?? 'A'),
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
        $this->ensureCanManageUser($user);
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
            'bidang_id' => $request->input('bidang_id'),
        ])->with('success_swal', 'Data absensi berhasil dihapus.');
    }

    public function uploadSertifikat(Request $request, User $user)
    {
        $this->ensureCanManageUser($user);

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

    public function uploadSertifikatTemplate(Request $request)
    {
        if (Auth::user()?->role !== 'superadmin') {
            abort(403);
        }

        $request->validate([
            'certificate_template' => 'required|file|mimes:html,htm,txt|max:2048',
        ], [
            'certificate_template.required' => 'File template sertifikat wajib dipilih.',
            'certificate_template.mimes' => 'Template sertifikat harus berupa file HTML.',
            'certificate_template.max' => 'Ukuran template sertifikat maksimal 2 MB.',
        ]);

        CertificateTemplate::storeUploaded($request->file('certificate_template'));

        return redirect()->route('admin.dashboard', ['tab' => 'sertifikat'])
            ->with('success_swal', 'Template sertifikat berhasil diupload dan siap dipakai.');
    }

    public function generateSertifikat(User $user)
    {
        $this->ensureCanManageUser($user);

        if (! $user->tanggal_selesai_magang || $user->tanggal_selesai_magang->isFuture()) {
            abort(404);
        }

        $uploadedTemplate = CertificateTemplate::renderUploaded($user);
        if ($uploadedTemplate) {
            return Pdf::loadHTML($uploadedTemplate)
                ->setPaper([0, 0, 1600, 1131])
                ->download(CertificatePayload::fileName($user));
        }

        $pdf = Pdf::loadView('sertifikat.show', [
            'user' => $user,
            'certificate' => CertificatePayload::forUser($user),
            'assets' => CertificatePayload::assets(),
            'pdfMode' => true,
        ])->setPaper([0, 0, 1600, 1131]);

        return $pdf->download(CertificatePayload::fileName($user));
    }

    public function viewSertifikat(User $user)
    {
        $this->ensureCanManageUser($user);

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
        $this->ensureCanManageUser($user);

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

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('success_swal', 'Jadwal mingguan berhasil disimpan!');
    }

    /**
     * Randomly assign WFO/WFH patterns to all employees in normal view.
     * Alternating pairs: (Senin & Rabu) and (Selasa & Kamis), with Jumat fixed to WFH.
     */
    public function randomizeSchedules()
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('error_swal', 'Belum ada peserta magang untuk dijadwalkan.');
        }

        $patterns = [
            // Pola 1: Senin & Rabu WFO, Selasa & Kamis WFH, Jumat WFH
            ['senin' => 'wfo', 'selasa' => 'wfh', 'rabu' => 'wfo', 'kamis' => 'wfh', 'jumat' => 'wfh'],
            // Pola 2: Senin & Rabu WFH, Selasa & Kamis WFO, Jumat WFH
            ['senin' => 'wfh', 'selasa' => 'wfo', 'rabu' => 'wfh', 'kamis' => 'wfo', 'jumat' => 'wfh'],
        ];

        $users->shuffle()->values()->each(function (User $user, int $i) use ($patterns) {
            $user->jadwalMingguan()->updateOrCreate(
                ['user_id' => $user->id],
                $patterns[$i % 2]
            );
        });

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('success_swal', 'Jadwal mingguan normal berhasil diacak! Pola selang-seling pasangan (Senin-Rabu dan Selasa-Kamis) diterapkan, Jumat tetap WFH.');
    }

    /**
     * Store a new custom team.
     */
    public function storeTeam(Request $request)
    {
        $request->validate([
            'nama_tim' => 'required|string|max:20',
        ], [
            'nama_tim.required' => 'Nama / kode tim harus diisi.',
            'nama_tim.max' => 'Nama tim maksimal 20 karakter.',
        ]);

        $rawName = trim((string) $request->input('nama_tim'));
        if (strlen($rawName) === 1) {
            $teamName = strtoupper($rawName);
        } elseif (preg_match('/^tim\s+(.+)$/i', $rawName, $matches)) {
            $teamName = trim($matches[1]);
            if (strlen($teamName) === 1) {
                $teamName = strtoupper($teamName);
            }
        } else {
            $teamName = $rawName;
        }

        $currentTeams = self::getAvailableTeams();
        if (!in_array($teamName, $currentTeams, true)) {
            $currentTeams[] = $teamName;
            natsort($currentTeams);
            $currentTeams = array_values(array_unique($currentTeams));

            DB::table('md_pengaturan')->updateOrInsert(
                ['kunci' => 'daftar_tim'],
                ['nilai' => json_encode($currentTeams), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $displayLabel = (strlen($teamName) <= 2) ? 'Tim ' . $teamName : $teamName;

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team'])
            ->with('success_swal', 'Tim baru "' . $displayLabel . '" berhasil ditambahkan!');
    }

    /**
     * Apply team-based scheduling manually.
     */
    public function updateTeamSchedules(Request $request)
    {
        $request->validate([
            'first_day_mode' => 'required|in:A_WFO,A_WFH',
        ]);

        $mode = $request->input('first_day_mode');
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team'])->with('error_swal', 'Belum ada peserta magang untuk dijadwalkan.');
        }

        $teams = self::getAvailableTeams();
        $pattern1 = JadwalMingguan::grupA();
        $pattern2 = JadwalMingguan::grupB();

        // Build pattern mapping per team
        $teamPatterns = [];
        foreach ($teams as $idx => $t) {
            $isEven = ($idx % 2 === 0);
            if ($mode === 'A_WFO') {
                $teamPatterns[$t] = $isEven ? $pattern1 : $pattern2;
            } else {
                $teamPatterns[$t] = $isEven ? $pattern2 : $pattern1;
            }
        }

        foreach ($users as $user) {
            $group = $user->grup ?: ($teams[0] ?? 'A');
            $pattern = $teamPatterns[$group] ?? ($mode === 'A_WFO' ? $pattern1 : $pattern2);

            $user->jadwalMingguan()->updateOrCreate(
                ['user_id' => $user->id],
                $pattern
            );
        }

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('success_swal', 'Jadwal berbasis tim berhasil diterapkan secara serentak!');
    }

    /**
     * Randomly assign WFH/WFO schedules based on team collectively (serentak per tim).
     */
    public function randomizeTeamSchedules()
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('error_swal', 'Belum ada peserta magang untuk dijadwalkan.');
        }

        $teams = self::getAvailableTeams();
        // Randomly pick which team group starts with WFO on Monday (coin flip)
        $startWithWfo = (bool) random_int(0, 1);

        $pattern1 = JadwalMingguan::grupA();
        $pattern2 = JadwalMingguan::grupB();

        $teamPatterns = [];
        foreach ($teams as $idx => $t) {
            $isEven = ($idx % 2 === 0);
            if ($startWithWfo) {
                $teamPatterns[$t] = $isEven ? $pattern1 : $pattern2;
            } else {
                $teamPatterns[$t] = $isEven ? $pattern2 : $pattern1;
            }
        }

        foreach ($users as $user) {
            $group = $user->grup ?: ($teams[0] ?? 'A');
            $pattern = $teamPatterns[$group] ?? ($startWithWfo ? $pattern1 : $pattern2);

            $user->jadwalMingguan()->updateOrCreate(
                ['user_id' => $user->id],
                $pattern
            );
        }

        $detail = $startWithWfo 
            ? 'Tim Ganjil (A, C, ...): Senin-Rabu WFO | Tim Genap (B, D, ...): Senin-Rabu WFH' 
            : 'Tim Ganjil (A, C, ...): Senin-Rabu WFH | Tim Genap (B, D, ...): Senin-Rabu WFO';

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])->with('success_swal', 'Jadwal tim berhasil diacak serentak! (' . $detail . ', Jumat semua WFH).');
    }

    /**
     * Randomly divide intern participants evenly across all available teams.
     */
    public function randomizeTeamMembers()
    {
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team'])->with('error_swal', 'Belum ada peserta magang untuk dibagi ke dalam tim.');
        }

        $teams = self::getAvailableTeams();
        if (empty($teams)) {
            $teams = ['A', 'B'];
        }

        $shuffled = $users->shuffle()->values();
        $teamCount = count($teams);

        foreach ($shuffled as $index => $user) {
            $group = $teams[$index % $teamCount];
            $user->update(['grup' => $group]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team'])->with('success_swal', 'Anggota tim berhasil diacak secara merata ke seluruh tim yang tersedia!');
    }

    /**
     * Update team assignment for intern participants (names remain fixed from master table).
     */
    public function updateTeamMembers(Request $request)
    {
        $request->validate([
            'members' => 'required|array',
            'members.*.grup' => 'required|string|max:20',
        ], [
            'members.*.grup.required' => 'Pilihan tim harus dipilih.',
        ]);

        foreach ($request->input('members') as $userId => $data) {
            $user = User::where('role', 'user')->find($userId);
            if ($user && isset($data['grup'])) {
                $user->update([
                    'grup' => $data['grup'],
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            $users = User::where('role', 'user')->get();
            $availableTeams = self::getAvailableTeams();
            $teamCounts = [];
            foreach ($availableTeams as $t) {
                $teamCounts[$t] = $users->where('grup', $t)->count();
            }

            return response()->json([
                'success' => true,
                'message' => 'Penugasan tim peserta magang berhasil disimpan!',
                'teamCounts' => $teamCounts,
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team'])->with('success_swal', 'Pembagian tim peserta magang berhasil disimpan!');
    }

    /**
     * Update landing page (home) schedule display mode setting.
     */
    public function updateLandingScheduleView(Request $request)
    {
        $request->validate([
            'jadwal_landing_view' => 'required|in:team,individual',
        ]);

        $mode = $request->input('jadwal_landing_view');
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => $mode, 'updated_at' => now(), 'created_at' => now()]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mode' => $mode,
                'message' => 'Pengaturan tampilan jadwal di halaman utama berhasil disimpan!',
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal'])
            ->with('success_swal', 'Pengaturan tampilan jadwal di halaman utama berhasil diperbarui!');
    }

    /**
     * Export Monthly Rekap to Excel.
     */
    public function exportExcel(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $bidangId = $request->input('bidang_id') ? (int) $request->input('bidang_id') : null;

        return Excel::download(new AbsensiExport($month, $year, $bidangId), "Rekap_Absensi_{$month}_{$year}.xlsx");
    }

    /**
     * Export Monthly Rekap to PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $bidangScope = $request->input('bidang_id') ? Bidang::find((int) $request->input('bidang_id')) : null;

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

        $employeesQuery = User::with(['absensi' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->with('statusMaster')
                  ->orderBy('tanggal', 'asc');
        }])->where('role', 'user')->orderBy('nama', 'asc');
        $this->applyUserBidangScope($employeesQuery, $bidangScope);
        $employeesWithAbsensi = $employeesQuery->get();

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
