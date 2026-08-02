<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $adminAccounts = [
        [
            'nama' => 'Admin Bidang Pengelolaan Informasi dan Komunikasi Publik',
            'username' => 'admin.pikp',
            'email' => 'admin.pikp@example.test',
            'password' => 'K7mQ2vLp',
            'bidang' => 'Bidang Pengelolaan Informasi dan Komunikasi Publik',
        ],
        [
            'nama' => 'Admin Bidang Aplikasi Informatika',
            'username' => 'admin.aplikasi',
            'email' => 'admin.aplikasi@example.test',
            'password' => 'R9xT4nBa',
            'bidang' => 'Bidang Aplikasi Informatika',
        ],
        [
            'nama' => 'Admin Bidang Infrastruktur Teknologi',
            'username' => 'admin.infrastruktur',
            'email' => 'admin.infrastruktur@example.test',
            'password' => 'P6hZ8cWu',
            'bidang' => 'Bidang Infrastruktur Teknologi',
        ],
        [
            'nama' => 'Admin Bidang Persandian dan Statistik',
            'username' => 'admin.persandian',
            'email' => 'admin.persandian@example.test',
            'password' => 'V3sL7qNd',
            'bidang' => 'Bidang Persandian dan Statistik',
        ],
        [
            'nama' => 'Admin Kepala UPT Radio dan Televisi',
            'username' => 'admin.upt-rtv',
            'email' => 'admin.upt-rtv@example.test',
            'password' => 'M8rY5pXe',
            'bidang' => 'Kepala UPT Radio dan Televisi',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('md_user') || ! Schema::hasTable('md_bidang')) {
            return;
        }

        $now = now();

        foreach ($this->adminAccounts as $account) {
            $bidangId = DB::table('md_bidang')->where('nama', $account['bidang'])->value('id');
            if (! $bidangId) {
                continue;
            }

            DB::table('md_user')->updateOrInsert(
                ['username' => $account['username']],
                [
                    'nama' => $account['nama'],
                    'email' => $account['email'],
                    'password' => Hash::make($account['password']),
                    'bidang_id' => $bidangId,
                    'bidang_magang' => $account['bidang'],
                    'role' => 'admin',
                    'status_akun' => 'aktif',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_user')) {
            return;
        }

        DB::table('md_user')
            ->whereIn('username', collect($this->adminAccounts)->pluck('username')->all())
            ->delete();
    }
};
