<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('md_absensi')) {
            return;
        }

        Schema::table('md_absensi', function (Blueprint $table) {
            foreach (['lokasi_akurasi', 'lokasi_masuk_akurasi', 'lokasi_pulang_akurasi'] as $column) {
                if (Schema::hasColumn('md_absensi', $column)) {
                    $table->decimal($column, 12, 2)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_absensi')) {
            return;
        }

        Schema::table('md_absensi', function (Blueprint $table) {
            foreach (['lokasi_akurasi', 'lokasi_masuk_akurasi', 'lokasi_pulang_akurasi'] as $column) {
                if (Schema::hasColumn('md_absensi', $column)) {
                    $table->decimal($column, 8, 2)->nullable()->change();
                }
            }
        });
    }
};
