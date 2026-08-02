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
        Schema::create('md_project_day_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['project_id', 'user_id', 'tanggal'], 'project_day_user_unique');
            $table->index(['project_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_project_day_assignments');
    }
};
