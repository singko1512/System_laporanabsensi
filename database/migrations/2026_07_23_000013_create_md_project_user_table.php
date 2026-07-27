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
        Schema::create('md_project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        DB::table('md_projects')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->get()
            ->each(function ($project): void {
                DB::table('md_project_user')->insertOrIgnore([
                    'project_id' => $project->id,
                    'user_id' => $project->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_project_user');
    }
};
