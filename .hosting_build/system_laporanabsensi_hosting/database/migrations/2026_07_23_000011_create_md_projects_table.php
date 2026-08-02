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
        Schema::create('md_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->string('nama', 150);
            $table->text('kebutuhan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('status_id')->nullable()->constrained('md_master_data')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_projects');
    }
};
