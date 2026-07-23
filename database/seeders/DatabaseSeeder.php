<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Pengaturan::updateOrCreate(
            ['kunci' => 'pin_admin'],
            ['nilai' => Hash::make('123456')]
        );
    }
}
