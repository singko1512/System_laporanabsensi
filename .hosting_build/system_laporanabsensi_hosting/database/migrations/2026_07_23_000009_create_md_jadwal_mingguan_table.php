<?php

use App\Models\JadwalMingguan;
use App\Models\User;
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
        if (! Schema::hasTable('md_jadwal_mingguan')) {
            Schema::create('md_jadwal_mingguan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
                $table->foreignId('senin_status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
                $table->foreignId('selasa_status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
                $table->foreignId('rabu_status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
                $table->foreignId('kamis_status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
                $table->foreignId('jumat_status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
                $table->timestamps();

                $table->unique('user_id');
            });

            User::query()->each(function (User $user): void {
                $user->jadwalMingguan()->create(JadwalMingguan::defaultSchedule());
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_jadwal_mingguan');
    }
};
