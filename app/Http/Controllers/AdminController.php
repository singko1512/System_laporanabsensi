<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use App\Models\Pengaturan;
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
        $users = User::orderBy('nama', 'asc')->get();

        // Monthly Filter parameters
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Calculate weekdays (Monday - Friday) in the range
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

        // Fetch users with their absensi in the range
        $employeesWithAbsensi = User::with(['absensi' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orderBy('tanggal', 'desc');
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
                'absensi' => $emp->absensi,
            ];
        }

        return view('admin.dashboard', compact('users', 'rekapData', 'month', 'year', 'totalWorkdays'));
    }

    /**
     * Store new employee (User).
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'nip_atau_id' => 'nullable|string|max:50|unique:md_user,nip_atau_id',
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nip_atau_id.unique' => 'NIP / ID sudah terdaftar.',
        ]);

        User::create([
            'nama' => $request->input('nama'),
            'nip_atau_id' => $request->input('nip_atau_id'),
        ]);

        return redirect()->route('admin.dashboard')->with('success_swal', 'Pegawai baru berhasil ditambahkan!');
    }

    /**
     * Update employee (User) details.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'nip_atau_id' => 'nullable|string|max:50|unique:md_user,nip_atau_id,' . $id,
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nip_atau_id.unique' => 'NIP / ID sudah terdaftar.',
        ]);

        $user->update([
            'nama' => $request->input('nama'),
            'nip_atau_id' => $request->input('nip_atau_id'),
        ]);

        return redirect()->route('admin.dashboard')->with('success_swal', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Delete employee (User).
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // automatically cascades absensi deletion due to DB schema constraint

        return redirect()->route('admin.dashboard')->with('success_swal', 'Pegawai berhasil dihapus!');
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
