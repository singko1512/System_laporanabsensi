<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const MAX_PARTICIPANTS_PER_BIDANG = 20;

    private array $bidangConfigs = [
        'pikp' => 'Bidang Pengelolaan Informasi dan Komunikasi Publik',
        'aplikasi' => 'Bidang Aplikasi Informatika',
        'infrastruktur' => 'Bidang Infrastruktur Teknologi',
        'persandian' => 'Bidang Persandian dan Statistik',
        'upt-rtv' => 'Kepala UPT Radio dan Televisi',
    ];

    private array $firstNames = [
        'Aditya', 'Alya', 'Bagas', 'Citra', 'Dimas',
        'Elisa', 'Farhan', 'Gita', 'Hafiz', 'Intan',
        'Jihan', 'Kirana', 'Lukman', 'Maya', 'Naufal',
        'Olivia', 'Putra', 'Raisa', 'Satria', 'Tiara',
    ];

    private array $lastNames = [
        'Pratama', 'Maharani', 'Wijaya', 'Lestari', 'Saputra',
        'Anjani', 'Ramadhan', 'Putri', 'Maulana', 'Kartika',
        'Santoso', 'Pertiwi', 'Nugroho', 'Amalia', 'Firmansyah',
        'Permata', 'Hidayat', 'Utami', 'Kusuma', 'Azzahra',
    ];

    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (! Schema::hasTable('md_user') || ! Schema::hasTable('md_bidang')) {
            return;
        }

        foreach ($this->bidangConfigs as $code => $bidangName) {
            DB::table('md_bidang')->updateOrInsert(
                ['nama' => $bidangName],
                ['updated_at' => now(), 'created_at' => now()]
            );

            $bidangId = DB::table('md_bidang')->where('nama', $bidangName)->value('id');
            if (! $bidangId) {
                continue;
            }

            $currentCount = DB::table('md_user')
                ->where('role', 'user')
                ->where('bidang_id', $bidangId)
                ->count();

            foreach ($this->participantRows($code, $bidangName) as $row) {
                $exists = DB::table('md_user')->where('email', $row['email'])->exists();

                if (! $exists && $currentCount >= self::MAX_PARTICIPANTS_PER_BIDANG) {
                    continue;
                }

                $payload = array_merge($row, [
                    'username' => null,
                    'password' => Hash::make('password123'),
                    'bidang_id' => $bidangId,
                    'bidang_magang' => $bidangName,
                    'role' => 'user',
                    'status_akun' => 'aktif',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);

                if (Schema::hasColumn('md_user', 'nip_atau_id')) {
                    $payload['nip_atau_id'] = sprintf('INT-%s-%03d', Str::upper(Str::replace('-', '', $code)), $row['number']);
                }

                unset($payload['number']);

                DB::table('md_user')->updateOrInsert(['email' => $row['email']], $payload);

                if (! $exists) {
                    $currentCount++;
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_user')) {
            return;
        }

        DB::table('md_user')
            ->whereIn('email', $this->participantEmails())
            ->delete();
    }

    private function participantRows(string $code, string $bidangName): array
    {
        $rows = [];
        $bidangIndex = array_search($bidangName, array_values($this->bidangConfigs), true) ?: 0;

        for ($i = 1; $i <= self::MAX_PARTICIPANTS_PER_BIDANG; $i++) {
            $firstName = $this->firstNames[($i - 1 + ($bidangIndex * 3)) % count($this->firstNames)];
            $lastName = $this->lastNames[($i - 1 + ($bidangIndex * 5)) % count($this->lastNames)];
            $username = sprintf('peserta.%s.%02d', $code, $i);

            $rows[] = [
                'nama' => $firstName.' '.$lastName,
                'email' => $username.'@example.test',
                'number' => $i,
                'tanggal_mulai_magang' => '2026-08-01',
                'tanggal_selesai_magang' => '2026-12-31',
            ];
        }

        return $rows;
    }

    private function participantEmails(): array
    {
        $emails = [];

        foreach (array_keys($this->bidangConfigs) as $code) {
            for ($i = 1; $i <= self::MAX_PARTICIPANTS_PER_BIDANG; $i++) {
                $emails[] = sprintf('peserta.%s.%02d@example.test', $code, $i);
            }
        }

        return $emails;
    }
};
