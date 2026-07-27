<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'pin_admin'],
            [
                'nilai' => Hash::make('180909'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'pin_admin'],
            [
                'nilai' => Hash::make('123456'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};
