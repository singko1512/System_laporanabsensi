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
        Schema::table('md_user', function (Blueprint $table) {
            if (! Schema::hasColumn('md_user', 'pembimbing_magang')) {
                $table->string('pembimbing_magang', 100)->nullable()->after('email');
            }

            if (! Schema::hasColumn('md_user', 'bidang_magang')) {
                $table->string('bidang_magang', 100)->nullable()->after('pembimbing_magang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_user', function (Blueprint $table) {
            if (Schema::hasColumn('md_user', 'bidang_magang')) {
                $table->dropColumn('bidang_magang');
            }

            if (Schema::hasColumn('md_user', 'pembimbing_magang')) {
                $table->dropColumn('pembimbing_magang');
            }
        });
    }
};
