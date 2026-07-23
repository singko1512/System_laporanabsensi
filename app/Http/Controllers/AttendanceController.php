<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use Illuminate\Http\Request;
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

            $absensiQuery = Absensi::with('user')
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            if ($userId) {
                $selectedUser = User::findOrFail($userId);
                $absensiQuery->where('user_id', $userId);
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

        return view('absensi.index', compact(
            'users', 'activeTab', 'selectedUser', 'absensi', 'stats', 'filterType'
        ));
    }

    /**
     * Store daily attendance submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:md_user,id',
            'status' => 'required|in:hadir,wfh,sakit,izin',
            'foto' => 'required_if:status,hadir,wfh,sakit|image|max:2048',
            'laporan' => 'required|string',
        ], [
            'user_id.required' => 'Pilih nama pegawai terlebih dahulu.',
            'user_id.exists' => 'Pegawai tidak terdaftar.',
            'status.required' => 'Pilih status absensi.',
            'foto.required_if' => 'Foto/dokumen wajib diunggah untuk status Hadir, WFH, dan Sakit.',
            'foto.image' => 'Berkas harus berupa gambar (JPG, PNG, JPEG).',
            'foto.max' => 'Ukuran berkas maksimal 2 MB.',
            'laporan.required' => 'Keterangan/laporan wajib diisi.',
        ]);

        $userId = $request->input('user_id');
        $today = Carbon::today()->toDateString();

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
            $file = $request->file('foto');
            $filename = time() . '_' . $userId . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $fotoPath = 'uploads/' . $filename;
        }

        Absensi::create([
            'user_id' => $userId,
            'tanggal' => $today,
            'status' => $request->input('status'),
            'foto' => $fotoPath,
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
}
