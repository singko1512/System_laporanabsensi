<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('md_pengaturan')) {
            return;
        }

        $now = now();
        $credentials = [
            'admin_login_username' => 'admin',
            'admin_login_password' => Hash::make('admin123'),
            'superadmin_login_username' => 'superadmin',
            'superadmin_login_password' => Hash::make('superadmin123'),
        ];

        foreach ($credentials as $key => $value) {
            DB::table('md_pengaturan')->updateOrInsert(
                ['kunci' => $key],
                ['nilai' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_pengaturan')) {
            return;
        }

        DB::table('md_pengaturan')
            ->whereIn('kunci', [
                'admin_login_username',
                'admin_login_password',
                'superadmin_login_username',
                'superadmin_login_password',
            ])
            ->delete();
    }
};
