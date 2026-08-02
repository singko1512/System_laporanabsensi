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
        Schema::create('md_bidang', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->timestamps();
        });

        // Seed from existing user division records
        if (Schema::hasTable('md_user')) {
            $existingBidangs = DB::table('md_user')
                ->whereNotNull('bidang_magang')
                ->where('bidang_magang', '<>', '')
                ->distinct()
                ->pluck('bidang_magang');

            foreach ($existingBidangs as $nama) {
                DB::table('md_bidang')->insertOrIgnore([
                    'nama' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_bidang');
    }
};
