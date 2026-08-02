<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('md_project_modules', function (Blueprint $table) {
            if (! Schema::hasColumn('md_project_modules', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('md_project_modules', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('md_project_modules', function (Blueprint $table) {
            foreach (['tanggal_mulai', 'tanggal_selesai'] as $column) {
                if (Schema::hasColumn('md_project_modules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
