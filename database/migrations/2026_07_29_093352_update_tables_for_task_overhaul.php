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
        Schema::table('md_project_modules', function (Blueprint $table) {
            if (! Schema::hasColumn('md_project_modules', 'bobot')) {
                $table->decimal('bobot', 5, 2)->default(0)->after('status');
            }
        });

        Schema::table('md_project_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('md_project_tasks', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('module_id')->constrained('md_user')->nullOnDelete();
            }
            if (! Schema::hasColumn('md_project_tasks', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('status');
            }
            if (! Schema::hasColumn('md_project_tasks', 'file_lampiran')) {
                $table->string('file_lampiran', 255)->nullable()->after('catatan_revisi');
            }
            if (! Schema::hasColumn('md_project_tasks', 'laporan_kerja')) {
                $table->text('laporan_kerja')->nullable()->after('file_lampiran');
            }
            if (! Schema::hasColumn('md_project_tasks', 'tanggal_selesai_kerja')) {
                $table->timestamp('tanggal_selesai_kerja')->nullable()->after('laporan_kerja');
            }
        });

        // Set default value of status column to 'belum_dikerjakan'
        DB::statement("ALTER TABLE md_project_tasks MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'belum_dikerjakan'");

        // Update existing tasks to have status 'belum_dikerjakan'
        DB::table('md_project_tasks')->where('status', 'open')->update(['status' => 'belum_dikerjakan']);

        if (! Schema::hasTable('md_activity_logs')) {
            Schema::create('md_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('md_user')->nullOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('md_projects')->onDelete('cascade');
                $table->string('aktivitas', 255);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_activity_logs');

        Schema::table('md_project_tasks', function (Blueprint $table) {
            DB::statement("ALTER TABLE md_project_tasks MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'open'");

            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'user_id',
                'catatan_revisi',
                'file_lampiran',
                'laporan_kerja',
                'tanggal_selesai_kerja',
            ]);
        });

        Schema::table('md_project_modules', function (Blueprint $table) {
            $table->dropColumn('bobot');
        });
    }
};
