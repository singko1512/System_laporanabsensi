<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('md_absensi')) {
            return;
        }

        Schema::table('md_absensi', function (Blueprint $table) {
            if (! Schema::hasColumn('md_absensi', 'lokasi_masuk_latitude')) {
                $table->decimal('lokasi_masuk_latitude', 10, 7)->nullable()->after('lokasi_diambil_pada');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_masuk_longitude')) {
                $table->decimal('lokasi_masuk_longitude', 10, 7)->nullable()->after('lokasi_masuk_latitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_masuk_akurasi')) {
                $table->decimal('lokasi_masuk_akurasi', 12, 2)->nullable()->after('lokasi_masuk_longitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_masuk_diambil_pada')) {
                $table->timestamp('lokasi_masuk_diambil_pada')->nullable()->after('lokasi_masuk_akurasi');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_pulang_latitude')) {
                $table->decimal('lokasi_pulang_latitude', 10, 7)->nullable()->after('lokasi_masuk_diambil_pada');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_pulang_longitude')) {
                $table->decimal('lokasi_pulang_longitude', 10, 7)->nullable()->after('lokasi_pulang_latitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_pulang_akurasi')) {
                $table->decimal('lokasi_pulang_akurasi', 12, 2)->nullable()->after('lokasi_pulang_longitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_pulang_diambil_pada')) {
                $table->timestamp('lokasi_pulang_diambil_pada')->nullable()->after('lokasi_pulang_akurasi');
            }
        });

        DB::table('md_absensi')
            ->whereNotNull('lokasi_latitude')
            ->whereNull('lokasi_masuk_latitude')
            ->update([
                'lokasi_masuk_latitude' => DB::raw('lokasi_latitude'),
                'lokasi_masuk_longitude' => DB::raw('lokasi_longitude'),
                'lokasi_masuk_akurasi' => DB::raw('lokasi_akurasi'),
                'lokasi_masuk_diambil_pada' => DB::raw('lokasi_diambil_pada'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_absensi')) {
            return;
        }

        Schema::table('md_absensi', function (Blueprint $table) {
            foreach ([
                'lokasi_pulang_diambil_pada',
                'lokasi_pulang_akurasi',
                'lokasi_pulang_longitude',
                'lokasi_pulang_latitude',
                'lokasi_masuk_diambil_pada',
                'lokasi_masuk_akurasi',
                'lokasi_masuk_longitude',
                'lokasi_masuk_latitude',
            ] as $column) {
                if (Schema::hasColumn('md_absensi', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
