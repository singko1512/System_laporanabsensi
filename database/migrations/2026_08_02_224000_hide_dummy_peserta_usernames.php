<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $bidangCodes = ['pikp', 'aplikasi', 'infrastruktur', 'persandian', 'upt-rtv'];

    public function up(): void
    {
        if (! Schema::hasTable('md_user') || ! Schema::hasColumn('md_user', 'username')) {
            return;
        }

        DB::table('md_user')
            ->whereIn('email', $this->participantEmails())
            ->where('role', 'user')
            ->update([
                'username' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('md_user') || ! Schema::hasColumn('md_user', 'username')) {
            return;
        }

        foreach ($this->participantEmails() as $email) {
            DB::table('md_user')
                ->where('email', $email)
                ->where('role', 'user')
                ->update([
                    'username' => str_replace('@example.test', '', $email),
                    'updated_at' => now(),
                ]);
        }
    }

    private function participantEmails(): array
    {
        $emails = [];

        foreach ($this->bidangCodes as $code) {
            for ($i = 1; $i <= 20; $i++) {
                $emails[] = sprintf('peserta.%s.%02d@example.test', $code, $i);
            }
        }

        return $emails;
    }
};
