<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected int $month;

    protected int $year;

    protected int $totalWorkdays;

    protected string $namaBulan;

    protected int $headerRow = 5;

    protected int $dataStartRow = 6;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
        $this->namaBulan = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        $this->totalWorkdays = $this->calculateWorkdays();
    }

    protected function calculateWorkdays(): int
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        $totalWorkdays = 0;
        $tempDate = $startDate->copy();
        $today = Carbon::today();
        $maxCalcDate = $endDate->gt($today) ? $today : $endDate;

        while ($tempDate->lte($maxCalcDate)) {
            if (! $tempDate->isWeekend()) {
                $totalWorkdays++;
            }
            $tempDate->addDay();
        }

        return $totalWorkdays > 0 ? $totalWorkdays : 1;
    }

    public function array(): array
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $users = User::with(['absensi' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }])->orderBy('nama', 'asc')->get();

        $rows = [
            ['REKAPITULASI ABSENSI PEGAWAI'],
            ['Bulan / Tahun', $this->namaBulan . ' ' . $this->year],
            ['Hari Kerja Efektif', $this->totalWorkdays . ' Hari'],
            ['Tanggal Cetak', Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB'],
            ['No', 'Nama Pegawai', 'Email', 'Hadir', 'WFH', 'Sakit', 'Izin', 'Persentase Kehadiran'],
        ];

        $no = 1;
        foreach ($users as $user) {
            $hadir = $user->absensi->where('status', 'hadir')->count();
            $wfh = $user->absensi->where('status', 'wfh')->count();
            $sakit = $user->absensi->where('status', 'sakit')->count();
            $izin = $user->absensi->where('status', 'izin')->count();
            $persentase = round((($hadir + $wfh) / $this->totalWorkdays) * 100, 1) . '%';

            $rows[] = [
                $no++,
                $user->nama,
                $user->email ?? '-',
                $hadir,
                $wfh,
                $sakit,
                $izin,
                $persentase,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max($sheet->getHighestRow(), $this->headerRow);

                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A2:B3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                ]);

                $sheet->getStyle('A5:H' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '6366F1'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                if ($lastRow >= $this->dataStartRow) {
                    $sheet->getStyle('A' . $this->dataStartRow . ':A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . $this->dataStartRow . ':H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    for ($row = $this->dataStartRow; $row <= $lastRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8FAFC'],
                                ],
                            ]);
                        }
                    }
                }

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(5)->setRowHeight(24);
                $sheet->freezePane('A6');
            },
        ];
    }
}
