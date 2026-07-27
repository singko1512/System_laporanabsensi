<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use App\Models\MasterData;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the landing page.
     */
    public function home()
    {
        $users = User::with('jadwalMingguan')->orderBy('nama', 'asc')->get();

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

        return view('home', compact('users', 'weekStart', 'weekEnd', 'todayKey', 'dayMap'));
    }

    /**
     * Combined absensi page: form + rekap in one view.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('nama', 'asc')->get();
        $absensiStatuses = MasterData::options(MasterData::ABSENSI_STATUS);

        $bidangList = \App\Models\Bidang::orderBy('nama', 'asc')->pluck('nama');

        // Determine active tab
        $activeTab = $request->input('tab', 'form');

        // Rekap data
        $selectedUser = null;
        $absensi = collect();
        $stats = [
            'hadir' => 0, 'wfh' => 0, 'sakit' => 0, 'izin' => 0,
            'persentase' => 0, 'total_hari_kerja' => 0,
        ];

        $filterType = $request->input('filter_type', 'all');
        $userId = $request->input('user_id');
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

        if ($activeTab === 'rekap') {
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

        if ($activeTab === 'timeline' && $userId) {
            $timelineUser = User::findOrFail($userId);
            $timelineProjects = Project::with(['statusMaster', 'notes.user', 'notes.kategoriMaster', 'members', 'dayAssignments.user'])
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhereHas('members', function ($memberQuery) use ($userId) {
                            $memberQuery->where('md_user.id', $userId);
                        });
                })
                ->orderByRaw('status_id = ? asc', [MasterData::idFor(MasterData::PROJECT_STATUS, 'selesai')])
                ->orderBy('tanggal_mulai', 'desc')
                ->get();
        }

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
            'absensiStatuses'
        ));
    }

    /**
     * Store daily attendance submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:md_user,id',
            'status' => [
                'required',
                Rule::exists('md_master_data', 'kode')
                    ->where(fn ($query) => $query->where('jenis', MasterData::ABSENSI_STATUS)->where('is_active', true)),
            ],
            'foto' => 'required_if:status,sakit|image|mimes:jpg,jpeg,png,webp|max:5120',
            'foto_kamera' => 'required_if:status,hadir|image|mimes:jpg,jpeg,png,webp|max:5120',
            'lokasi_latitude' => 'required_if:status,wfh|nullable|numeric|between:-90,90',
            'lokasi_longitude' => 'required_if:status,wfh|nullable|numeric|between:-180,180',
            'lokasi_akurasi' => 'nullable|numeric|min:0|max:99999',
            'laporan' => 'required|string',
        ], [
            'user_id.required' => 'Pilih nama peserta magang terlebih dahulu.',
            'user_id.exists' => 'Peserta magang tidak terdaftar.',
            'status.required' => 'Pilih status absensi.',
            'foto.required_if' => 'Lampiran wajib diunggah untuk status Sakit.',
            'foto.image' => 'Berkas harus berupa gambar (JPG, PNG, JPEG, WEBP).',
            'foto.mimes' => 'Berkas harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran berkas maksimal 5 MB.',
            'foto_kamera.required_if' => 'Foto kamera wajib diambil untuk status Hadir.',
            'foto_kamera.image' => 'Foto kamera harus berupa gambar (JPG, PNG, JPEG, WEBP).',
            'foto_kamera.mimes' => 'Foto kamera harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            'foto_kamera.max' => 'Ukuran foto kamera maksimal 5 MB.',
            'lokasi_latitude.required_if' => 'Lokasi wajib diaktifkan untuk absensi WFH.',
            'lokasi_longitude.required_if' => 'Lokasi wajib diaktifkan untuk absensi WFH.',
            'laporan.required' => 'Keterangan/laporan wajib diisi.',
        ]);

        $userId = $request->input('user_id');
        $today = Carbon::today(config('app.timezone'))->toDateString();

        $alreadyExists = Absensi::where('user_id', $userId)
            ->where('tanggal', $today)
            ->exists();

        if ($alreadyExists) {
            return redirect()->back()
                ->withInput()
                ->with('error_swal', 'Anda sudah melakukan absensi hari ini!');
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $this->storeAbsensiFile($request->file('foto'), $userId, 'lampiran');
        }

        $fotoKameraPath = null;
        if ($request->hasFile('foto_kamera')) {
            $fotoKameraPath = $this->storeAbsensiFile($request->file('foto_kamera'), $userId, 'kamera');
        }

        $isWfh = $request->input('status') === 'wfh';

        Absensi::create([
            'user_id' => $userId,
            'tanggal' => $today,
            'status' => $request->input('status'),
            'foto' => $fotoPath,
            'foto_kamera' => $fotoKameraPath,
            'lokasi_latitude' => $isWfh ? $request->input('lokasi_latitude') : null,
            'lokasi_longitude' => $isWfh ? $request->input('lokasi_longitude') : null,
            'lokasi_akurasi' => $isWfh ? $request->input('lokasi_akurasi') : null,
            'lokasi_diambil_pada' => $isWfh ? now(config('app.timezone')) : null,
            'laporan' => $request->input('laporan'),
        ]);

        $user = User::find($userId);
        return redirect()->route('absensi.index')->with('success_swal', 'Absensi ' . $user->nama . ' berhasil disimpan!');
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
        return redirect()->route('absensi.index', array_merge(['tab' => 'rekap'], $request->query()));
    }

    public function lampiran(Absensi $absensi)
    {
        return $this->serveAbsensiFile($absensi->foto);
    }

    public function kamera(Absensi $absensi)
    {
        return $this->serveAbsensiFile($absensi->foto_kamera);
    }

    public function sertifikat(string $slug)
    {
        $user = User::all()->first(function (User $user) use ($slug) {
            return Str::slug($user->nama) === $slug;
        });

        if (! $user || ! $user->tanggal_selesai_magang || $user->tanggal_selesai_magang->isFuture()) {
            abort(404);
        }

        return view('sertifikat.show', compact('user'));
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
}
