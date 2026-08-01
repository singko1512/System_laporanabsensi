<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Bidang;
use App\Models\JadwalMingguan;
use App\Models\MasterData;
use App\Models\PembimbingMagang;
use App\Models\Pengaturan;
use App\Models\Project;
use App\Models\ProjectDayAssignment;
use App\Models\ProjectModule;
use App\Models\ProjectNote;
use App\Models\ProjectTask;
use App\Models\ProjectTaskParticipant;
use App\Models\ProjectTimeline;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        MasterData::seedDefaults();
        Pengaturan::updateOrCreate(
            ['kunci' => 'pin_admin'],
            ['nilai' => Hash::make('123456')]
        );
        Pengaturan::updateOrCreate(
            ['kunci' => 'pin_superadmin'],
            ['nilai' => Hash::make('180909')]
        );
        Pengaturan::updateOrCreate(
            ['kunci' => 'admin_login_username'],
            ['nilai' => 'admin']
        );
        Pengaturan::updateOrCreate(
            ['kunci' => 'admin_login_password'],
            ['nilai' => Hash::make('admin123')]
        );
        Pengaturan::updateOrCreate(
            ['kunci' => 'superadmin_login_username'],
            ['nilai' => 'superadmin']
        );
        Pengaturan::updateOrCreate(
            ['kunci' => 'superadmin_login_password'],
            ['nilai' => Hash::make('superadmin123')]
        );
        $this->seedAdminAccounts();

        $today = Carbon::today(config('app.timezone'));
        $mulaiMagang = $today->copy()->startOfMonth()->subMonth();
        $selesaiMagang = $mulaiMagang->copy()->addMonths(3)->endOfMonth();

        $dummyUsers = [
            ['nama' => 'Andi Pratama', 'nip_atau_id' => 'INT-2026-001', 'email' => 'andi.pratama@example.test', 'pembimbing_magang' => 'Rina Kartika', 'bidang_magang' => 'Backend Developer'],
            ['nama' => 'Bunga Maharani', 'nip_atau_id' => 'INT-2026-002', 'email' => 'bunga.maharani@example.test', 'pembimbing_magang' => 'Budi Santoso', 'bidang_magang' => 'Frontend Developer'],
            ['nama' => 'Candra Wijaya', 'nip_atau_id' => 'INT-2026-003', 'email' => 'candra.wijaya@example.test', 'pembimbing_magang' => 'Dimas Prakoso', 'bidang_magang' => 'Quality Assurance'],
            ['nama' => 'Dewi Lestari', 'nip_atau_id' => 'INT-2026-004', 'email' => 'dewi.lestari@example.test', 'pembimbing_magang' => 'Sari Amalia', 'bidang_magang' => 'UI/UX Designer'],
            ['nama' => 'Eko Saputra', 'nip_atau_id' => 'INT-2026-005', 'email' => 'eko.saputra@example.test', 'pembimbing_magang' => 'Rizky Maulana', 'bidang_magang' => 'Data Analyst'],
            ['nama' => 'Fitri Anjani', 'nip_atau_id' => 'INT-2026-006', 'email' => 'fitri.anjani@example.test', 'pembimbing_magang' => 'Rizky Maulana', 'bidang_magang' => 'Backend Developer'],
            ['nama' => 'Gilang Ramadhan', 'nip_atau_id' => 'INT-2026-007', 'email' => 'gilang.ramadhan@example.test', 'pembimbing_magang' => 'Sari Amalia', 'bidang_magang' => 'UI/UX Designer'],
            ['nama' => 'Hana Putri', 'nip_atau_id' => 'INT-2026-008', 'email' => 'hana.putri@example.test', 'pembimbing_magang' => 'Rizky Maulana', 'bidang_magang' => 'Data Analyst'],
        ];

        $bidangMasters = collect($dummyUsers)->pluck('bidang_magang')->unique()->values();
        $pembimbingMasters = collect($dummyUsers)
            ->mapWithKeys(fn (array $user) => [$user['pembimbing_magang'] => $user['bidang_magang']]);

        if (Schema::hasTable('md_bidang')) {
            $bidangMasters->each(fn (string $nama) => Bidang::updateOrCreate(['nama' => $nama]));
        }

        if (Schema::hasTable('md_pembimbing_magang')) {
            $pembimbingMasters->each(function (string $bidangNama, string $nama): void {
                $payload = [];

                if (Schema::hasColumn('md_pembimbing_magang', 'bidang_id') && Schema::hasTable('md_bidang')) {
                    $payload['bidang_id'] = Bidang::where('nama', $bidangNama)->value('id');
                }

                PembimbingMagang::updateOrCreate(['nama' => $nama], $payload);
            });
        }

        $schedulePatterns = [
            JadwalMingguan::defaultSchedule(),
            JadwalMingguan::grupA(),
            JadwalMingguan::grupB(),
        ];

        $users = collect($dummyUsers)->map(function (array $data, int $index) use ($mulaiMagang, $selesaiMagang, $schedulePatterns): User {
            $userPayload = [
                'nama' => $data['nama'],
                'email' => $data['email'],
                'tanggal_mulai_magang' => $mulaiMagang->copy()->addDays($index % 4)->toDateString(),
                'tanggal_selesai_magang' => $selesaiMagang->copy()->addDays($index % 6)->toDateString(),
            ];

            if (Schema::hasColumn('md_user', 'nip_atau_id')) {
                $userPayload['nip_atau_id'] = $data['nip_atau_id'];
            }

            if (Schema::hasColumn('md_user', 'pembimbing_magang')) {
                $userPayload['pembimbing_magang'] = $data['pembimbing_magang'];
            }

            if (Schema::hasColumn('md_user', 'pembimbing_magang_id') && Schema::hasTable('md_pembimbing_magang')) {
                $userPayload['pembimbing_magang_id'] = PembimbingMagang::where('nama', $data['pembimbing_magang'])->value('id');
            }

            if (Schema::hasColumn('md_user', 'bidang_magang')) {
                $userPayload['bidang_magang'] = $data['bidang_magang'];
            }

            if (Schema::hasColumn('md_user', 'bidang_id') && Schema::hasTable('md_bidang')) {
                $userPayload['bidang_id'] = Bidang::where('nama', $data['bidang_magang'])->value('id');
            }

            if (Schema::hasColumn('md_user', 'role')) {
                $userPayload['role'] = 'user';
            }

            if (Schema::hasColumn('md_user', 'username')) {
                $userPayload['username'] = Str::slug($data['nama'], '.');
            }

            if (Schema::hasColumn('md_user', 'password')) {
                $userPayload['password'] = 'password123';
            }

            if (Schema::hasColumn('md_user', 'status_akun')) {
                $userPayload['status_akun'] = 'aktif';
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $userPayload
            );

            JadwalMingguan::updateOrCreate(
                ['user_id' => $user->id],
                $schedulePatterns[$index % count($schedulePatterns)]
            );

            return $user;
        })->values();

        $this->seedDummyAbsensi($users, $today);
        $this->seedDummyProjects($users, $today);
    }

    private function seedDummyAbsensi($users, Carbon $today): void
    {
        if (! Schema::hasTable('md_absensi')) {
            return;
        }

        $workdays = collect();
        $date = $today->copy();

        while ($workdays->count() < 5) {
            if (! $date->isWeekend()) {
                $workdays->prepend($date->copy());
            }

            $date->subDay();
        }

        $statusPatterns = [
            ['hadir', 'wfh', 'hadir', 'hadir', 'wfh'],
            ['wfh', 'hadir', 'hadir', 'izin', 'hadir'],
            ['hadir', 'hadir', 'sakit', 'hadir', 'wfh'],
            ['hadir', 'wfh', 'hadir', 'wfh', 'hadir'],
        ];

        $locations = [
            ['lat' => -6.2087634, 'lng' => 106.8455990],
            ['lat' => -6.1753924, 'lng' => 106.8271528],
            ['lat' => -6.2607131, 'lng' => 106.7816165],
            ['lat' => -6.2297280, 'lng' => 106.6894312],
            ['lat' => -6.4024844, 'lng' => 106.7942405],
            ['lat' => -6.9147444, 'lng' => 107.6098111],
            ['lat' => -7.2504450, 'lng' => 112.7688450],
            ['lat' => -6.9903988, 'lng' => 110.4229104],
        ];

        $laporanTemplates = [
            'Menyusun rekap absensi dan validasi data laporan harian.',
            'Mengerjakan modul timeline project dan melakukan pengecekan catatan tugas.',
            'Merapikan tampilan dashboard admin serta menguji alur input data.',
            'Membuat dokumentasi kebutuhan fitur dan mencatat hasil progres pekerjaan.',
            'Melakukan review data WFH, lokasi, dan laporan aktivitas peserta magang.',
        ];

        $users->each(function (User $user, int $userIndex) use ($workdays, $statusPatterns, $locations, $laporanTemplates): void {
            foreach ($workdays as $dayIndex => $date) {
                $status = $statusPatterns[$userIndex % count($statusPatterns)][$dayIndex];
                $isWfh = $status === 'wfh';

                Absensi::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tanggal' => $date->toDateString(),
                    ],
                    [
                        'status' => $status,
                        'foto' => null,
                        'foto_kamera' => null,
                        'lokasi_latitude' => $isWfh ? $locations[$userIndex]['lat'] : null,
                        'lokasi_longitude' => $isWfh ? $locations[$userIndex]['lng'] : null,
                        'lokasi_akurasi' => $isWfh ? 18 + ($userIndex * 3) : null,
                        'lokasi_diambil_pada' => $isWfh ? $date->copy()->setTime(8 + ($userIndex % 2), 10 + $dayIndex, 0) : null,
                        'laporan' => $laporanTemplates[$dayIndex],
                    ]
                );
            }
        });
    }

    private function seedDummyProjects($users, Carbon $today): void
    {
        if (! Schema::hasTable('md_projects') || ! Schema::hasTable('md_project_notes')) {
            return;
        }

        $projectTemplates = [
            ['nama' => 'Dashboard Rekap Absensi', 'kebutuhan' => 'Filter rekap, ringkasan status, dan export laporan bulanan.'],
            ['nama' => 'Validasi Absensi WFH', 'kebutuhan' => 'Penguncian lokasi, bukti foto aktivitas, dan catatan pekerjaan WFH.'],
            ['nama' => 'Timeline Project Admin', 'kebutuhan' => 'Gantt harian, catatan prioritas, dan tombol penyelesaian note.'],
            ['nama' => 'Dokumentasi SOP Absensi', 'kebutuhan' => 'Alur penggunaan sistem, aturan WFO/WFH, dan format laporan harian.'],
            ['nama' => 'Portal Register Peserta', 'kebutuhan' => 'Form daftar akun, validasi email, pilihan bidang, dan pembimbing magang.'],
            ['nama' => 'Manajemen Master Magang', 'kebutuhan' => 'CRUD bidang magang, pembimbing magang, dan relasi dropdown pendaftaran.'],
            ['nama' => 'Monitoring Task Harian', 'kebutuhan' => 'Daftar task peserta, status join, progres approval, dan riwayat submission.'],
            ['nama' => 'Export Laporan Bulanan', 'kebutuhan' => 'Export rekap absensi ke Excel/PDF lengkap dengan filter bulan dan status.'],
            ['nama' => 'Sertifikat Magang Digital', 'kebutuhan' => 'Upload file sertifikat, akses private, dan status kelayakan sertifikat.'],
            ['nama' => 'Audit Login Role Sistem', 'kebutuhan' => 'Pemisahan akses peserta, admin, superadmin, serta redirect dashboard sesuai role.'],
        ];

        collect($projectTemplates)->each(function (array $template, int $index) use ($today, $users): void {
            $user = $users->get($index % max($users->count(), 1));
            if (! $user) {
                return;
            }

            $startDate = $today->copy()->startOfWeek(Carbon::MONDAY)->subDays($index % 3);
            $endDate = $startDate->copy()->addDays(13 + ($index % 4));

            $project = Project::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'nama' => $template['nama'],
                ],
                [
                    'kebutuhan' => $template['kebutuhan'],
                    'tanggal_mulai' => $startDate->toDateString(),
                    'tanggal_selesai' => $endDate->toDateString(),
                    'status' => 'aktif',
                ]
            );

            if (Schema::hasTable('md_project_user')) {
                $memberIds = collect([
                    $user->id,
                    optional($users->get(($index + 1) % $users->count()))->id,
                ])->filter()->unique()->values()->all();

                $project->members()->sync($memberIds);
            }

            $notes = [
                [
                    'tanggal' => $startDate->copy()->addDays(1),
                    'kategori' => 'rendah',
                    'judul' => 'Rapikan data awal',
                    'catatan' => 'Lengkapi data peserta magang, jadwal, dan kebutuhan awal project.',
                    'selesai_pada' => $today->copy()->subDay()->setTime(16, 30),
                ],
                [
                    'tanggal' => $today->copy()->isWeekend() ? $today->copy()->next(Carbon::MONDAY) : $today->copy(),
                    'kategori' => 'sedang',
                    'judul' => 'Checkpoint progres harian',
                    'catatan' => 'Update pekerjaan yang sudah selesai dan catat kendala yang perlu dicek admin.',
                    'selesai_pada' => null,
                ],
                [
                    'tanggal' => $startDate->copy()->addDays(5 + ($index % 3)),
                    'kategori' => 'tinggi',
                    'judul' => 'Review hasil modul',
                    'catatan' => 'Pastikan hasil pekerjaan sesuai kebutuhan sebelum masuk tahap berikutnya.',
                    'selesai_pada' => null,
                ],
            ];

            foreach ($notes as $note) {
                $notePayload = [
                    'kategori' => $note['kategori'],
                    'catatan' => $note['catatan'],
                    'selesai_pada' => $note['selesai_pada'],
                ];

                if (Schema::hasColumn('md_project_notes', 'user_id')) {
                    $notePayload['user_id'] = $user->id;
                }

                ProjectNote::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'tanggal' => $note['tanggal']->toDateString(),
                        'judul' => $note['judul'],
                    ],
                    $notePayload
                );
            }

            if (Schema::hasTable('md_project_day_assignments')) {
                foreach ($memberIds ?? [$user->id] as $memberIndex => $memberId) {
                    ProjectDayAssignment::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'user_id' => $memberId,
                            'tanggal' => $startDate->copy()->addDays($memberIndex + 1)->toDateString(),
                        ]
                    );

                    ProjectDayAssignment::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'user_id' => $memberId,
                            'tanggal' => $today->copy()->isWeekend() ? $today->copy()->next(Carbon::MONDAY)->toDateString() : $today->toDateString(),
                        ]
                    );
                }
            }

            $timeline = null;
            if (Schema::hasTable('md_project_timelines')) {
                $timeline = ProjectTimeline::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'nama' => 'Sprint 1',
                    ],
                    [
                        'tanggal_mulai' => $startDate->toDateString(),
                        'tanggal_selesai' => $startDate->copy()->addDays(6)->toDateString(),
                        'status' => 'berjalan',
                        'urutan' => 1,
                    ]
                );

                ProjectTimeline::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'nama' => 'Sprint 2',
                    ],
                    [
                        'tanggal_mulai' => $startDate->copy()->addDays(7)->toDateString(),
                        'tanggal_selesai' => $endDate->toDateString(),
                        'status' => $index % 2 === 0 ? 'belum_dimulai' : 'berjalan',
                        'urutan' => 2,
                    ]
                );
            }

            if (Schema::hasTable('md_project_modules') && Schema::hasTable('md_project_tasks')) {
                $module = ProjectModule::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'nama' => 'Modul Utama',
                    ],
                    [
                        'timeline_id' => $timeline?->id,
                        'deskripsi' => 'Pekerjaan inti yang perlu diselesaikan peserta magang.',
                        'progress' => 20 + (($index % 5) * 15),
                        'status' => $index % 3 === 0 ? 'selesai' : 'berjalan',
                        'urutan' => 1,
                    ]
                );

                if (Schema::hasTable('module_members')) {
                    $module->members()->sync($memberIds ?? [$user->id]);
                }

                $task = ProjectTask::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'judul' => 'Selesaikan checkpoint modul',
                    ],
                    [
                        'module_id' => $module->id,
                        'deskripsi' => 'Submit hasil pekerjaan lalu tunggu approval admin.',
                        'tanggal_mulai' => $startDate->toDateString(),
                        'tanggal_selesai' => $endDate->toDateString(),
                        'join_window_minutes' => 5,
                        'status' => 'open',
                        'urutan' => 1,
                    ]
                );

                if (Schema::hasTable('md_project_task_participants')) {
                    foreach (($memberIds ?? [$user->id]) as $memberId) {
                        ProjectTaskParticipant::updateOrCreate(
                            [
                                'task_id' => $task->id,
                                'user_id' => $memberId,
                            ],
                            [
                                'joined_at' => $today->copy()->setTime(8, 0),
                                'contribution_percentage' => round(100 / max(count($memberIds ?? [$user->id]), 1), 2),
                                'status' => ProjectTaskParticipant::STATUS_JOINED,
                            ]
                        );
                    }
                }
            }
        });
    }

    private function seedAdminAccounts(): void
    {
        if (! Schema::hasTable('md_user') || ! Schema::hasColumn('md_user', 'username') || ! Schema::hasColumn('md_user', 'password')) {
            return;
        }

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin',
                'email' => 'admin@example.test',
                'password' => 'admin123',
                'role' => 'admin',
                'status_akun' => 'aktif',
            ]
        );

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'nama' => 'Super Admin',
                'email' => 'superadmin@example.test',
                'password' => 'superadmin123',
                'role' => 'superadmin',
                'status_akun' => 'aktif',
            ]
        );
    }
}
