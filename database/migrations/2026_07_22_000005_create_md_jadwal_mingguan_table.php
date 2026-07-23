<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('md_jadwal_mingguan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('md_user')->onDelete('cascade');
            $table->enum('senin', ['wfo', 'wfh'])->default('wfo');
            $table->enum('selasa', ['wfo', 'wfh'])->default('wfo');
            $table->enum('rabu', ['wfo', 'wfh'])->default('wfo');
            $table->enum('kamis', ['wfo', 'wfh'])->default('wfo');
            $table->enum('jumat', ['wfo', 'wfh'])->default('wfo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_jadwal_mingguan');
    }
};
