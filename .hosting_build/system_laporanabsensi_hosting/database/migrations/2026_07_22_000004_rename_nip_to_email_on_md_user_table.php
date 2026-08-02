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
        if (Schema::hasColumn('md_user', 'nip_atau_id') && ! Schema::hasColumn('md_user', 'email')) {
            Schema::table('md_user', function (Blueprint $table) {
                $table->renameColumn('nip_atau_id', 'email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('md_user', 'email') && ! Schema::hasColumn('md_user', 'nip_atau_id')) {
            Schema::table('md_user', function (Blueprint $table) {
                $table->renameColumn('email', 'nip_atau_id');
            });
        }
    }
};
