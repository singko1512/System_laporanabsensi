<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use App\Models\Pengaturan;
use App\Models\JadwalMingguan;
use Illuminate\Http\Request;
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

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $absensiQuery = Absensi::with('user')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $absensiQuery->whereHas('user', function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '' && $status !== 'all') {
            $absensiQuery->where('status', $status);
        }

        $absensiRecords = $absensiQuery->get();

        return view('admin.dashboard', compact(
            'users',
            'absensiRecords',
            'month',
            'year',
            'search',
            'status'
        ));
    }

    /**
     * Store new employee (User).
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:md_user,email',
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        User::create([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
        ])->jadwalMingguan()->create(JadwalMingguan::defaultSchedule());

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Pegawai baru berhasil ditambahkan!');
    }

    /**
     * Update employee (User) details.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:md_user,email,' . $id,
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        $user->update([
            'nama' => $request->input('nama'),
            'email' => $request->input('email'),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Delete employee (User).
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // automatically cascades absensi deletion due to DB schema constraint

        return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Pegawai berhasil dihapus!');
    }

    /**
     * Save weekly schedules for all employees.
     */
    public function updateSchedules(Request $request)
    {
        $weekdays = ['senin', 'selasa', 'rabu', 'kamis'];
        $schedules = $request->input('schedules', []);

        foreach ($schedules as $userId => $schedule) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $data = ['jumat' => 'wfh'];
            foreach ($weekdays as $day) {
                $data[$day] = in_array($schedule[$day] ?? 'wfo', ['wfo', 'wfh'], true)
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
            return redirect()->route('admin.dashboard', ['tab' => 'jadwal'])->with('error_swal', 'Belum ada pegawai untuk diacak jadwalnya.');
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

        return redirect()->route('admin.dashboard', ['tab' => 'jadwal'])->with('success_swal', 'Jadwal berhasil diacak! Jumat tetap WFH untuk semua pegawai.');
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
