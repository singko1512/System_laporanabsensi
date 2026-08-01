<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('md_pembimbing_magang')) {
            return;
        }

        Schema::table('md_pembimbing_magang', function (Blueprint $table) {
            if (! Schema::hasColumn('md_pembimbing_magang', 'bidang_id')) {
                $table->foreignId('bidang_id')->nullable()->after('id')->constrained('md_bidang')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('md_user') || ! Schema::hasTable('md_bidang')) {
            return;
        }

        DB::table('md_pembimbing_magang')
            ->whereNull('bidang_id')
            ->orderBy('id')
            ->get()
            ->each(function ($pembimbing): void {
                $bidangName = DB::table('md_user')
                    ->where('pembimbing_magang', $pembimbing->nama)
                    ->whereNotNull('bidang_magang')
                    ->where('bidang_magang', '<>', '')
                    ->orderBy('id')
                    ->value('bidang_magang');

                if (! $bidangName) {
                    return;
                }

                $bidangId = DB::table('md_bidang')->where('nama', $bidangName)->value('id');

                if ($bidangId) {
                    DB::table('md_pembimbing_magang')
                        ->where('id', $pembimbing->id)
                        ->update(['bidang_id' => $bidangId]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_pembimbing_magang') || ! Schema::hasColumn('md_pembimbing_magang', 'bidang_id')) {
            return;
        }

        Schema::table('md_pembimbing_magang', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_id');
        });
    }
};
