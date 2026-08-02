<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $adminPasswords = [
        'admin.pikp' => 'K7mQ2vLp',
        'admin.aplikasi' => 'R9xT4nBa',
        'admin.infrastruktur' => 'P6hZ8cWu',
        'admin.persandian' => 'V3sL7qNd',
        'admin.upt-rtv' => 'M8rY5pXe',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('md_user')) {
            return;
        }

        foreach ($this->adminPasswords as $username => $password) {
            DB::table('md_user')
                ->where('username', $username)
                ->where('role', 'admin')
                ->update([
                    'password' => Hash::make($password),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_user')) {
            return;
        }

        foreach (array_keys($this->adminPasswords) as $username) {
            DB::table('md_user')
                ->where('username', $username)
                ->where('role', 'admin')
                ->update([
                    'password' => Hash::make('admin123'),
                    'updated_at' => now(),
                ]);
        }
    }
};
