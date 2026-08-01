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
        if (Schema::hasTable('md_user') && ! Schema::hasColumn('md_user', 'role')) {
            Schema::table('md_user', function (Blueprint $table) {
                $table->string('role', 20)->default('user')->after('tanggal_selesai_magang');
            });
        }

        if (Schema::hasTable('md_user') && Schema::hasColumn('md_user', 'role')) {
            DB::table('md_user')
                ->whereNull('role')
                ->orWhere('role', '')
                ->update(['role' => 'user']);
        }

        if (! Schema::hasTable('md_pengaturan')) {
            return;
        }

        $now = now();
        $adminPin = DB::table('md_pengaturan')->where('kunci', 'pin_admin')->first();
        $superadminHash = ($adminPin && Hash::check('180909', $adminPin->nilai))
            ? $adminPin->nilai
            : Hash::make('180909');

        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'pin_superadmin'],
            ['nilai' => $superadminHash, 'created_at' => $now, 'updated_at' => $now]
        );

        if ($adminPin && Hash::check('180909', $adminPin->nilai)) {
            DB::table('md_pengaturan')
                ->where('kunci', 'pin_admin')
                ->update(['nilai' => Hash::make('123456'), 'updated_at' => $now]);
        } elseif (! $adminPin) {
            DB::table('md_pengaturan')->insert([
                'kunci' => 'pin_admin',
                'nilai' => Hash::make('123456'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('md_user') && Schema::hasColumn('md_user', 'role')) {
            Schema::table('md_user', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        if (Schema::hasTable('md_pengaturan')) {
            DB::table('md_pengaturan')->where('kunci', 'pin_superadmin')->delete();
        }
    }
};
