<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Absensi;
use App\Models\Pengaturan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin PIN (default: '123456' hashed)
        Pengaturan::updateOrCreate(
            ['kunci' => 'pin_admin'],
            ['nilai' => Hash::make('123456')]
        );

        // 2. Seed Employee Users
        $employees = [
            ['nama' => 'Ahmad Fauzi', 'nip_atau_id' => '19920815201801'],
            ['nama' => 'Budi Santoso', 'nip_atau_id' => '19890402201503'],
            ['nama' => 'Citra Lestari', 'nip_atau_id' => '19950711202012'],
            ['nama' => 'Dewi Sartika', 'nip_atau_id' => '19930225201908'],
            ['nama' => 'Eko Prasetyo', 'nip_atau_id' => '19901103201604'],
            ['nama' => 'Fitri Handayani', 'nip_atau_id' => '19970519202202'],
        ];

        $userInstances = [];
        foreach ($employees as $emp) {
            $userInstances[] = User::create($emp);
        }

        // 3. Seed historical attendance for the last 14 days (excluding weekends)
        $statuses = ['hadir', 'wfh', 'sakit', 'izin'];
        $hadirTasks = [
            'Menyelesaikan modul frontend dashboard admin',
            'Rapat tim mingguan dan sinkronisasi database server',
            'Melakukan bug fixing pada sistem pembayaran dan checkout',
            'Optimalisasi performa query SQL dan indexing tabel database',
            'Membuat dokumentasi API endpoint untuk integrasi sistem mobile',
            'Maintenance berkala server staging dan backup data harian',
            'Review merge request developer magang dan diskusi teknis',
            'Mengatur konfigurasi keamanan web firewall dan SSL renewal'
        ];
        $wfhTasks = [
            'WFH: Menyusun laporan bulanan divisi IT',
            'WFH: Melakukan riset implementasi design system baru',
            'WFH: Melanjutkan penulisan unit test untuk modul otentikasi',
            'WFH: Koordinasi online via Zoom mengenai update progress sprint 2',
            'WFH: Analisis performa web Core Web Vitals dan refactoring CSS'
        ];
        $sakitReasons = [
            'Sakit demam dan flu, berobat ke klinik terdekat',
            'Sakit radang tenggorokan berat, disarankan istirahat oleh dokter',
            'Mengalami cedera otot punggung ringan saat olahraga',
            'Sakit migrain berkepanjangan sejak tadi pagi'
        ];
        $izinReasons = [
            'Izin mengurus perpanjangan STNK kendaraan bermotor',
            'Izin menghadiri upacara pernikahan saudara kandung',
            'Izin mengantar orang tua kontrol rutin ke rumah sakit',
            'Izin keperluan mendesak di kantor kelurahan'
        ];

        // Seed from 18 days ago up to today
        for ($i = 18; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($userInstances as $user) {
                // Randomize presence status
                // 75% hadir, 15% wfh, 5% sakit, 5% izin
                $rand = rand(1, 100);
                if ($rand <= 75) {
                    $status = 'hadir';
                    $laporan = $hadirTasks[array_rand($hadirTasks)];
                    $foto = 'dummy_hadir.jpg'; // We can use mock paths
                } elseif ($rand <= 90) {
                    $status = 'wfh';
                    $laporan = $wfhTasks[array_rand($wfhTasks)];
                    $foto = 'dummy_wfh.jpg';
                } elseif ($rand <= 95) {
                    $status = 'sakit';
                    $laporan = $sakitReasons[array_rand($sakitReasons)];
                    $foto = 'dummy_suratsakit.jpg';
                } else {
                    $status = 'izin';
                    $laporan = $izinReasons[array_rand($izinReasons)];
                    $foto = rand(0, 1) ? 'dummy_izin.jpg' : null; // photo is optional for izin
                }

                // Don't log future or partial current day randomly
                if ($date->isToday() && rand(1, 100) > 60) {
                    continue; // some users haven't clocked in today yet
                }

                Absensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $date->format('Y-m-d'),
                    'status' => $status,
                    'foto' => $foto,
                    'laporan' => $laporan,
                    'created_at' => $date->copy()->setTime(rand(7, 9), rand(0, 59)),
                    'updated_at' => $date->copy()->setTime(rand(16, 17), rand(0, 59)),
                ]);
            }
        }
    }
}
