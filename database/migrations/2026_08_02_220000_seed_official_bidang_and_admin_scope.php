<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $officialBidangs = [
        'Bidang Pengelolaan Informasi dan Komunikasi Publik',
        'Bidang Aplikasi Informatika',
        'Bidang Infrastruktur Teknologi',
        'Bidang Persandian dan Statistik',
        'Kepala UPT Radio dan Televisi',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('md_bidang')) {
            return;
        }

        $now = now();
        foreach ($this->officialBidangs as $nama) {
            DB::table('md_bidang')->updateOrInsert(
                ['nama' => $nama],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        if (! Schema::hasTable('md_user') || ! Schema::hasColumn('md_user', 'bidang_id')) {
            return;
        }

        $aplikasiBidangId = DB::table('md_bidang')->where('nama', 'Bidang Aplikasi Informatika')->value('id');
        if ($aplikasiBidangId) {
            DB::table('md_user')
                ->where('role', 'admin')
                ->where(function ($query): void {
                    $query->where('username', 'admin')->orWhere('email', 'admin@example.test');
                })
                ->update([
                    'bidang_id' => $aplikasiBidangId,
                    'bidang_magang' => 'Bidang Aplikasi Informatika',
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('md_user') && Schema::hasColumn('md_user', 'bidang_id')) {
            DB::table('md_user')
                ->where('role', 'admin')
                ->where(function ($query): void {
                    $query->where('username', 'admin')->orWhere('email', 'admin@example.test');
                })
                ->where('bidang_magang', 'Bidang Aplikasi Informatika')
                ->update([
                    'bidang_id' => null,
                    'bidang_magang' => null,
                    'updated_at' => now(),
                ]);
        }
    }
};
