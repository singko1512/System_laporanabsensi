<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use App\Models\Bidang;
use App\Models\MasterData;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ActivityLog;
use App\Models\ProjectTaskParticipant;
use App\Models\ProjectModule;
use App\Support\CertificatePayload;
use App\Support\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private const OFFICIAL_BIDANG_NAMES = [
        'Bidang Pengelolaan Informasi dan Komunikasi Publik',
        'Bidang Aplikasi Informatika',
        'Bidang Infrastruktur Teknologi',
        'Bidang Persandian dan Statistik',
        'Kepala UPT Radio dan Televisi',
    ];

    /**
     * Display the landing page.
     */
    public function home()
    {
        $users = User::with(['jadwalMingguan', 'bidang'])->where('role', 'user')->orderBy('nama', 'asc')->get();
        $bidangsByName = Bidang::whereIn('nama', self::OFFICIAL_BIDANG_NAMES)->get()->keyBy('nama');
        $scheduleBidangGroups = collect(self::OFFICIAL_BIDANG_NAMES)->map(function (string $bidangName) use ($users, $bidangsByName) {
            $bidang = $bidangsByName->get($bidangName);
            $members = $users
                ->filter(function (User $user) use ($bidang, $bidangName): bool {
                    return ($bidang && (int) ($user->bidang_id ?? 0) === (int) $bidang->id)
                        || $user->bidang_magang === $bidangName;
                })
                ->sortBy('nama')
                ->values();

            return [
                'id' => $bidang?->id,
                'nama' => $bidangName,
                'users' => $members,
            ];
        });

        $jadwalLandingView = \Illuminate\Support\Facades\DB::table('md_pengaturan')->where('kunci', 'jadwal_landing_view')->value('nilai') ?? 'individual';
        $availableTeams = AdminController::getAvailableTeams();

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(4);
        $todayKey = match (Carbon::now()->dayOfWeekIso) {
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            default => null,
        };
        $dayMap = [
            'senin' => $weekStart->copy(),
            'selasa' => $weekStart->copy()->addDay(),
            'rabu' => $weekStart->copy()->addDays(2),
            'kamis' => $weekStart->copy()->addDays(3),
            'jumat' => $weekStart->copy()->addDays(4),
        ];

        return view('home', compact('users', 'scheduleBidangGroups', 'weekStart', 'weekEnd', 'todayKey', 'dayMap', 'jadwalLandingView', 'availableTeams'));
    }

    /**
     * Combined absensi page: form + rekap in one view.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $users = User::where('role', 'user')->orderBy('nama', 'asc')->get();
        $absensiStatuses = MasterData::options(MasterData::ABSENSI_STATUS);

        $bidangList = \App\Models\Bidang::orderBy('nama', 'asc')->pluck('nama');

        // Determine active tab
        $activeTab = $request->input('tab', 'form');
        if (! in_array($activeTab, ['form', 'timeline'], true)) {
            return redirect()
                ->route('absensi.index', ['tab' => 'form'])
                ->with('error_swal', 'Peserta magang hanya dapat mengakses absensi dan timeline proyek.');
        }

        // Rekap data
        $selectedUser = null;
        $absensi = collect();
        $stats = [
            'hadir' => 0, 'wfh' => 0, 'sakit' => 0, 'izin' => 0,
            'persentase' => 0, 'total_hari_kerja' => 0,
        ];

        $filterType = $request->input('filter_type', 'all');
        $userId = $currentUser->id;
        $bidangMagang = $request->input('bidang_magang');
        if ($userId && !$bidangMagang) {
            $tempUser = User::find($userId);
            if ($tempUser) {
                $bidangMagang = $tempUser->bidang_magang;
                $request->merge(['bidang_magang' => $bidangMagang]);
            }
        }
        $timelineUser = null;
        $timelineProjects = collect();

        if (true) {
            if ($filterType === 'date' && $request->filled('date')) {
                $startDate = Carbon::parse($request->input('date'))->startOfDay();
                $endDate = Carbon::parse($request->input('date'))->endOfDay();
            } elseif ($filterType === 'month' && $request->filled('month_filter')) {
                $parts = explode('-', $request->input('month_filter'));
                $startDate = Carbon::createFromDate((int) $parts[0], (int) $parts[1], 1)->startOfMonth();
                $endDate = Carbon::createFromDate((int) $parts[0], (int) $parts[1], 1)->endOfMonth();
            } else {
                $startDate = Carbon::create(2020, 1, 1);
                $endDate = Carbon::today()->endOfDay();
            }

            $absensiQuery = Absensi::with(['user', 'statusMaster'])
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            if ($userId) {
                $selectedUser = User::findOrFail($userId);
                $absensiQuery->where('user_id', $userId);
            } elseif ($bidangMagang) {
                $absensiQuery->whereHas('user', function ($query) use ($bidangMagang) {
                    $query->where('bidang_magang', $bidangMagang);
                });
            }

            $absensi = $absensiQuery->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

            $totalWorkdays = 0;
            $tempDate = $startDate->copy();
            $maxCalcDate = $endDate->gt(Carbon::today()) ? Carbon::today() : $endDate;
            while ($tempDate->lte($maxCalcDate)) {
                if (! $tempDate->isWeekend()) {
                    $totalWorkdays++;
                }
                $tempDate->addDay();
            }
            if ($totalWorkdays === 0) {
                $totalWorkdays = 1;
            }

            $stats['hadir'] = $absensi->where('status', 'hadir')->count();
            $stats['wfh'] = $absensi->where('status', 'wfh')->count();
            $stats['sakit'] = $absensi->where('status', 'sakit')->count();
            $stats['izin'] = $absensi->where('status', 'izin')->count();
            $stats['total_hari_kerja'] = $totalWorkdays;
            $stats['persentase'] = round((($stats['hadir'] + $stats['wfh']) / $totalWorkdays) * 100, 1);
        }

        $today = Carbon::today(config('app.timezone'))->toDateString();
        $todayAttendance = Absensi::with('task.project', 'task.module')
            ->where('user_id', $userId)
            ->where('tanggal', $today)
            ->first();

        $myActiveTasks = ProjectTask::with(['project', 'module'])
            ->where('user_id', $userId)
            ->where('status', 'sedang_dikerjakan')
            ->whereNull('catatan_revisi')
            ->get();

        $myRevisionTasks = ProjectTask::with(['project', 'module'])
            ->where('user_id', $userId)
            ->where('status', 'sedang_dikerjakan')
            ->whereNotNull('catatan_revisi')
            ->get();

        $myReviewTasks = ProjectTask::with(['project', 'module'])
            ->where('user_id', $userId)
            ->where('status', 'review')
            ->get();

        $myCompletedTasks = ProjectTask::with(['project', 'module'])
            ->where('user_id', $userId)
            ->where('status', 'selesai')
            ->get();

        $myTodayTasks = $myActiveTasks->merge($myRevisionTasks);

        $hasActiveTask = $myActiveTasks->isNotEmpty() || $myRevisionTasks->isNotEmpty() || $myReviewTasks->isNotEmpty();

        $activeStatusId = MasterData::idFor(MasterData::PROJECT_STATUS, 'aktif');
        $allActiveProjects = Project::with([
                'statusMaster',
                'members',
                'modules.tasks',
                'tasks.module',
                'tasks.user',
            ])
            ->where('status_id', $activeStatusId)
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $timelineProjects = $allActiveProjects;

        // Determine active selected project
        $selectedProject = null;
        if ($hasActiveTask) {
            $firstActive = $myTodayTasks->first() ?: $myReviewTasks->first();
            $selectedProject = $firstActive ? $firstActive->project : null;
            if ($selectedProject) {
                session(['active_project_id' => $selectedProject->id]);
            }
            if ($request->has('project_id') || $request->has('reset_project')) {
                session()->flash('warning_swal', 'Anda masih memiliki tugas yang sedang dikerjakan. Silakan klik "Batal Pilih" pada tugas di atas jika ingin mengganti proyek.');
            }
        } elseif ($request->has('reset_project')) {
            session()->forget('active_project_id');
            $selectedProject = null;
        } elseif ($request->filled('project_id')) {
            $selectedProjectId = (int) $request->input('project_id');
            $selectedProject = $allActiveProjects->firstWhere('id', $selectedProjectId) ?? Project::with([
                'statusMaster',
                'members',
                'modules.tasks.user',
                'tasks.module',
                'tasks.user',
            ])->find($selectedProjectId);
            if ($selectedProject) {
                session(['active_project_id' => $selectedProject->id]);
            }
        } elseif (session('active_project_id')) {
            $selectedProjectId = (int) session('active_project_id');
            $selectedProject = $allActiveProjects->firstWhere('id', $selectedProjectId);
            if (!$selectedProject) {
                session()->forget('active_project_id');
            }
        }

        if ($hasActiveTask) {
            $activeTaskIds = $myActiveTasks->pluck('id')
                ->merge($myRevisionTasks->pluck('id'))
                ->merge($myReviewTasks->pluck('id'))
                ->unique();
            $availableTasks = ProjectTask::with(['project', 'module', 'user'])
                ->whereIn('id', $activeTaskIds)
                ->get();
            $allAvailableTasks = collect();
            $availableModules = collect();
            $allAvailableModules = collect();
        } else {
            if ($selectedProject) {
                // Semua task yang belum diambil pada proyek terpilih
                $allAvailableTasks = ProjectTask::with(['project', 'module', 'user'])
                    ->where('project_id', $selectedProject->id)
                    ->whereNull('user_id')
                    ->where('status', 'belum_dikerjakan')
                    ->orderBy('tanggal_selesai')
                    ->orderBy('judul')
                    ->get();
                $availableTasks = $allAvailableTasks;

                // Modul yang belum di-breakdown (belum memiliki task sama sekali)
                $availableModules = ProjectModule::with(['project', 'tasks'])
                    ->where('project_id', $selectedProject->id)
                    ->whereDoesntHave('tasks')
                    ->orderBy('nama')
                    ->get();
                $allAvailableModules = $availableModules;
            } else {
                $availableTasks = collect();
                $allAvailableTasks = collect();
                $availableModules = collect();
                $allAvailableModules = collect();
            }
        }

        $timelineUser = $currentUser;
        $taskParticipants = ProjectTaskParticipant::with([
                'task.project',
                'task.module',
                'task.participants.user',
                'submissions.replies.user',
                'submissions.reviewer',
            ])
            ->where('user_id', $userId)
            ->latest('joined_at')
            ->get();

        return view('absensi.index', compact(
            'users',
            'bidangList',
            'activeTab',
            'selectedUser',
            'absensi',
            'stats',
            'filterType',
            'timelineUser',
            'timelineProjects',
            'absensiStatuses',
            'currentUser',
            'todayAttendance',
            'availableTasks',
            'taskParticipants',
            // Data task baru untuk user dashboard
            'myActiveTasks',
            'myRevisionTasks',
            'myReviewTasks',
            'myCompletedTasks',
            'myTodayTasks',
            'allAvailableTasks',
            'hasActiveTask',
            'availableModules',
            'allAvailableModules',
            'allActiveProjects',
            'selectedProject'
        ));
    }

    /**
     * Store daily attendance submission.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today(config('app.timezone'))->toDateString();

        $absensi = Absensi::where('user_id', $userId)
            ->where('tanggal', $today)
            ->first();

        if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
            return redirect()->back()
                ->withInput()
                ->with('error_swal', 'Absensi masuk dan pulang hari ini sudah lengkap.');
        }

        $hasProjects = \Illuminate\Support\Facades\DB::table('md_project_user')->where('user_id', $userId)->exists();
        $isCheckout = $absensi && ! $absensi->jam_pulang;
        $status = (string) $request->input('status');

        $request->validate([
            'status' => [
                'required',
                Rule::exists('md_master_data', 'kode')
                    ->where(fn ($query) => $query->where('jenis', MasterData::ABSENSI_STATUS)->where('is_active', true)),
            ],
            'task_id' => [
                Rule::requiredIf(function () use ($isCheckout, $status, $userId) {
                    if ($isCheckout || !in_array($status, ['hadir', 'wfh'], true)) {
                        return false;
                    }
                    
                    $hasProjects = \Illuminate\Support\Facades\DB::table('md_project_user')->where('user_id', $userId)->exists();
                    if ($hasProjects) {
                        return ProjectTask::whereNull('user_id')
                            ->where('status', 'belum_dikerjakan')
                            ->whereHas('project', function ($query) use ($userId) {
                                $query->where('status', 'aktif')
                                    ->where(function ($sub) use ($userId) {
                                        $sub->whereHas('members', function ($m) use ($userId) {
                                            $m->where('md_user.id', $userId);
                                        })->orWhereDoesntHave('members');
                                    });
                            })
                            ->exists();
                    } else {
                        return ProjectTask::whereNull('user_id')
                            ->where('status', 'belum_dikerjakan')
                            ->whereHas('project', function ($query) {
                                $query->where('status', 'aktif');
                            })
                            ->exists();
                    }
                }),
                'nullable',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && str_starts_with($value, 'module_')) {
                        $id = (int) str_replace('module_', '', $value);
                        if (!\App\Models\ProjectModule::where('id', $id)->exists()) {
                            $fail('Modul yang dipilih tidak valid.');
                        }
                    } elseif (is_string($value) && str_starts_with($value, 'task_')) {
                        $id = (int) str_replace('task_', '', $value);
                        if (!\App\Models\ProjectTask::where('id', $id)->exists()) {
                            $fail('Tugas yang dipilih tidak valid.');
                        }
                    } else {
                        $id = (int) $value;
                        if (!\App\Models\ProjectTask::where('id', $id)->exists()) {
                            $fail('Tugas yang dipilih tidak valid.');
                        }
                    }
                }
            ],
            'foto' => [
                Rule::requiredIf($isCheckout && in_array($status, ['hadir', 'wfh'], true)),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'foto_kamera' => [
                Rule::requiredIf((! $isCheckout && in_array($status, ['hadir', 'wfh', 'sakit'], true)) || ($isCheckout && $status === 'sakit')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'lokasi_latitude' => 'required|numeric|between:-90,90',
            'lokasi_longitude' => 'required|numeric|between:-180,180',
            'lokasi_akurasi' => 'nullable|numeric|min:0|max:99999999',
            'keterangan' => [
                Rule::requiredIf($status === 'izin' || ($isCheckout && in_array($status, ['hadir', 'wfh'], true))),
                'nullable',
                'string',
            ],
        ], [
            'status.required' => 'Pilih status absensi.',
            'task_id.required' => 'Pilih tugas atau modul yang akan dikerjakan hari ini.',
            'task_id.required_if' => 'Pilih tugas atau modul yang akan dikerjakan hari ini.',
            'foto.required' => 'Lampiran wajib diunggah untuk status Hadir/WFH saat absen pulang.',
            'foto.image' => 'Berkas harus berupa gambar (JPG, PNG, JPEG, WEBP).',
            'foto.mimes' => 'Berkas harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran berkas maksimal 5 MB.',
            'foto_kamera.required' => 'Foto kamera wajib diambil untuk status dan waktu absensi ini.',
            'foto_kamera.image' => 'Foto kamera harus berupa gambar (JPG, PNG, JPEG, WEBP).',
            'foto_kamera.mimes' => 'Foto kamera harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            'foto_kamera.max' => 'Ukuran foto kamera maksimal 5 MB.',
            'lokasi_latitude.required' => 'Lokasi GPS wajib dikunci untuk semua status absensi.',
            'lokasi_longitude.required' => 'Lokasi GPS wajib dikunci untuk semua status absensi.',
            'keterangan.required' => $isCheckout && in_array($status, ['hadir', 'wfh'], true)
                ? 'Laporan pekerjaan hari ini wajib diisi saat absen pulang Hadir/WFH.'
                : 'Alasan izin wajib diisi.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $this->storeAbsensiFile($request->file('foto'), $userId, 'lampiran');
        }

        $fotoKameraPath = null;
        if ($request->hasFile('foto_kamera')) {
            $fotoKameraPath = $this->storeAbsensiFile($request->file('foto_kamera'), $userId, 'kamera');
        }

        $statusId = MasterData::idFor(MasterData::ABSENSI_STATUS, $status);
        $now = now(config('app.timezone'));
        
        $taskIdInput = $request->input('task_id');
        $taskId = null;
        if ($taskIdInput) {
            if (str_starts_with($taskIdInput, 'module_')) {
                $moduleId = (int) str_replace('module_', '', $taskIdInput);
                $module = ProjectModule::findOrFail($moduleId);
                
                if (!$module->project->members()->where('md_user.id', $userId)->exists()) {
                    $module->project->members()->attach($userId);
                }
                
                $task = ProjectTask::create([
                    'project_id' => $module->project_id,
                    'module_id' => $module->id,
                    'user_id' => $userId,
                    'judul' => 'Pengerjaan Modul: ' . $module->nama,
                    'deskripsi' => 'Pengerjaan seluruh modul ' . $module->nama,
                    'tanggal_mulai' => $module->tanggal_mulai,
                    'tanggal_selesai' => $module->tanggal_selesai,
                    'status' => 'sedang_dikerjakan',
                    'join_window_minutes' => 999999,
                    'urutan' => ProjectTask::where('project_id', $module->project_id)->max('urutan') + 1,
                ]);
                
                ProjectTaskParticipant::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'status' => 'joined',
                    'joined_at' => now(),
                    'contribution_percentage' => 100.00,
                ]);
                
                $task->recalculateModuleProgress();
                
                ActivityLog::create([
                    'user_id' => $userId,
                    'project_id' => $module->project_id,
                    'aktivitas' => User::find($userId)->nama . ' mengambil modul saat absen masuk: ' . $module->nama,
                ]);
                
                $taskId = $task->id;
            } elseif (str_starts_with($taskIdInput, 'task_')) {
                $taskId = (int) str_replace('task_', '', $taskIdInput);
            } else {
                $taskId = (int) $taskIdInput;
            }
        }
        $locationPayload = [
            'lokasi_latitude' => $request->input('lokasi_latitude'),
            'lokasi_longitude' => $request->input('lokasi_longitude'),
            'lokasi_akurasi' => $request->input('lokasi_akurasi'),
            'lokasi_diambil_pada' => $now,
        ];

        if (! $absensi) {
            if ($taskId) {
                try {
                    $this->joinTaskForUser($taskId, $userId);
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error_swal', $e->getMessage());
                }
            }

            Absensi::create([
                'user_id' => $userId,
                'task_id' => $taskId,
                'tanggal' => $today,
                'jam_masuk' => $now->format('H:i:s'),
                'status' => $status,
                'status_id' => $statusId,
                'status_masuk_id' => $statusId,
                'foto' => $fotoPath,
                'foto_kamera' => $fotoKameraPath,
                'foto_masuk' => $fotoKameraPath,
                ...$locationPayload,
                'lokasi_masuk_latitude' => $request->input('lokasi_latitude'),
                'lokasi_masuk_longitude' => $request->input('lokasi_longitude'),
                'lokasi_masuk_akurasi' => $request->input('lokasi_akurasi'),
                'lokasi_masuk_diambil_pada' => $now,
                'laporan' => $request->input('keterangan'),
            ]);

            return redirect()->route('absensi.index')->with('success_swal', 'Absensi masuk berhasil disimpan.');
        }

        $absensi->update([
            'jam_pulang' => $now->format('H:i:s'),
            'status_pulang_id' => $statusId,
            'foto_pulang' => $fotoPath ?: $fotoKameraPath,
            'foto_kamera' => $fotoKameraPath ?: $absensi->foto_kamera,
            'foto' => $fotoPath ?: $absensi->foto,
            ...$locationPayload,
            'lokasi_pulang_latitude' => $request->input('lokasi_latitude'),
            'lokasi_pulang_longitude' => $request->input('lokasi_longitude'),
            'lokasi_pulang_akurasi' => $request->input('lokasi_akurasi'),
            'lokasi_pulang_diambil_pada' => $now,
            'laporan' => $request->input('keterangan') ?: $absensi->laporan,
        ]);

        return redirect()->route('absensi.index')->with('success_swal', 'Absensi pulang berhasil disimpan. Terima kasih atas kerja keras Anda hari ini!');
    }

    /**
     * Legacy routes redirect to combined page.
     */
    public function showForm()
    {
        return redirect()->route('absensi.index', ['tab' => 'form']);
    }

    public function rekap(Request $request)
    {
        return redirect()->route('absensi.index', ['tab' => 'form']);
    }

    public function lampiran(Absensi $absensi)
    {
        return $this->serveAbsensiFile($absensi->foto);
    }

    public function kamera(Absensi $absensi)
    {
        return $this->serveAbsensiFile($absensi->foto_kamera ?: $absensi->foto_masuk ?: $absensi->foto_pulang);
    }

    public function sertifikat(string $slug)
    {
        $user = User::all()->first(function (User $user) use ($slug) {
            return Str::slug($user->nama) === $slug;
        });

        if (! $user || ! $user->tanggal_selesai_magang || $user->tanggal_selesai_magang->isFuture()) {
            abort(404);
        }

        $uploadedTemplate = CertificateTemplate::renderUploaded($user);
        if ($uploadedTemplate) {
            return response($uploadedTemplate);
        }

        return view('sertifikat.show', [
            'user' => $user,
            'certificate' => CertificatePayload::forUser($user),
            'assets' => CertificatePayload::assets(),
            'pdfMode' => false,
        ]);
    }

    private function storeAbsensiFile($file, int $userId, string $folder): string
    {
        $relativeDir = 'uploads/absensi/' . $folder;
        $uploadDir = public_path($relativeDir);

        File::ensureDirectoryExists($uploadDir, 0755, true);

        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $filename = now(config('app.timezone'))->format('Ymd_His')
            . '_' . $userId
            . '_' . Str::random(10)
            . '.' . $extension;

        $file->move($uploadDir, $filename);

        return $relativeDir . '/' . $filename;
    }

    private function serveAbsensiFile(?string $path)
    {
        if (! $path) {
            abort(404);
        }

        $uploadsRoot = realpath(public_path('uploads'));
        $filePath = realpath(public_path($path));

        if (! $uploadsRoot || ! $filePath || ! Str::startsWith($filePath, $uploadsRoot) || ! File::isFile($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }

    private function joinTaskForUser(int $taskId, int $userId): void
    {
        $task = ProjectTask::with('project.members')->findOrFail($taskId);

        if (! $task->project->members->contains('id', $userId)) {
            $task->project->members()->attach($userId);
        }

        $now = now(config('app.timezone'));

        if (! $task->user_id) {
            $hasActiveTask = ProjectTask::where('user_id', $userId)
                ->whereIn('status', ['sedang_dikerjakan', 'review', 'revision'])
                ->exists();
            if ($hasActiveTask) {
                throw new \Exception("Anda hanya dapat mengambil satu tugas dalam satu waktu. Selesaikan tugas aktif Anda terlebih dahulu.");
            }

            $task->update([
                'user_id' => $userId,
                'status' => 'sedang_dikerjakan',
            ]);

            ActivityLog::create([
                'user_id' => $userId,
                'project_id' => $task->project_id,
                'aktivitas' => User::find($userId)->nama . ' mengambil tugas saat absen masuk: ' . $task->judul,
            ]);
        }

        ProjectTaskParticipant::firstOrCreate(
            ['task_id' => $task->id, 'user_id' => $userId],
            [
                'joined_at' => $now,
                'status' => 'joined',
                'contribution_percentage' => 100.00,
            ]
        );

        $task->recalculateModuleProgress();
    }

    private function recalculateTaskContribution(ProjectTask $task): void
    {
        $count = max($task->participants->count(), 1);
        $share = round(100 / $count, 2);

        foreach ($task->participants as $participant) {
            $participant->update(['contribution_percentage' => $share]);
        }
    }
}
