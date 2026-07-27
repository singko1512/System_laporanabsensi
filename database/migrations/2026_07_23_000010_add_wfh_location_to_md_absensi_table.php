<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('md_absensi', function (Blueprint $table) {
            if (! Schema::hasColumn('md_absensi', 'lokasi_latitude')) {
                $table->decimal('lokasi_latitude', 10, 7)->nullable()->after('foto_kamera');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_longitude')) {
                $table->decimal('lokasi_longitude', 10, 7)->nullable()->after('lokasi_latitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_akurasi')) {
                $table->decimal('lokasi_akurasi', 8, 2)->nullable()->after('lokasi_longitude');
            }

            if (! Schema::hasColumn('md_absensi', 'lokasi_diambil_pada')) {
                $table->timestamp('lokasi_diambil_pada')->nullable()->after('lokasi_akurasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_absensi', function (Blueprint $table) {
            foreach (['lokasi_diambil_pada', 'lokasi_akurasi', 'lokasi_longitude', 'lokasi_latitude'] as $column) {
                if (Schema::hasColumn('md_absensi', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
