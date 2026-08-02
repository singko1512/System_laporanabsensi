<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('md_user', function (Blueprint $table) {
            if (!Schema::hasColumn('md_user', 'grup')) {
                $table->string('grup', 10)->default('A')->after('role');
            }
        });

        // Set group based on existing schedule if available
        $users = DB::table('md_user')->where('role', 'user')->get();
        foreach ($users as $user) {
            $schedule = DB::table('md_jadwal_mingguan')->where('user_id', $user->id)->first();
            if ($schedule) {
                $grup = (strtolower($schedule->senin) === 'wfo') ? 'A' : 'B';
            } else {
                $grup = 'A';
            }
            DB::table('md_user')->where('id', $user->id)->update(['grup' => $grup]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_user', function (Blueprint $table) {
            if (Schema::hasColumn('md_user', 'grup')) {
                $table->dropColumn('grup');
            }
        });
    }
};
