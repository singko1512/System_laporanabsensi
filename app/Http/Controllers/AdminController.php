<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use App\Models\Pengaturan;
use App\Models\JadwalMingguan;
use App\Models\MasterData;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Handle Admin PIN Verification.
     */
    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ], [
            'pin.required' => 'PIN Admin wajib diisi.',
        ]);

        $pin = $request->input('pin');
        $pengaturan = Pengaturan::where('kunci', 'pin_admin')->first();

        if ($pengaturan && Hash::check($pin, $pengaturan->nilai)) {
            session(['admin_authenticated' => true]);
            return redirect()->route('admin.dashboard')->with('success_swal', 'Login Admin Berhasil!');
        }

        return redirect()->back()->with('error_swal', 'PIN Admin salah atau tidak valid.');
    }

    /**
     * Log out Admin session.
     */
    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect()->route('home')->with('success_swal', 'Logout berhasil.');
    }

    /**
     * Display the Admin Dashboard.
     */
    public function dashboard(Request $request)
    {
        $users = User::with('jadwalMingguan')->orderBy('nama', 'asc')->get();
        $magangSearch = trim((string) $request->input('magang_search', ''));
        $pembimbingMagang = trim((string) $request->input('pembimbing_magang', ''));

        $pembimbingOptions = User::query()
            ->whereNotNull('pembimbing_magang')
            ->where('pembimbing_magang', '<>', '')
            ->select('pembimbing_magang')
            ->distinct()
            ->orderBy('pembimbing_magang')
            ->pluck('pembimbing_magang');

        $magangQuery = User::query()->orderBy('pembimbing_magang')->orderBy('nama');

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
        $projects = Project::with(['user', 'members', 'statusMaster', 'notes.user', 'notes.kategoriMaster', 'dayAssignments.user'])
            ->orderByRaw('status_id = ? asc', [$selesaiProjectStatusId])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        return view('admin.dashboard', compact(
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
            'month',
            'year',
            'search',
            'status',
            'magangSearch',
            'pembimbingMagang'
        ));
    }

    /**
     * Store new internship participant (User).
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:md_user,email',
            'pembimbing_magang' => 'nullable|string|max:100',
            'bidang_magang' => 'nullable|string|max:100',
            'tanggal_mulai_magang' => 'nullable|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
        ], [
            'nama.required' => 'Nama peserta magang wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'tanggal_mulai_magang.date' => 'Tanggal mulai magang tidak valid.',
            'tanggal_selesai_magang.date' => 'Tanggal selesai magang tidak valid.',
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama atau setelah tanggal mulai.',
        ]);

        User::create([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
            'pembimbing_magang' => $request->input('pembimbing_magang'),
            'bidang_magang' => $request->input('bidang_magang'),
            'tanggal_mulai_magang' => $request->input('tanggal_mulai_magang'),
            'tanggal_selesai_magang' => $request->input('tanggal_selesai_magang'),
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
            'email' => 'nullable|email|max:100|unique:md_user,email,' . $id,
            'pembimbing_magang' => 'nullable|string|max:100',
            'bidang_magang' => 'nullable|string|max:100',
            'tanggal_mulai_magang' => 'nullable|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
        ], [
            'nama.required' => 'Nama peserta magang wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'tanggal_mulai_magang.date' => 'Tanggal mulai magang tidak valid.',
            'tanggal_selesai_magang.date' => 'Tanggal selesai magang tidak valid.',
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama atau setelah tanggal mulai.',
        ]);

        $user->update([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
            'pembimbing_magang' => $request->input('pembimbing_magang'),
            'bidang_magang' => $request->input('bidang_magang'),
            'tanggal_mulai_magang' => $request->input('tanggal_mulai_magang'),
            'tanggal_selesai_magang' => $request->input('tanggal_selesai_magang'),
        ]);

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
}
