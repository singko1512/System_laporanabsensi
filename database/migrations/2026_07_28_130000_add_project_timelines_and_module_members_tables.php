<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('md_project_timelines')) {
            Schema::create('md_project_timelines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
                $table->string('nama', 150);
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->string('status', 30)->default('belum_dimulai');
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();

                $table->index(['project_id', 'urutan']);
                $table->index(['status', 'tanggal_mulai', 'tanggal_selesai']);
            });
        }

        if (Schema::hasTable('md_project_modules')) {
            Schema::table('md_project_modules', function (Blueprint $table) {
                if (! Schema::hasColumn('md_project_modules', 'timeline_id')) {
                    $table->foreignId('timeline_id')->nullable()->after('project_id')->constrained('md_project_timelines')->nullOnDelete();
                }

                if (! Schema::hasColumn('md_project_modules', 'progress')) {
                    $table->decimal('progress', 5, 2)->default(0)->after('deskripsi');
                }

                if (! Schema::hasColumn('md_project_modules', 'status')) {
                    $table->string('status', 30)->default('belum_dimulai')->after('progress');
                }
            });

            if (! $this->indexExists('md_project_modules', 'md_project_modules_timeline_id_urutan_index')) {
                Schema::table('md_project_modules', function (Blueprint $table) {
                    $table->index(['timeline_id', 'urutan']);
                });
            }
        }

        if (! Schema::hasTable('md_project_module_members')) {
            Schema::create('md_project_module_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('md_project_modules')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['module_id', 'user_id']);
                $table->index(['user_id', 'module_id']);
            });
        }

        $this->backfillDefaultTimelines();
    }

    public function down(): void
    {
        Schema::dropIfExists('md_project_module_members');

        if (Schema::hasTable('md_project_modules')) {
            Schema::table('md_project_modules', function (Blueprint $table) {
                if (Schema::hasColumn('md_project_modules', 'timeline_id')) {
                    $table->dropConstrainedForeignId('timeline_id');
                }

                foreach (['progress', 'status'] as $column) {
                    if (Schema::hasColumn('md_project_modules', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('md_project_timelines');
    }

    private function backfillDefaultTimelines(): void
    {
        if (! Schema::hasTable('md_projects') || ! Schema::hasTable('md_project_timelines')) {
            return;
        }

        DB::table('md_projects')
            ->orderBy('id')
            ->get()
            ->each(function ($project): void {
                $timelineId = DB::table('md_project_timelines')
                    ->where('project_id', $project->id)
                    ->where('nama', 'Timeline Utama')
                    ->value('id');

                if (! $timelineId) {
                    $timelineId = DB::table('md_project_timelines')->insertGetId([
                        'project_id' => $project->id,
                        'nama' => 'Timeline Utama',
                        'tanggal_mulai' => $project->tanggal_mulai,
                        'tanggal_selesai' => $project->tanggal_selesai,
                        'status' => 'berjalan',
                        'urutan' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (Schema::hasTable('md_project_modules') && Schema::hasColumn('md_project_modules', 'timeline_id')) {
                    DB::table('md_project_modules')
                        ->where('project_id', $project->id)
                        ->whereNull('timeline_id')
                        ->update([
                            'timeline_id' => $timelineId,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $database = DB::getDatabaseName();

            return DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        } catch (Throwable) {
            return false;
        }
    }
};
