<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('md_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 60);
            $table->string('kode', 60);
            $table->string('nama', 100);
            $table->string('warna', 20)->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['jenis', 'kode']);
            $table->index(['jenis', 'is_active', 'urutan']);
        });

        $now = now();
        $items = [
            ['absensi_status', 'hadir', 'Hadir', '#10b981', 1],
            ['absensi_status', 'wfh', 'WFH', '#6366f1', 2],
            ['absensi_status', 'sakit', 'Sakit', '#ef4444', 3],
            ['absensi_status', 'izin', 'Izin', '#f59e0b', 4],
            ['jadwal_status', 'wfo', 'WFO', '#10b981', 1],
            ['jadwal_status', 'wfh', 'WFH', '#6366f1', 2],
            ['project_status', 'aktif', 'Aktif', '#10b981', 1],
            ['project_status', 'selesai', 'Selesai', '#64748b', 2],
            ['note_kategori', 'rendah', 'Rendah', '#10b981', 1],
            ['note_kategori', 'sedang', 'Sedang', '#f59e0b', 2],
            ['note_kategori', 'tinggi', 'Tinggi', '#ef4444', 3],
        ];

        foreach ($items as [$jenis, $kode, $nama, $warna, $urutan]) {
            DB::table('md_master_data')->insert([
                'jenis' => $jenis,
                'kode' => $kode,
                'nama' => $nama,
                'warna' => $warna,
                'urutan' => $urutan,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_master_data');
    }
};
