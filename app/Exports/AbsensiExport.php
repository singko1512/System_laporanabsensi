<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $month;
    protected $year;
    protected $totalWorkdays;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

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

        $this->totalWorkdays = $totalWorkdays > 0 ? $totalWorkdays : 1;
    }

    public function collection()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        return User::with(['absensi' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }])->orderBy('nama', 'asc')->get();
    }

    public function headings(): array
    {
        $namaBulan = Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F');
        return [
            ['REKAPITULASI ABSENSI PEGAWAI'],
            ['Bulan: ' . $namaBulan . ' ' . $this->year],
            ['Total Hari Kerja: ' . $this->totalWorkdays . ' Hari'],
            [], // Empty row
            ['No', 'Nama Pegawai', 'NIP / ID', 'Hadir', 'WFH', 'Sakit', 'Izin', 'Persentase Kehadiran']
        ];
    }

    /**
     * @var User $user
     */
    public function map($user): array
    {
        static $no = 1;

        $hadir = $user->absensi->where('status', 'hadir')->count();
        $wfh = $user->absensi->where('status', 'wfh')->count();
        $sakit = $user->absensi->where('status', 'sakit')->count();
        $izin = $user->absensi->where('status', 'izin')->count();

        $attended = $hadir + $wfh;
        $persentase = round(($attended / $this->totalWorkdays) * 100, 1) . '%';

        return [
            $no++,
            $user->nama,
            $user->nip_atau_id ?? '-',
            $hadir,
            $wfh,
            $sakit,
            $izin,
            $persentase
        ];
    }
}
