<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('md_user', function (Blueprint $table) {
            if (! Schema::hasColumn('md_user', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('nama');
            }

            if (! Schema::hasColumn('md_user', 'password')) {
                $table->string('password')->nullable()->after('email');
            }

            if (! Schema::hasColumn('md_user', 'status_akun')) {
                $table->string('status_akun', 20)->default('aktif')->after('role');
            }

            if (! Schema::hasColumn('md_user', 'remember_token')) {
                $table->rememberToken()->after('status_akun');
            }
        });

        if (Schema::hasTable('md_absensi')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                if (! Schema::hasColumn('md_absensi', 'jam_masuk')) {
                    $table->time('jam_masuk')->nullable()->after('tanggal');
                }

                if (! Schema::hasColumn('md_absensi', 'jam_pulang')) {
                    $table->time('jam_pulang')->nullable()->after('jam_masuk');
                }

                if (! Schema::hasColumn('md_absensi', 'status_masuk_id')) {
                    $table->foreignId('status_masuk_id')->nullable()->after('status_id')->constrained('md_master_data')->nullOnDelete();
                }

                if (! Schema::hasColumn('md_absensi', 'status_pulang_id')) {
                    $table->foreignId('status_pulang_id')->nullable()->after('status_masuk_id')->constrained('md_master_data')->nullOnDelete();
                }

                if (! Schema::hasColumn('md_absensi', 'foto_masuk')) {
                    $table->string('foto_masuk', 255)->nullable()->after('foto_kamera');
                }

                if (! Schema::hasColumn('md_absensi', 'foto_pulang')) {
                    $table->string('foto_pulang', 255)->nullable()->after('foto_masuk');
                }

            });

            DB::table('md_absensi')
                ->whereNull('jam_masuk')
                ->update([
                    'jam_masuk' => DB::raw('TIME(created_at)'),
                    'foto_masuk' => DB::raw('foto_kamera'),
                    'status_masuk_id' => DB::raw('status_id'),
                ]);
        }

        Schema::create('md_project_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'urutan']);
        });

        Schema::create('md_project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('md_project_modules')->nullOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedInteger('join_window_minutes')->default(5);
            $table->timestamp('join_dibuka_pada')->nullable();
            $table->timestamp('join_ditutup_pada')->nullable();
            $table->string('status', 30)->default('open');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'module_id']);
            $table->index(['status', 'join_ditutup_pada']);
        });

        if (Schema::hasTable('md_absensi') && ! Schema::hasColumn('md_absensi', 'task_id')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                $table->foreignId('task_id')->nullable()->after('user_id')->constrained('md_project_tasks')->nullOnDelete();
            });
        }

        Schema::create('md_project_task_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('md_project_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->timestamp('joined_at')->nullable();
            $table->decimal('contribution_percentage', 5, 2)->default(0);
            $table->string('status', 30)->default('joined');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('md_user')->nullOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('md_work_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_participant_id')->constrained('md_project_task_participants')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('md_project_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->date('tanggal');
            $table->text('isi_laporan');
            $table->string('lampiran', 255)->nullable();
            $table->string('status', 30)->default('submitted');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('md_user')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'user_id', 'status']);
        });

        Schema::create('md_project_note_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('md_work_submissions')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('md_project_tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->string('tipe', 30)->default('comment');
            $table->text('isi');
            $table->string('lampiran', 255)->nullable();
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
        });

        $this->seedDefaultAccounts();
    }

    public function down(): void
    {
        if (Schema::hasTable('md_absensi')) {
            Schema::table('md_absensi', function (Blueprint $table) {
                if (Schema::hasColumn('md_absensi', 'task_id')) {
                    $table->dropConstrainedForeignId('task_id');
                }

                foreach (['status_masuk_id', 'status_pulang_id'] as $column) {
                    if (Schema::hasColumn('md_absensi', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }

                foreach (['jam_masuk', 'jam_pulang', 'foto_masuk', 'foto_pulang'] as $column) {
                    if (Schema::hasColumn('md_absensi', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('md_project_note_replies');
        Schema::dropIfExists('md_work_submissions');
        Schema::dropIfExists('md_project_task_participants');
        Schema::dropIfExists('md_project_tasks');
        Schema::dropIfExists('md_project_modules');

        Schema::table('md_user', function (Blueprint $table) {
            foreach (['username', 'password', 'status_akun', 'remember_token'] as $column) {
                if (Schema::hasColumn('md_user', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedDefaultAccounts(): void
    {
        $now = now();

        DB::table('md_user')->updateOrInsert(
            ['username' => 'admin'],
            [
                'nama' => 'Admin',
                'email' => 'admin@example.test',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status_akun' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('md_user')->updateOrInsert(
            ['username' => 'superadmin'],
            [
                'nama' => 'Super Admin',
                'email' => 'superadmin@example.test',
                'password' => Hash::make('superadmin123'),
                'role' => 'superadmin',
                'status_akun' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('md_user')
            ->where('role', 'user')
            ->whereNull('password')
            ->orderBy('id')
            ->get()
            ->each(function ($user): void {
                $baseUsername = $user->username ?: strtolower(preg_replace('/[^a-z0-9]+/i', '.', $user->nama));
                $baseUsername = trim($baseUsername, '.') ?: 'peserta'.$user->id;
                $username = $baseUsername;
                $counter = 1;

                while (DB::table('md_user')->where('username', $username)->where('id', '<>', $user->id)->exists()) {
                    $username = $baseUsername.$counter;
                    $counter++;
                }

                DB::table('md_user')->where('id', $user->id)->update([
                    'username' => $username,
                    'password' => Hash::make('password123'),
                    'status_akun' => 'aktif',
                    'updated_at' => now(),
                ]);
            });
    }
};
