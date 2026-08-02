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

        $officialIds = DB::table('md_bidang')
            ->whereIn('nama', $this->officialBidangs)
            ->pluck('id');

        if (Schema::hasTable('md_user') && Schema::hasColumn('md_user', 'bidang_magang')) {
            $userUpdate = ['updated_at' => $now];

            if (Schema::hasColumn('md_user', 'bidang_id')) {
                $userUpdate['bidang_id'] = null;
            }

            if (Schema::hasColumn('md_user', 'bidang_magang')) {
                $userUpdate['bidang_magang'] = null;
            }

            if (Schema::hasColumn('md_user', 'pembimbing_magang_id')) {
                $userUpdate['pembimbing_magang_id'] = null;
            }

            $usersToDetach = DB::table('md_user')
                ->where(function ($query): void {
                    $query->whereNotIn('bidang_magang', $this->officialBidangs)
                        ->orWhereNull('bidang_magang');
                });

            if (Schema::hasColumn('md_user', 'bidang_id')) {
                $usersToDetach->where(function ($query) use ($officialIds): void {
                    $query->whereNull('bidang_id')->orWhereNotIn('bidang_id', $officialIds);
                });
            }

            $usersToDetach->update($userUpdate);
        }

        DB::table('md_bidang')
            ->whereNotIn('nama', $this->officialBidangs)
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
