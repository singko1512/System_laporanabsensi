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
        $this->convertAbsensiStatus();
        $this->convertJadwalStatus();
        $this->convertProjectStatus();
        $this->convertProjectNoteKategori();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->restoreAbsensiStatus();
        $this->restoreJadwalStatus();
        $this->restoreProjectStatus();
        $this->restoreProjectNoteKategori();
    }

    private function masterIds(string $jenis): array
    {
        return DB::table('md_master_data')
            ->where('jenis', $jenis)
            ->pluck('id', 'kode')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function masterKodeById(string $jenis): array
    {
        return DB::table('md_master_data')
            ->where('jenis', $jenis)
            ->pluck('kode', 'id')
            ->all();
    }

    private function convertAbsensiStatus(): void
    {
        if (Schema::hasTable('md_absensi') && ! Schema::hasColumn('md_absensi', 'status_id')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                $table->foreignId('status_id')->nullable()->after('tanggal')->constrained('md_master_data')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('md_absensi', 'status')) {
            foreach ($this->masterIds('absensi_status') as $kode => $id) {
                DB::table('md_absensi')->where('status', $kode)->update(['status_id' => $id]);
            }

            Schema::table('md_absensi', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    private function convertJadwalStatus(): void
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        foreach ($days as $day) {
            $column = $day . '_status_id';

            if (Schema::hasTable('md_jadwal_mingguan') && ! Schema::hasColumn('md_jadwal_mingguan', $column)) {
                Schema::table('md_jadwal_mingguan', function (Blueprint $table) use ($column, $day) {
                    $table->foreignId($column)->nullable()->after($day)->constrained('md_master_data')->nullOnDelete();
                });
            }
        }

        $ids = $this->masterIds('jadwal_status');
        foreach ($days as $day) {
            if (! Schema::hasColumn('md_jadwal_mingguan', $day)) {
                continue;
            }

            foreach ($ids as $kode => $id) {
                DB::table('md_jadwal_mingguan')->where($day, $kode)->update([$day . '_status_id' => $id]);
            }
        }

        $oldColumns = array_values(array_filter($days, fn ($day) => Schema::hasColumn('md_jadwal_mingguan', $day)));
        if ($oldColumns !== []) {
            Schema::table('md_jadwal_mingguan', function (Blueprint $table) use ($oldColumns) {
                $table->dropColumn($oldColumns);
            });
        }
    }

    private function convertProjectStatus(): void
    {
        if (Schema::hasTable('md_projects') && ! Schema::hasColumn('md_projects', 'status_id')) {
            Schema::table('md_projects', function (Blueprint $table) {
                $table->foreignId('status_id')->nullable()->after('tanggal_selesai')->constrained('md_master_data')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('md_projects', 'status')) {
            foreach ($this->masterIds('project_status') as $kode => $id) {
                DB::table('md_projects')->where('status', $kode)->update(['status_id' => $id]);
            }

            Schema::table('md_projects', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    private function convertProjectNoteKategori(): void
    {
        if (Schema::hasTable('md_project_notes') && ! Schema::hasColumn('md_project_notes', 'kategori_id')) {
            Schema::table('md_project_notes', function (Blueprint $table) {
                $table->foreignId('kategori_id')->nullable()->after('tanggal')->constrained('md_master_data')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('md_project_notes', 'kategori')) {
            foreach ($this->masterIds('note_kategori') as $kode => $id) {
                DB::table('md_project_notes')->where('kategori', $kode)->update(['kategori_id' => $id]);
            }

            Schema::table('md_project_notes', function (Blueprint $table) {
                $table->dropIndex(['kategori', 'selesai_pada']);
                $table->dropColumn('kategori');
                $table->index(['kategori_id', 'selesai_pada']);
            });
        }
    }

    private function restoreAbsensiStatus(): void
    {
        if (! Schema::hasColumn('md_absensi', 'status')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                $table->string('status', 60)->default('hadir')->after('tanggal');
            });
        }

        foreach ($this->masterKodeById('absensi_status') as $id => $kode) {
            DB::table('md_absensi')->where('status_id', $id)->update(['status' => $kode]);
        }

        if (Schema::hasColumn('md_absensi', 'status_id')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                $table->dropConstrainedForeignId('status_id');
            });
        }
    }

    private function restoreJadwalStatus(): void
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        foreach ($days as $day) {
            if (! Schema::hasColumn('md_jadwal_mingguan', $day)) {
                Schema::table('md_jadwal_mingguan', function (Blueprint $table) use ($day) {
                    $table->string($day, 60)->default($day === 'jumat' ? 'wfh' : 'wfo');
                });
            }
        }

        foreach ($this->masterKodeById('jadwal_status') as $id => $kode) {
            foreach ($days as $day) {
                DB::table('md_jadwal_mingguan')->where($day . '_status_id', $id)->update([$day => $kode]);
            }
        }

        foreach ($days as $day) {
            $column = $day . '_status_id';
            if (Schema::hasColumn('md_jadwal_mingguan', $column)) {
                Schema::table('md_jadwal_mingguan', function (Blueprint $table) use ($column) {
                    $table->dropConstrainedForeignId($column);
                });
            }
        }
    }

    private function restoreProjectStatus(): void
    {
        if (! Schema::hasColumn('md_projects', 'status')) {
            Schema::table('md_projects', function (Blueprint $table) {
                $table->string('status', 60)->default('aktif')->after('tanggal_selesai');
            });
        }

        foreach ($this->masterKodeById('project_status') as $id => $kode) {
            DB::table('md_projects')->where('status_id', $id)->update(['status' => $kode]);
        }

        if (Schema::hasColumn('md_projects', 'status_id')) {
            Schema::table('md_projects', function (Blueprint $table) {
                $table->dropConstrainedForeignId('status_id');
            });
        }
    }

    private function restoreProjectNoteKategori(): void
    {
        if (! Schema::hasColumn('md_project_notes', 'kategori')) {
            Schema::table('md_project_notes', function (Blueprint $table) {
                $table->string('kategori', 60)->default('sedang')->after('tanggal');
            });
        }

        foreach ($this->masterKodeById('note_kategori') as $id => $kode) {
            DB::table('md_project_notes')->where('kategori_id', $id)->update(['kategori' => $kode]);
        }

        if (Schema::hasColumn('md_project_notes', 'kategori_id')) {
            Schema::table('md_project_notes', function (Blueprint $table) {
                $table->dropIndex(['kategori_id', 'selesai_pada']);
                $table->dropConstrainedForeignId('kategori_id');
                $table->index(['kategori', 'selesai_pada']);
            });
        }
    }
};
