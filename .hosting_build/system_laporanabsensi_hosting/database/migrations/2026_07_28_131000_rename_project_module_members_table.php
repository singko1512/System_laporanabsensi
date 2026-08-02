<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('md_project_module_members') && ! Schema::hasTable('module_members')) {
            Schema::rename('md_project_module_members', 'module_members');
            return;
        }

        if (! Schema::hasTable('module_members')) {
            Schema::create('module_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('md_project_modules')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('md_user')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['module_id', 'user_id']);
                $table->index(['user_id', 'module_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_members') && ! Schema::hasTable('md_project_module_members')) {
            Schema::rename('module_members', 'md_project_module_members');
        }
    }
};
