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
        Schema::table('md_project_notes', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('project_id')
                ->constrained('md_user')
                ->nullOnDelete();

            $table->index(['project_id', 'user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_project_notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['project_id', 'user_id', 'tanggal']);
            $table->dropColumn('user_id');
        });
    }
};
