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
            if (! Schema::hasColumn('md_absensi', 'foto_kamera')) {
                $table->string('foto_kamera', 255)->nullable()->after('foto');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_absensi', function (Blueprint $table) {
            if (Schema::hasColumn('md_absensi', 'foto_kamera')) {
                $table->dropColumn('foto_kamera');
            }
        });
    }
};
