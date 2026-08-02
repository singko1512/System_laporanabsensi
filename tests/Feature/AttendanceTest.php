<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Absensi;
use App\Models\Bidang;
use App\Models\MasterData;
use App\Models\PembimbingMagang;
use App\Models\Pengaturan;
use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\ProjectNote;
use App\Models\ProjectNoteReply;
use App\Models\ProjectTask;
use App\Models\ProjectTaskParticipant;
use App\Models\ProjectTimeline;
use App\Models\User;
use App\Models\WorkSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    /**
     * Test user routes can be accessed.
     */
    public function test_user_routes_can_be_accessed(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('absensi.form'));
        $response->assertRedirect(route('absensi.index', ['tab' => 'form']));

        $response = $this->actingAs($user)->get(route('absensi.rekap'));
        $response->assertRedirect(route('absensi.index', ['tab' => 'form']));
    }

    public function test_home_schedule_groups_participants_by_bidang(): void
    {
        Pengaturan::updateOrCreate(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => 'individual']
        );

        $bidangAplikasi = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $bidangInfrastruktur = Bidang::firstOrCreate(['nama' => 'Bidang Infrastruktur Teknologi']);

        $aplikasiUser = User::factory()->create([
            'nama' => 'Aplikasi Intern',
            'email' => 'aplikasi.intern@example.test',
            'bidang_id' => $bidangAplikasi->id,
            'bidang_magang' => $bidangAplikasi->nama,
            'grup' => 'A',
            'role' => 'user',
        ]);
        $aplikasiUser->jadwalMingguan()->create([
            'senin' => 'wfo',
            'selasa' => 'wfh',
            'rabu' => 'wfo',
            'kamis' => 'wfh',
            'jumat' => 'wfh',
        ]);

        $infrastrukturUser = User::factory()->create([
            'nama' => 'Infrastruktur Intern',
            'email' => 'infrastruktur.intern@example.test',
            'bidang_id' => $bidangInfrastruktur->id,
            'bidang_magang' => $bidangInfrastruktur->nama,
            'grup' => 'B',
            'role' => 'user',
        ]);
        $infrastrukturUser->jadwalMingguan()->create([
            'senin' => 'wfh',
            'selasa' => 'wfo',
            'rabu' => 'wfh',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ]);

        $legacyBidang = Bidang::create(['nama' => 'Data Analyst']);
        $legacyUser = User::factory()->create([
            'nama' => 'Legacy Analyst Intern',
            'email' => 'legacy.analyst@example.test',
            'bidang_id' => $legacyBidang->id,
            'bidang_magang' => $legacyBidang->nama,
            'grup' => 'A',
            'role' => 'user',
        ]);
        $legacyUser->jadwalMingguan()->create([
            'senin' => 'wfo',
            'selasa' => 'wfo',
            'rabu' => 'wfo',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jadwal Kerja Minggu Ini')
            ->assertSee('schedule-bidang-tab', false)
            ->assertSee('Bidang Aplikasi Informatika')
            ->assertSee('Aplikasi Intern')
            ->assertSee('Bidang Infrastruktur Teknologi')
            ->assertSee('Infrastruktur Intern')
            ->assertDontSee('Data Analyst')
            ->assertDontSee('Legacy Analyst Intern');

        Pengaturan::updateOrCreate(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => 'team']
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tampilan Tim')
            ->assertSee('data-schedule-bidang-target', false)
            ->assertSee('Bidang Aplikasi Informatika')
            ->assertSee('KELOMPOK TIM A')
            ->assertSee('Bidang Infrastruktur Teknologi')
            ->assertSee('KELOMPOK TIM B');
    }

    /**
     * Test admin dashboard redirects to home if unauthenticated.
     */
    public function test_admin_dashboard_redirects_if_unauthenticated(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('error');
    }

    /**
     * Test superadmin authentication with separated credentials.
     */
    public function test_superadmin_can_login_with_credentials(): void
    {
        $response = $this->post(route('superadmin.login'), [
            'username' => 'superadmin',
            'password' => 'superadmin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('admin_authenticated', true);
        $response->assertSessionHas('admin_role', 'superadmin');
    }

    public function test_admin_can_login_with_separated_credentials(): void
    {
        $response = $this->post(route('admin.login'), [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $response->assertSessionHas('admin_authenticated', true);
        $response->assertSessionHas('admin_role', 'admin');
    }

    public function test_bidang_admin_accounts_are_available(): void
    {
        foreach ([
            'admin.pikp' => ['bidang' => 'Bidang Pengelolaan Informasi dan Komunikasi Publik', 'password' => 'K7mQ2vLp'],
            'admin.aplikasi' => ['bidang' => 'Bidang Aplikasi Informatika', 'password' => 'R9xT4nBa'],
            'admin.infrastruktur' => ['bidang' => 'Bidang Infrastruktur Teknologi', 'password' => 'P6hZ8cWu'],
            'admin.persandian' => ['bidang' => 'Bidang Persandian dan Statistik', 'password' => 'V3sL7qNd'],
            'admin.upt-rtv' => ['bidang' => 'Kepala UPT Radio dan Televisi', 'password' => 'M8rY5pXe'],
        ] as $username => $account) {
            $admin = User::where('username', $username)->first();

            $this->assertNotNull($admin, "Akun {$username} belum tersedia.");
            $this->assertSame('admin', $admin->role);
            $this->assertSame($account['bidang'], $admin->bidang?->nama);
            $this->assertTrue(Hash::check($account['password'], $admin->password));
        }

        $response = $this->post(route('admin.login'), [
            'username' => 'admin.aplikasi',
            'password' => 'R9xT4nBa',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $response->assertSessionHas('admin_authenticated', true);
        $response->assertSessionHas('admin_role', 'admin');
    }

    public function test_login_page_contains_register_option(): void
    {
        $this->get(route('login.form'))
            ->assertOk()
            ->assertSee('Daftar')
            ->assertSee('Email')
            ->assertDontSee('Email Peserta / ID Admin')
            ->assertDontSee('type="email"', false)
            ->assertSee('Lupa password?')
            ->assertSee(route('password.request'), false);

        $this->get(route('login.form', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('ID Admin / Email')
            ->assertDontSee('type="email"', false);

        $this->get(route('login.form', ['mode' => 'register']))
            ->assertOk()
            ->assertSee('Register Akun')
            ->assertSee(route('register.store'), false);
    }

    public function test_user_login_requires_email_not_username(): void
    {
        $user = User::factory()->create([
            'username' => 'peserta.email.only',
            'email' => 'peserta.email.only@example.test',
            'password' => 'password123',
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);

        $usernameResponse = $this->post(route('login'), [
            'login' => 'peserta.email.only',
            'password' => 'password123',
        ]);

        $usernameResponse->assertRedirect();
        $usernameResponse->assertSessionHas('error_swal');
        $this->assertGuest();

        $emailResponse = $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $emailResponse->assertRedirect(route('absensi.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_from_general_page_without_email_symbol(): void
    {
        $admin = User::where('username', 'admin.aplikasi')->firstOrFail();

        $response = $this->post(route('login'), [
            'login' => 'admin.aplikasi',
            'password' => 'R9xT4nBa',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_can_reset_password_with_registered_name_and_email(): void
    {
        $user = User::factory()->create([
            'nama' => 'Reset Password Intern',
            'email' => 'reset.intern@example.test',
            'password' => 'oldpass123',
            'role' => 'user',
        ]);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password')
            ->assertSee('Cek Data')
            ->assertSee(route('password.verify'), false)
            ->assertDontSee('Simpan Password Baru');

        $verifyResponse = $this->post(route('password.verify'), [
            'nama' => 'Reset Password Intern',
            'email' => 'reset.intern@example.test',
        ]);

        $verifyResponse->assertRedirect(route('password.request'));
        $verifyResponse->assertSessionHas('success_swal');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Simpan Password Baru')
            ->assertSee('reset.intern@example.test')
            ->assertSee(route('password.update'), false);

        $response = $this->post(route('password.update'), [
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('success_swal');

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_forgot_password_rejects_unmatched_or_admin_account(): void
    {
        $admin = User::factory()->create([
            'nama' => 'Admin Reset Guard',
            'email' => 'admin.reset.guard@example.test',
            'password' => 'oldpass123',
            'role' => 'admin',
        ]);

        $response = $this->post(route('password.verify'), [
            'nama' => 'Admin Reset Guard',
            'email' => 'admin.reset.guard@example.test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_swal');

        $unverifiedReset = $this->post(route('password.update'), [
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $unverifiedReset->assertRedirect(route('password.request'));
        $unverifiedReset->assertSessionHas('error_swal');

        $this->assertTrue(Hash::check('oldpass123', $admin->fresh()->password));
    }

    public function test_user_can_register_and_is_redirected_to_attendance(): void
    {
        $bidang = Bidang::create(['nama' => 'Backend Developer']);
        $pembimbing = PembimbingMagang::create(['nama' => 'Rina Kartika', 'bidang_id' => $bidang->id]);

        $response = $this->post(route('register.store'), [
            'nama' => 'Register Intern',
            'email' => 'register.intern@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'bidang_id' => $bidang->id,
            'pembimbing_magang_id' => $pembimbing->id,
            'tanggal_mulai_magang' => '2026-01-01',
            'tanggal_selesai_magang' => '2026-06-30',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('md_user', [
            'nama' => 'Register Intern',
            'email' => 'register.intern@example.test',
            'bidang_id' => $bidang->id,
            'pembimbing_magang_id' => $pembimbing->id,
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);

        $user = User::where('email', 'register.intern@example.test')->first();
        $this->assertNotNull($user->jadwalMingguan);
    }

    public function test_admin_login_rejects_user_account(): void
    {
        User::factory()->create([
            'username' => 'regular.user',
            'email' => 'regular.user@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response = $this->post(route('admin.login'), [
            'username' => 'regular.user',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login.form', ['role' => 'admin']));
        $response->assertSessionHas('error_swal');
        $this->assertGuest();
    }

    public function test_admin_and_superadmin_dashboard_render_after_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.render',
            'email' => 'admin.render@example.test',
        ]);
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.render',
            'email' => 'superadmin.render@example.test',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['tab' => 'pegawai']))
            ->assertOk()
            ->assertSee('Dashboard Admin');

        Auth::logout();

        $this->actingAs($superadmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Super Admin');
    }

    public function test_superadmin_can_filter_dashboard_by_bidang(): void
    {
        $bidangAplikasi = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $bidangRadio = Bidang::firstOrCreate(['nama' => 'Kepala UPT Radio dan Televisi']);

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.bidang.scope',
            'email' => 'superadmin.bidang.scope@example.test',
        ]);
        User::factory()->create([
            'nama' => 'Peserta Aplikasi Scope',
            'email' => 'peserta.aplikasi.scope@example.test',
            'bidang_id' => $bidangAplikasi->id,
            'bidang_magang' => $bidangAplikasi->nama,
        ]);
        User::factory()->create([
            'nama' => 'Peserta Radio Scope',
            'email' => 'peserta.radio.scope@example.test',
            'bidang_id' => $bidangRadio->id,
            'bidang_magang' => $bidangRadio->nama,
        ]);

        $this->actingAs($superadmin)
            ->get(route('admin.dashboard', ['tab' => 'pegawai', 'bidang_id' => $bidangAplikasi->id]))
            ->assertOk()
            ->assertSee('Bidang Aplikasi Informatika')
            ->assertSee('Peserta Aplikasi Scope')
            ->assertDontSee('Peserta Radio Scope');
    }

    public function test_admin_bidang_cannot_manage_other_bidang_user(): void
    {
        $bidangAplikasi = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $bidangRadio = Bidang::firstOrCreate(['nama' => 'Kepala UPT Radio dan Televisi']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.bidang.scope',
            'email' => 'admin.bidang.scope@example.test',
            'bidang_id' => $bidangAplikasi->id,
        ]);
        $otherUser = User::factory()->create([
            'bidang_id' => $bidangRadio->id,
            'bidang_magang' => $bidangRadio->nama,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.user.update', $otherUser->id), [])
            ->assertForbidden();
    }

    public function test_admin_bidang_can_access_scoped_timeline_project(): void
    {
        MasterData::seedDefaults();

        $bidangAplikasi = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $bidangRadio = Bidang::firstOrCreate(['nama' => 'Kepala UPT Radio dan Televisi']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.timeline.scope',
            'email' => 'admin.timeline.scope@example.test',
            'bidang_id' => $bidangAplikasi->id,
        ]);
        $memberAplikasi = User::factory()->create([
            'nama' => 'Peserta Timeline Aplikasi',
            'email' => 'timeline.aplikasi@example.test',
            'bidang_id' => $bidangAplikasi->id,
            'bidang_magang' => $bidangAplikasi->nama,
        ]);
        $memberRadio = User::factory()->create([
            'nama' => 'Peserta Timeline Radio',
            'email' => 'timeline.radio@example.test',
            'bidang_id' => $bidangRadio->id,
            'bidang_magang' => $bidangRadio->nama,
        ]);

        $ownProject = Project::create([
            'user_id' => $memberAplikasi->id,
            'nama' => 'Project Bidang Aplikasi',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);
        $ownProject->members()->sync([$memberAplikasi->id]);

        $otherProject = Project::create([
            'user_id' => $memberRadio->id,
            'nama' => 'Project Bidang Radio',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);
        $otherProject->members()->sync([$memberRadio->id]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['tab' => 'timeline']))
            ->assertOk()
            ->assertSee('Timeline Proyek')
            ->assertSee("timeline: document.getElementById('panel-timeline')", false)
            ->assertSee('Project Bidang Aplikasi')
            ->assertDontSee('Project Bidang Radio');

        $this->actingAs($admin)->post(route('admin.project.store'), [
            'user_ids' => [$memberAplikasi->id],
            'nama' => 'Project Baru Admin Bidang',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-20',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $this->assertDatabaseHas('md_projects', [
            'nama' => 'Project Baru Admin Bidang',
            'user_id' => $memberAplikasi->id,
        ]);

        $this->actingAs($admin)->post(route('admin.project.store'), [
            'user_ids' => [$memberRadio->id],
            'nama' => 'Project Ilegal Lintas Bidang',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-20',
        ])->assertSessionHasErrors('user_ids');

        $this->assertDatabaseMissing('md_projects', [
            'nama' => 'Project Ilegal Lintas Bidang',
        ]);
    }

    /**
     * Test admin authentication fails with incorrect credentials.
     */
    public function test_admin_cannot_login_with_incorrect_credentials(): void
    {
        $response = $this->post(route('admin.login'), [
            'username' => 'admin',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_swal');
        $response->assertSessionMissing('admin_authenticated');
    }

    /**
     * Test admin can perform CRUD operations on internship participants.
     */
    public function test_admin_can_crud_internship_participants(): void
    {
        $bidang = Bidang::create(['nama' => 'Backend Developer']);
        $updatedBidang = Bidang::create(['nama' => 'Quality Assurance']);
        $pembimbing = PembimbingMagang::create(['nama' => 'Rina Kartika', 'bidang_id' => $bidang->id]);
        $updatedPembimbing = PembimbingMagang::create(['nama' => 'Dimas Prakoso', 'bidang_id' => $updatedBidang->id]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.test',
            'email' => 'admin.test@example.test',
        ]);

        // 1. Create User
        $response = $this->actingAs($admin)->post(route('admin.user.store'), [
            'nama' => 'John Doe',
            'email' => 'john@example.test',
            'password' => 'secret123',
            'pembimbing_magang_id' => $pembimbing->id,
            'bidang_id' => $bidang->id,
            'tanggal_mulai_magang' => '2026-01-01',
            'tanggal_selesai_magang' => '2026-06-30',
            'status_akun' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseHas('md_user', [
            'nama' => 'John Doe',
            'email' => 'john@example.test',
            'pembimbing_magang_id' => $pembimbing->id,
            'pembimbing_magang' => 'Rina Kartika',
            'bidang_id' => $bidang->id,
            'bidang_magang' => 'Backend Developer',
        ]);

        $user = User::where('email', 'john@example.test')->first();

        // 2. Update User
        $response = $this->actingAs($admin)->post(route('admin.user.update', $user->id), [
            'nama' => 'John Doe Edited',
            'email' => 'john.edited@example.test',
            'pembimbing_magang_id' => $updatedPembimbing->id,
            'bidang_id' => $updatedBidang->id,
            'tanggal_mulai_magang' => '2026-02-01',
            'tanggal_selesai_magang' => '2026-07-31',
            'status_akun' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseHas('md_user', [
            'id' => $user->id,
            'nama' => 'John Doe Edited',
            'email' => 'john.edited@example.test',
            'pembimbing_magang_id' => $updatedPembimbing->id,
            'pembimbing_magang' => 'Dimas Prakoso',
            'bidang_id' => $updatedBidang->id,
            'bidang_magang' => 'Quality Assurance',
        ]);

        // 3. Delete User
        $response = $this->actingAs($admin)->post(route('admin.user.destroy', $user->id));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseMissing('md_user', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test WFH attendance stores check-in with selected task.
     */
    public function test_wfh_attendance_stores_check_in_with_selected_task(): void
    {
        MasterData::seedDefaults();
        $user = User::factory()->create([
            'nama' => 'Alice Intern',
            'email' => 'alice@example.test',
            'bidang_magang' => 'Backend Developer',
        ]);
        $project = Project::create([
            'user_id' => $user->id,
            'nama' => 'Backend Project',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'status' => 'aktif',
        ]);
        $project->members()->sync([$user->id]);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'judul' => 'Build API',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'join_window_minutes' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'wfh',
            'task_id' => $task->id,
            'foto_kamera' => $this->fakePng('wfh.png'),
            'lokasi_latitude' => -6.2087,
            'lokasi_longitude' => 106.8456,
            'keterangan' => 'WFH dari rumah',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status_id' => MasterData::idFor(MasterData::ABSENSI_STATUS, 'wfh'),
            'laporan' => 'WFH dari rumah',
        ]);
        $this->assertDatabaseHas('md_project_task_participants', [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test Hadir attendance still requires camera photo.
     */
    public function test_hadir_attendance_still_requires_camera_photo(): void
    {
        $user = User::factory()->create([
            'nama' => 'Bob Intern',
            'email' => 'bob@example.test',
            'bidang_magang' => 'Frontend Developer',
        ]);
        MasterData::seedDefaults();
        $project = Project::create([
            'user_id' => $user->id,
            'nama' => 'Frontend Project',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'status' => 'aktif',
        ]);
        $project->members()->sync([$user->id]);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'judul' => 'Build UI',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'hadir',
            'task_id' => $task->id,
        ]);

        $response->assertSessionHasErrors(['foto_kamera']);
    }

    public function test_izin_attendance_requires_location_without_camera_or_attachment(): void
    {
        MasterData::seedDefaults();
        $user = User::factory()->create([
            'nama' => 'Izin Intern',
            'email' => 'izin@example.test',
        ]);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'izin',
            'lokasi_latitude' => -6.2087,
            'lokasi_longitude' => 106.8456,
            'lokasi_akurasi' => 12,
            'keterangan' => 'Izin keperluan kampus',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $user->id,
            'status_id' => MasterData::idFor(MasterData::ABSENSI_STATUS, 'izin'),
            'lokasi_masuk_latitude' => -6.2087,
            'lokasi_masuk_longitude' => 106.8456,
            'laporan' => 'Izin keperluan kampus',
        ]);
    }

    public function test_attendance_accepts_large_gps_accuracy_value(): void
    {
        MasterData::seedDefaults();
        $user = User::factory()->create([
            'nama' => 'Large Accuracy Intern',
            'email' => 'large.accuracy@example.test',
        ]);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'izin',
            'lokasi_latitude' => -6.2087654,
            'lokasi_longitude' => 106.8456789,
            'lokasi_akurasi' => 1500000.55,
            'keterangan' => 'Izin dengan akurasi GPS besar',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $user->id,
            'lokasi_akurasi' => 1500000.55,
            'lokasi_masuk_akurasi' => 1500000.55,
        ]);
    }

    public function test_wfh_checkout_requires_attachment_description_and_location(): void
    {
        MasterData::seedDefaults();
        Storage::fake('local');

        $user = User::factory()->create([
            'nama' => 'WFH Checkout Intern',
            'email' => 'wfh.checkout@example.test',
        ]);

        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => now(config('app.timezone'))->toDateString(),
            'jam_masuk' => '08:00:00',
            'status' => 'wfh',
            'status_id' => MasterData::idFor(MasterData::ABSENSI_STATUS, 'wfh'),
            'status_masuk_id' => MasterData::idFor(MasterData::ABSENSI_STATUS, 'wfh'),
            'lokasi_latitude' => -6.2087,
            'lokasi_longitude' => 106.8456,
            'lokasi_masuk_latitude' => -6.2087,
            'lokasi_masuk_longitude' => 106.8456,
            'lokasi_masuk_diambil_pada' => now(config('app.timezone'))->setTime(8, 0),
        ]);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'wfh',
        ]);

        $response->assertSessionHasErrors(['foto', 'keterangan', 'lokasi_latitude', 'lokasi_longitude']);

        $response = $this->actingAs($user)->post(route('absensi.store'), [
            'status' => 'wfh',
            'foto' => $this->fakePng('hasil-wfh.png'),
            'lokasi_latitude' => -6.2000,
            'lokasi_longitude' => 106.8400,
            'lokasi_akurasi' => 14,
            'keterangan' => 'Menyelesaikan integrasi project WFH',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $user->id,
            'lokasi_pulang_latitude' => -6.2000,
            'lokasi_pulang_longitude' => 106.8400,
            'laporan' => 'Menyelesaikan integrasi project WFH',
        ]);
    }

    /**
     * Test note completion flow (user completion and admin confirmation).
     */
    public function test_note_completion_flow(): void
    {
        $user = User::factory()->create([
            'nama' => 'Charlie Intern',
            'email' => 'charlie@example.test',
            'bidang_magang' => 'Quality Assurance',
        ]);
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.test',
            'email' => 'superadmin.test@example.test',
        ]);

        MasterData::seedDefaults();

        $project = Project::create([
            'user_id' => $user->id,
            'nama' => 'QA Project',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'status' => 'aktif',
        ]);

        $note = ProjectNote::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'tanggal' => now()->toDateString(),
            'kategori' => 'sedang',
            'judul' => 'Write test cases',
        ]);

        // User marks it complete
        $response = $this->actingAs($user)->post(route('timeline.note.complete', $note->id), [
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $note->refresh();
        $this->assertNotNull($note->user_selesai_pada);
        $this->assertNull($note->selesai_pada);

        // Admin marks/confirms it complete
        $response2 = $this->actingAs($superadmin)
            ->post(route('timeline.note.complete', $note->id));

        $response2->assertRedirect();
        $note->refresh();
        $this->assertNotNull($note->user_selesai_pada);
        $this->assertNotNull($note->selesai_pada);
    }

    public function test_superadmin_can_create_project_timeline_module_with_pic(): void
    {
        MasterData::seedDefaults();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.timeline',
            'email' => 'superadmin.timeline@example.test',
        ]);
        $memberA = User::factory()->create([
            'nama' => 'Arjuna Intern',
            'email' => 'arjuna.timeline@example.test',
        ]);
        $memberB = User::factory()->create([
            'nama' => 'Fachrizal Intern',
            'email' => 'fachrizal.timeline@example.test',
        ]);

        $project = Project::create([
            'user_id' => $memberA->id,
            'nama' => 'Project Website',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);
        $project->members()->sync([$memberA->id, $memberB->id]);

        $timelineResponse = $this->actingAs($superadmin)->post(route('admin.project.timeline.store'), [
            'project_id' => $project->id,
            'nama' => 'Sprint 1',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-14',
            'status' => ProjectTimeline::STATUS_BERJALAN,
        ]);

        $timelineResponse->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $timeline = ProjectTimeline::where('project_id', $project->id)->where('nama', 'Sprint 1')->firstOrFail();

        $moduleResponse = $this->actingAs($superadmin)->post(route('admin.project.module.store'), [
            'timeline_id' => $timeline->id,
            'nama' => 'Authentication',
            'tanggal_mulai' => '2026-08-05',
            'tanggal_selesai' => '2026-08-14',
            'deskripsi' => 'Modul autentikasi pengguna.',
            'progress' => 60,
            'status' => ProjectModule::STATUS_BERJALAN,
            'user_ids' => [$memberA->id, $memberB->id],
        ]);

        $moduleResponse->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $module = ProjectModule::where('timeline_id', $timeline->id)->where('nama', 'Authentication')->firstOrFail();
        $this->assertSame($project->id, $module->project_id);
        $this->assertSame(60.0, (float) $module->progress);
        $this->assertDatabaseHas('module_members', [
            'module_id' => $module->id,
            'user_id' => $memberA->id,
        ]);
        $this->assertDatabaseHas('module_members', [
            'module_id' => $module->id,
            'user_id' => $memberB->id,
        ]);

        $timeline2 = ProjectTimeline::create([
            'project_id' => $project->id,
            'nama' => 'Sprint 2',
            'tanggal_mulai' => '2026-08-15',
            'tanggal_selesai' => '2026-08-31',
            'status' => ProjectTimeline::STATUS_BELUM_DIMULAI,
            'urutan' => 2,
        ]);

        $this->actingAs($superadmin)->postJson(route('admin.project.module.reorder'), [
            'modules' => [
                [
                    'id' => $module->id,
                    'timeline_id' => $timeline2->id,
                    'urutan' => 1,
                ],
            ],
        ])->assertOk()
            ->assertJson(['message' => 'Urutan modul berhasil disimpan.']);

        $module->refresh();
        $this->assertSame($timeline2->id, $module->timeline_id);
        $this->assertSame(1, $module->urutan);

        $this->actingAs($superadmin)->post(route('admin.project.timeline.update', $timeline2), [
            'nama' => 'Sprint 2 Updated',
            'tanggal_mulai' => '2026-08-16',
            'tanggal_selesai' => '2026-08-31',
            'status' => ProjectTimeline::STATUS_SELESAI,
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $timeline2->refresh();
        $this->assertSame('Sprint 2 Updated', $timeline2->nama);
        $this->assertSame(ProjectTimeline::STATUS_SELESAI, $timeline2->status);

        $this->actingAs($superadmin)->post(route('admin.project.module.update', $module), [
            'timeline_id' => $timeline2->id,
            'nama' => 'Authentication Updated',
            'tanggal_mulai' => '2026-08-16',
            'tanggal_selesai' => '2026-08-25',
            'deskripsi' => 'Modul autentikasi pengguna versi update.',
            'progress' => 85,
            'status' => ProjectModule::STATUS_SELESAI,
            'user_ids' => [$memberA->id],
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $module->refresh();
        $this->assertSame('Authentication Updated', $module->nama);
        $this->assertSame(85.0, (float) $module->progress);
        $this->assertDatabaseHas('module_members', [
            'module_id' => $module->id,
            'user_id' => $memberA->id,
        ]);
        $this->assertDatabaseMissing('module_members', [
            'module_id' => $module->id,
            'user_id' => $memberB->id,
        ]);

        $this->actingAs($superadmin)
            ->post(route('admin.project.module.destroy', $module))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $this->assertDatabaseMissing('md_project_modules', [
            'id' => $module->id,
        ]);

        $this->actingAs($superadmin)
            ->post(route('admin.project.timeline.destroy', $timeline2))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $this->assertDatabaseMissing('md_project_timelines', [
            'id' => $timeline2->id,
        ]);
    }

    public function test_superadmin_can_create_project_then_module_without_manual_timeline(): void
    {
        MasterData::seedDefaults();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.module.flow',
            'email' => 'superadmin.module.flow@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'Module Flow Intern',
            'email' => 'module.flow@example.test',
        ]);

        $this->actingAs($superadmin)->post(route('admin.project.store'), [
            'user_ids' => [$member->id],
            'nama' => 'Project Langsung Modul',
            'kebutuhan' => 'Project tanpa input timeline manual.',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $project = Project::where('nama', 'Project Langsung Modul')->firstOrFail();
        $timeline = ProjectTimeline::where('project_id', $project->id)->firstOrFail();
        $this->assertSame('Timeline Proyek', $timeline->nama);
        $this->assertSame('2026-08-01', $timeline->tanggal_mulai->toDateString());
        $this->assertSame('2026-08-31', $timeline->tanggal_selesai->toDateString());

        $this->actingAs($superadmin)->post(route('admin.project.module.store'), [
            'project_id' => $project->id,
            'nama' => 'Dashboard',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-20',
            'deskripsi' => 'Membuat dashboard utama.',
            'progress' => 25,
            'status' => ProjectModule::STATUS_BERJALAN,
            'user_ids' => [$member->id],
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $this->assertDatabaseHas('md_project_modules', [
            'project_id' => $project->id,
            'timeline_id' => $timeline->id,
            'nama' => 'Dashboard',
            'status' => ProjectModule::STATUS_BERJALAN,
        ]);
    }

    public function test_superadmin_can_delete_project_with_task_dependencies(): void
    {
        MasterData::seedDefaults();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.delete.project',
            'email' => 'superadmin.delete.project@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'Delete Project Intern',
            'email' => 'delete.project.intern@example.test',
        ]);

        $project = Project::create([
            'user_id' => $member->id,
            'nama' => 'Project Delete Dependency',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => 'aktif',
        ]);
        $project->members()->sync([$member->id]);

        $timeline = ProjectTimeline::create([
            'project_id' => $project->id,
            'nama' => 'Sprint Delete',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => ProjectTimeline::STATUS_BERJALAN,
            'urutan' => 1,
        ]);

        $module = ProjectModule::create([
            'project_id' => $project->id,
            'timeline_id' => $timeline->id,
            'nama' => 'Module Delete',
            'progress' => 30,
            'status' => ProjectModule::STATUS_BERJALAN,
            'urutan' => 1,
        ]);
        $module->members()->sync([$member->id]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task Delete',
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'status' => 'open',
        ]);

        $participant = ProjectTaskParticipant::create([
            'task_id' => $task->id,
            'user_id' => $member->id,
            'status' => ProjectTaskParticipant::STATUS_SUBMITTED,
            'joined_at' => now(),
        ]);

        $submission = WorkSubmission::create([
            'task_participant_id' => $participant->id,
            'task_id' => $task->id,
            'user_id' => $member->id,
            'tanggal' => now()->toDateString(),
            'isi_laporan' => 'Laporan sebelum project dihapus.',
            'status' => WorkSubmission::STATUS_SUBMITTED,
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $task->id,
            'user_id' => $member->id,
            'tipe' => 'submission',
            'isi' => 'Reply sebelum project dihapus.',
        ]);

        Absensi::create([
            'user_id' => $member->id,
            'task_id' => $task->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'status_id' => MasterData::idFor(MasterData::ABSENSI_STATUS, 'hadir'),
            'jam_masuk' => '08:00:00',
        ]);

        $this->actingAs($superadmin)
            ->post(route('admin.project.destroy', $project))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'timeline']));

        $this->assertDatabaseMissing('md_projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('md_project_tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('md_project_modules', ['id' => $module->id]);
        $this->assertDatabaseMissing('md_project_timelines', ['id' => $timeline->id]);
        $this->assertDatabaseMissing('md_work_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('md_project_task_participants', ['id' => $participant->id]);
        $this->assertDatabaseMissing('module_members', ['module_id' => $module->id]);
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $member->id,
            'task_id' => null,
        ]);
    }

    /**
     * Test admin can perform CRUD operations on divisions (Bidang).
     */
    public function test_admin_can_crud_bidang(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.bidang',
            'email' => 'superadmin.bidang@example.test',
        ]);

        // 1. Create Bidang
        $response = $this->actingAs($superadmin)->post(route('admin.bidang.store'), [
            'nama' => 'Mobile Developer',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_bidang', [
            'nama' => 'Mobile Developer',
        ]);

        $bidang = Bidang::where('nama', 'Mobile Developer')->first();

        // 2. Update Bidang
        $response = $this->actingAs($superadmin)->post(route('admin.bidang.update', $bidang->id), [
            'nama' => 'Mobile Apps Developer',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_bidang', [
            'id' => $bidang->id,
            'nama' => 'Mobile Apps Developer',
        ]);

        // 3. Destroy Bidang
        $response = $this->actingAs($superadmin)->post(route('admin.bidang.destroy', $bidang->id));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseMissing('md_bidang', [
            'id' => $bidang->id,
        ]);
    }

    public function test_admin_can_crud_pembimbing_magang(): void
    {
        $bidang = Bidang::create(['nama' => 'Backend Developer']);
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.pembimbing',
            'email' => 'superadmin.pembimbing@example.test',
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.pembimbing.store'), [
            'nama' => 'Rina Kartika',
            'bidang_id' => $bidang->id,
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_pembimbing_magang', [
            'nama' => 'Rina Kartika',
            'bidang_id' => $bidang->id,
        ]);

        $pembimbing = PembimbingMagang::where('nama', 'Rina Kartika')->first();

        $response = $this->actingAs($superadmin)->post(route('admin.pembimbing.update', $pembimbing->id), [
            'nama' => 'Rina Kartika Updated',
            'bidang_id' => $bidang->id,
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_pembimbing_magang', [
            'id' => $pembimbing->id,
            'nama' => 'Rina Kartika Updated',
            'bidang_id' => $bidang->id,
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.pembimbing.destroy', $pembimbing->id));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseMissing('md_pembimbing_magang', [
            'id' => $pembimbing->id,
        ]);
    }

    public function test_admin_role_cannot_manage_bidang(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.restricted',
            'email' => 'admin.restricted@example.test',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bidang.store'), [
            'nama' => 'Restricted Division',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error_swal');
        $this->assertDatabaseMissing('md_bidang', [
            'nama' => 'Restricted Division',
        ]);
    }

    public function test_admin_can_generate_certificate_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.cert',
            'email' => 'admin.cert@example.test',
        ]);

        $user = User::factory()->create([
            'nama' => 'Dina Intern',
            'email' => 'dina@example.test',
            'bidang_magang' => 'Bidang Aplikasi Informatika',
            'pembimbing_magang' => 'Pembimbing Dina',
            'tanggal_mulai_magang' => now()->subMonths(3)->toDateString(),
            'tanggal_selesai_magang' => now()->subDay()->toDateString(),
        ]);

        $preview = $this->get(route('sertifikat.show', Str::slug($user->nama)));
        $preview->assertOk()
            ->assertSee('SERTIFIKAT')
            ->assertSee('Dina Intern')
            ->assertSee('Bidang Aplikasi Informatika')
            ->assertSee('Pembimbing Magang')
            ->assertSee('Pembimbing Dina')
            ->assertDontSee('Kepala Dinas Komunikasi dan Informatika Kabupaten Bogor');

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['tab' => 'sertifikat']))
            ->assertOk()
            ->assertSee('Preview')
            ->assertSee('PDF');

        $response = $this->actingAs($admin)->get(route('admin.sertifikat.generate', $user));
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_superadmin_can_upload_certificate_template_for_pdf_generation(): void
    {
        Storage::fake('local');

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.cert.template',
            'email' => 'superadmin.cert.template@example.test',
        ]);

        $user = User::factory()->create([
            'nama' => 'Template Intern',
            'email' => 'template.intern@example.test',
            'bidang_magang' => 'Bidang Infrastruktur Teknologi',
            'tanggal_mulai_magang' => now()->subMonths(2)->toDateString(),
            'tanggal_selesai_magang' => now()->subDay()->toDateString(),
        ]);

        $templateHtml = '<!doctype html><html><body><h1>SERTIFIKAT CUSTOM</h1><p>{{ nama }}</p><p>{{ bidang }}</p></body></html>';

        $uploadResponse = $this->actingAs($superadmin)->post(route('admin.sertifikat.template.upload'), [
            'certificate_template' => UploadedFile::fake()->createWithContent('template-sertifikat.html', $templateHtml),
        ]);

        $uploadResponse->assertRedirect(route('admin.dashboard', ['tab' => 'sertifikat']));

        $preview = $this->get(route('sertifikat.show', Str::slug($user->nama)));
        $preview->assertOk()
            ->assertSee('SERTIFIKAT CUSTOM')
            ->assertSee('Template Intern')
            ->assertSee('Bidang Infrastruktur Teknologi');

        $pdf = $this->actingAs($superadmin)->get(route('admin.sertifikat.generate', $user));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
    }

    public function test_user_focused_workflow_limits(): void
    {
        MasterData::seedDefaults();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.focused',
            'email' => 'superadmin.focused@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'Focused Intern',
            'email' => 'focused.intern@example.test',
        ]);

        // Create project
        $project = Project::create([
            'user_id' => $superadmin->id,
            'nama' => 'Project Focus',
            'kebutuhan' => 'Kebutuhan focus',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
        ]);
        $project->members()->attach($member->id);

        // Create module & tasks
        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module A',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-15',
            'bobot' => 50,
        ]);

        $task1 = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task 1',
            'status' => 'belum_dikerjakan',
        ]);

        $task2 = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task 2',
            'status' => 'belum_dikerjakan',
        ]);

        // 1. User takes Task 1
        $this->actingAs($member)
            ->post(route('absensi.task.ambil', $task1))
            ->assertRedirect();

        $task1->refresh();
        $this->assertSame($member->id, $task1->user_id);
        $this->assertSame('sedang_dikerjakan', $task1->status);

        // 2. User tries to take Task 2 while Task 1 is active (should fail due to Focused Workflow)
        $response = $this->actingAs($member)
            ->post(route('absensi.task.ambil', $task2));

        $response->assertSessionHas('error_swal');
        $task2->refresh();
        $this->assertNull($task2->user_id);

        // 3. Admin approves Task 1
        $this->actingAs($superadmin)
            ->post(route('admin.project.task.approve', $task1))
            ->assertRedirect();

        $task1->refresh();
        $this->assertSame('selesai', $task1->status);

        // 4. User should now be able to take Task 2 (as they have no active tasks left)
        $this->actingAs($member)
            ->post(route('absensi.task.ambil', $task2))
            ->assertRedirect();

        $task2->refresh();
        $this->assertSame($member->id, $task2->user_id);
        $this->assertSame('sedang_dikerjakan', $task2->status);
    }

    public function test_admin_can_manage_task_pic_and_delete_tasks(): void
    {
        MasterData::seedDefaults();

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'superadmin.task.manage',
            'email' => 'superadmin.task.manage@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'Task Manage Intern',
            'email' => 'task.manage.intern@example.test',
        ]);

        // Create project
        $project = Project::create([
            'user_id' => $superadmin->id,
            'nama' => 'Project Task Management',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
        ]);
        $project->members()->attach($member->id);

        // Create module
        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Task Management',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-15',
            'bobot' => 50,
        ]);

        // 1. Admin stores a task WITH a PIC
        $this->actingAs($superadmin)->post(route('admin.project.task.store'), [
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task With PIC',
            'deskripsi' => 'Some description',
            'user_id' => $member->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('md_project_tasks', [
            'judul' => 'Task With PIC',
            'user_id' => $member->id,
            'status' => 'sedang_dikerjakan',
        ]);

        $taskWithPic = ProjectTask::where('judul', 'Task With PIC')->firstOrFail();
        $this->assertDatabaseHas('md_project_task_participants', [
            'task_id' => $taskWithPic->id,
            'user_id' => $member->id,
            'status' => 'joined',
        ]);

        // 2. Admin stores a task WITHOUT a PIC
        $this->actingAs($superadmin)->post(route('admin.project.task.store'), [
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task Unassigned',
            'deskripsi' => 'Another description',
        ])->assertRedirect();

        $this->assertDatabaseHas('md_project_tasks', [
            'judul' => 'Task Unassigned',
            'user_id' => null,
            'status' => 'belum_dikerjakan',
        ]);

        $taskUnassigned = ProjectTask::where('judul', 'Task Unassigned')->firstOrFail();

        // 3. Admin assigns PIC to the unassigned task
        $this->actingAs($superadmin)->post(route('admin.project.task.assign_pic', $taskUnassigned), [
            'user_id' => $member->id,
        ])->assertRedirect();

        $taskUnassigned->refresh();
        $this->assertSame($member->id, $taskUnassigned->user_id);
        $this->assertSame('sedang_dikerjakan', $taskUnassigned->status);

        // 4. Admin unassigns PIC from the task
        $this->actingAs($superadmin)->post(route('admin.project.task.unassign_pic', $taskUnassigned))
            ->assertRedirect();

        $taskUnassigned->refresh();
        $this->assertNull($taskUnassigned->user_id);
        $this->assertSame('belum_dikerjakan', $taskUnassigned->status);

        // 5. Admin deletes the task
        $this->actingAs($superadmin)->post(route('admin.project.task.destroy', $taskUnassigned))
            ->assertRedirect();

        $this->assertDatabaseMissing('md_project_tasks', [
            'id' => $taskUnassigned->id,
        ]);
    }

    public function test_project_less_user_can_select_task_and_auto_join_project(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin.auto.join',
            'email' => 'admin.auto.join@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'Auto Join Intern',
            'email' => 'autojoin.intern@example.test',
        ]);

        // Create project with NO members
        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project Auto Join',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
        ]);

        // Create module
        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Auto Join',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-15',
            'bobot' => 100,
        ]);

        // Create available task
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task Auto Join',
            'status' => 'belum_dikerjakan',
        ]);

        // Initially member has no projects
        $this->assertFalse($project->members()->where('md_user.id', $member->id)->exists());

        // Member submits check-in selecting this task
        $response = $this->actingAs($member)->post(route('absensi.store'), [
            'status' => 'hadir',
            'task_id' => $task->id,
            'lokasi_latitude' => -6.200000,
            'lokasi_longitude' => 106.816666,
            'lokasi_akurasi' => 15.0,
            'foto_kamera' => $this->fakePng('kamera.png'),
        ]);

        $response->assertRedirect();

        // Member should now be in the project members pivot table automatically!
        $this->assertTrue($project->members()->where('md_user.id', $member->id)->exists());

        // Task should be assigned to the member
        $task->refresh();
        $this->assertSame($member->id, $task->user_id);
        $this->assertSame('sedang_dikerjakan', $task->status);
    }

    public function test_admin_assigns_pic_who_is_not_member_auto_joins_project(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'admin.pic.join',
            'email' => 'admin.pic.join@example.test',
        ]);
        $member = User::factory()->create([
            'nama' => 'PIC Join Intern',
            'email' => 'picjoin.intern@example.test',
        ]);

        // Create project with NO members
        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project PIC Join',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
        ]);

        // Create module
        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module PIC Join',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-15',
            'bobot' => 100,
        ]);

        // 1. Admin stores a task WITH this member as PIC
        $this->actingAs($admin)->post(route('admin.project.task.store'), [
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task PIC Join 1',
            'deskripsi' => 'Some description',
            'user_id' => $member->id,
        ])->assertRedirect();

        // Member should be in the project members pivot table automatically!
        $this->assertTrue($project->members()->where('md_user.id', $member->id)->exists());

        // Reset project membership for testing assign PIC
        $project->members()->detach($member->id);
        $this->assertFalse($project->members()->where('md_user.id', $member->id)->exists());

        // Create available task
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task PIC Join 2',
            'status' => 'belum_dikerjakan',
        ]);

        // 2. Admin assigns this task's PIC to this member
        $this->actingAs($admin)->post(route('admin.project.task.assign_pic', $task), [
            'user_id' => $member->id,
        ])->assertRedirect();

        // Member should be in the project members pivot table automatically!
        $this->assertTrue($project->members()->where('md_user.id', $member->id)->exists());
    }

    public function test_user_can_self_assign_module_directly_and_via_checkin(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create([
            'role' => 'superadmin',
            'username' => 'admin.mod',
            'email' => 'admin.mod@example.test',
        ]);
        $member1 = User::factory()->create([
            'nama' => 'Module Take Member 1',
            'email' => 'mod1.take@example.test',
        ]);
        $member2 = User::factory()->create([
            'nama' => 'Module Take Member 2',
            'email' => 'mod2.take@example.test',
        ]);

        // Create project with NO members
        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project Open Mod',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);

        // Create module 1 (for direct take)
        $module1 = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Direct Take',
            'bobot' => 50,
        ]);

        // Create module 2 (for check-in take)
        $module2 = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Checkin Take',
            'bobot' => 50,
        ]);

        // 1. Member 1 takes Module 1 directly
        $this->actingAs($member1)->post(route('absensi.module.ambil', $module1))
            ->assertRedirect();

        // Verify Member 1 auto-joined the project
        $this->assertTrue($project->members()->where('md_user.id', $member1->id)->exists());

        // Verify task was created for the module
        $task1 = ProjectTask::where('module_id', $module1->id)->first();
        $this->assertNotNull($task1);
        $this->assertSame($member1->id, $task1->user_id);
        $this->assertSame('Pengerjaan Modul: '.$module1->nama, $task1->judul);
        $this->assertSame('sedang_dikerjakan', $task1->status);

        // 2. Member 2 takes Module 2 via check-in
        $this->actingAs($member2)->post(route('absensi.store'), [
            'status' => 'hadir',
            'task_id' => 'module_'.$module2->id,
            'foto_kamera' => $this->fakePng('hadir.png'),
            'lokasi_latitude' => -6.2087,
            'lokasi_longitude' => 106.8456,
            'keterangan' => 'Mulai modul',
        ])->assertRedirect();

        // Verify Member 2 auto-joined the project
        $this->assertTrue($project->members()->where('md_user.id', $member2->id)->exists());

        // Verify task was created and check-in links to it
        $task2 = ProjectTask::where('module_id', $module2->id)->first();
        $this->assertNotNull($task2);
        $this->assertSame($member2->id, $task2->user_id);
        $this->assertSame('sedang_dikerjakan', $task2->status);

        $absensi = Absensi::where('user_id', $member2->id)->first();
        $this->assertNotNull($absensi);
        $this->assertSame($task2->id, $absensi->task_id);
    }

    public function test_task_is_hidden_until_its_module_is_chosen(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create([
            'nama' => 'Regular User',
            'email' => 'reg.user@example.test',
        ]);
        $otherUser = User::factory()->create([
            'nama' => 'Other User',
            'email' => 'other.user@example.test',
        ]);

        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project Dependency',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);

        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Alpha',
            'bobot' => 100,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Task inside Alpha',
            'status' => 'belum_dikerjakan',
            'user_id' => null,
        ]);

        // 1. Visit dashboard selecting the project
        $response = $this->actingAs($user)->get(route('absensi.index', ['project_id' => $project->id]));
        $response->assertStatus(200);

        // Assert that the module Alpha is visible
        $response->assertSee('Module Alpha');
        // Assert that the task 'Task inside Alpha' is NOT visible (since the module is not chosen yet)
        $response->assertDontSee('Task inside Alpha');

        // 2. Other User claims module Alpha (take it directly)
        $this->actingAs($otherUser)->post(route('absensi.module.ambil', $module))->assertRedirect();

        // 3. Visit dashboard again. The remaining tasks inside Module Alpha (like 'Task inside Alpha') should now be visible!
        $response = $this->actingAs($user)->get(route('absensi.index', ['project_id' => $project->id]));
        $response->assertStatus(200);

        $response->assertSee('Task inside Alpha');
    }

    public function test_approving_module_task_completes_all_tasks_in_module(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project Auto Finish',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);

        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Beta',
            'bobot' => 100,
        ]);

        // Create main module task
        $moduleTask = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Pengerjaan Modul: Module Beta',
            'status' => 'sedang_dikerjakan',
            'user_id' => $user->id,
        ]);

        // Create other task inside the module
        $subTask = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Sub Task Beta 1',
            'status' => 'belum_dikerjakan',
            'user_id' => null,
        ]);

        // Approve the module task
        $this->actingAs($admin)->post(route('admin.project.task.approve', $moduleTask))->assertRedirect();

        // Both tasks must now be marked as selesai!
        $moduleTask->refresh();
        $subTask->refresh();

        $this->assertSame('selesai', $moduleTask->status);
        $this->assertSame('selesai', $subTask->status);
    }

    public function test_approving_sub_task_does_not_complete_module_tasks(): void
    {
        MasterData::seedDefaults();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $admin->id,
            'nama' => 'Project Auto Finish 2',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'aktif',
        ]);

        $module = ProjectModule::create([
            'project_id' => $project->id,
            'nama' => 'Module Gamma',
            'bobot' => 100,
        ]);

        $moduleTask = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Pengerjaan Modul: Module Gamma',
            'status' => 'sedang_dikerjakan',
            'user_id' => $user->id,
        ]);

        $subTask = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'judul' => 'Sub Task Gamma 1',
            'status' => 'sedang_dikerjakan',
            'user_id' => $user->id,
        ]);

        // Approve the sub task (not the module task)
        $this->actingAs($admin)->post(route('admin.project.task.approve', $subTask))->assertRedirect();

        $moduleTask->refresh();
        $subTask->refresh();

        // Subtask is completed, but module level task remains in progress!
        $this->assertSame('sedang_dikerjakan', $moduleTask->status);
        $this->assertSame('selesai', $subTask->status);
    }

    public function test_weekly_schedule_dual_views_and_manual_team_editing(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $bidang = Bidang::create(['nama' => 'Mobile Dev']);
        $pembimbing = PembimbingMagang::create(['nama' => 'Budi Santoso', 'bidang_id' => $bidang->id]);

        $user1 = User::factory()->create([
            'nama' => 'Peserta Satu',
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
            'bidang_id' => $bidang->id,
            'pembimbing_magang_id' => $pembimbing->id,
        ]);

        $user2 = User::factory()->create([
            'nama' => 'Peserta Dua',
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'B',
            'bidang_id' => $bidang->id,
            'pembimbing_magang_id' => $pembimbing->id,
        ]);

        // 1. Superadmin can access Jadwal tab
        $response = $this->actingAs($superadmin)->get(route('admin.dashboard', ['tab' => 'jadwal']));
        $response->assertOk()
            ->assertSee('Tampilan Perorangan')
            ->assertSee('Tampilan Tim')
            ->assertSee('Peserta Satu')
            ->assertSee('Peserta Dua');

        // 2. Superadmin can update team assignment (master names remain intact)
        $updateResponse = $this->actingAs($superadmin)->post(route('admin.jadwal.team.members'), [
            'members' => [
                $user1->id => [
                    'grup' => 'B',
                ],
                $user2->id => [
                    'grup' => 'A',
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team']));

        $user1->refresh();
        $user2->refresh();

        // Master names preserved, groups updated
        $this->assertSame('Peserta Satu', $user1->nama);
        $this->assertSame('B', $user1->grup);

        $this->assertSame('Peserta Dua', $user2->nama);
        $this->assertSame('A', $user2->grup);
    }

    public function test_normal_schedule_randomization_uses_alternating_pairs_and_friday_wfh(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $users = User::factory()->count(4)->create([
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.jadwal.random'));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal']));

        foreach ($users as $user) {
            $user->refresh();
            $jadwal = $user->jadwalMingguan;

            $this->assertNotNull($jadwal);
            // Alternating pairs: Senin == Rabu, Selasa == Kamis, Senin != Selasa
            $this->assertSame($jadwal->senin, $jadwal->rabu);
            $this->assertSame($jadwal->selasa, $jadwal->kamis);
            $this->assertNotSame($jadwal->senin, $jadwal->selasa);
            // Friday always WFH
            $this->assertSame('wfh', $jadwal->jumat);
        }
    }

    public function test_team_schedule_randomization_applies_collectively_per_team(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $teamAUsers = User::factory()->count(2)->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
        ]);

        $teamBUsers = User::factory()->count(2)->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'B',
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.jadwal.team.random'));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal']));

        $teamAFirstSchedule = null;
        foreach ($teamAUsers as $user) {
            $user->refresh();
            $jadwal = $user->jadwalMingguan;
            $this->assertNotNull($jadwal);
            $this->assertSame('wfh', $jadwal->jumat);

            if ($teamAFirstSchedule === null) {
                $teamAFirstSchedule = $jadwal;
            } else {
                // All Team A members must have identical schedule (serentak)
                $this->assertSame($teamAFirstSchedule->senin, $jadwal->senin);
                $this->assertSame($teamAFirstSchedule->selasa, $jadwal->selasa);
                $this->assertSame($teamAFirstSchedule->rabu, $jadwal->rabu);
                $this->assertSame($teamAFirstSchedule->kamis, $jadwal->kamis);
            }
        }

        $teamBFirstSchedule = null;
        foreach ($teamBUsers as $user) {
            $user->refresh();
            $jadwal = $user->jadwalMingguan;
            $this->assertNotNull($jadwal);
            $this->assertSame('wfh', $jadwal->jumat);

            if ($teamBFirstSchedule === null) {
                $teamBFirstSchedule = $jadwal;
            } else {
                // All Team B members must have identical schedule (serentak)
                $this->assertSame($teamBFirstSchedule->senin, $jadwal->senin);
                $this->assertSame($teamBFirstSchedule->selasa, $jadwal->selasa);
                $this->assertSame($teamBFirstSchedule->rabu, $jadwal->rabu);
                $this->assertSame($teamBFirstSchedule->kamis, $jadwal->kamis);
            }
        }

        // Team A and Team B must have complementary schedules
        $this->assertNotSame($teamAFirstSchedule->senin, $teamBFirstSchedule->senin);
        $this->assertNotSame($teamAFirstSchedule->selasa, $teamBFirstSchedule->selasa);
    }

    public function test_team_members_randomization_balances_teams_evenly(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        User::factory()->count(6)->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.jadwal.team.random_members'));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team']));

        $users = User::where('role', 'user')->get();
        $teamACount = $users->where('grup', 'A')->count();
        $teamBCount = $users->where('grup', 'B')->count();

        $this->assertSame(3, $teamACount);
        $this->assertSame(3, $teamBCount);
    }

    public function test_superadmin_can_add_custom_team_and_assign_members(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
        ]);

        // 1. Add new Team "C"
        $storeTeamResponse = $this->actingAs($superadmin)->post(route('admin.jadwal.team.store'), [
            'nama_tim' => 'C',
        ]);
        $storeTeamResponse->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team']));

        $availableTeams = AdminController::getAvailableTeams();
        $this->assertContains('C', $availableTeams);

        // 2. Assign user to Team "C"
        $updateResponse = $this->actingAs($superadmin)->post(route('admin.jadwal.team.members'), [
            'members' => [
                $user->id => [
                    'grup' => 'C',
                ],
            ],
        ]);
        $updateResponse->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team']));

        $user->refresh();
        $this->assertSame('C', $user->grup);

        // 3. Team view renders Team C
        $dashboardResponse = $this->actingAs($superadmin)->get(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'team']));
        $dashboardResponse->assertOk()
            ->assertSee('Tim C')
            ->assertSee('KELOMPOK TIM C');
    }

    public function test_superadmin_can_update_team_assignment_via_ajax(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
        ]);

        $response = $this->actingAs($superadmin)->postJson(route('admin.jadwal.team.members'), [
            'members' => [
                $user->id => [
                    'grup' => 'B',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'teamCounts',
            ]);

        $user->refresh();
        $this->assertSame('B', $user->grup);
    }

    public function test_superadmin_can_update_landing_schedule_view_setting(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.jadwal.landing_view'), [
            'jadwal_landing_view' => 'team',
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'jadwal', 'view' => 'normal']));
        $this->assertSame('team', DB::table('md_pengaturan')->where('kunci', 'jadwal_landing_view')->value('nilai'));

        // Test JSON request
        $responseJson = $this->actingAs($superadmin)->postJson(route('admin.jadwal.landing_view'), [
            'jadwal_landing_view' => 'individual',
        ]);

        $responseJson->assertOk()
            ->assertJson([
                'success' => true,
                'mode' => 'individual',
            ]);
        $this->assertSame('individual', DB::table('md_pengaturan')->where('kunci', 'jadwal_landing_view')->value('nilai'));
    }

    public function test_admin_bidang_can_update_own_landing_schedule_view_setting(): void
    {
        $bidang = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'status_akun' => 'aktif',
            'bidang_id' => $bidang->id,
            'bidang_magang' => $bidang->nama,
        ]);

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'jadwal']));
        $dashboard->assertOk()
            ->assertSee('Jadwal Mingguan')
            ->assertSee('Pengaturan Tampilan Jadwal di Halaman Utama Peserta');

        $response = $this->actingAs($admin)->post(route('admin.jadwal.landing_view'), [
            'jadwal_landing_view' => 'team',
        ]);

        $response->assertRedirect(route('admin.dashboard', [
            'tab' => 'jadwal',
            'view' => 'normal',
            'bidang_id' => $bidang->id,
        ]));

        $this->assertSame(
            'team',
            DB::table('md_pengaturan')
                ->where('kunci', 'jadwal_landing_view_bidang_'.$bidang->id)
                ->value('nilai')
        );
    }

    public function test_home_displays_schedule_with_team_and_individual_modes(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status_akun' => 'aktif',
            'grup' => 'A',
            'nama' => 'Test Participant Team A',
        ]);
        $user->jadwalMingguan()->create([
            'senin' => 'wfo',
            'selasa' => 'wfh',
            'rabu' => 'wfo',
            'kamis' => 'wfh',
            'jumat' => 'wfh',
        ]);

        // When setting is 'team'
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => 'team', 'updated_at' => now(), 'created_at' => now()]
        );

        $responseTeam = $this->get(route('home'));
        $responseTeam->assertOk();
        $responseTeam->assertSee('Jadwal Kerja Minggu Ini');
        $responseTeam->assertSee('Tampilan Tim');
        $responseTeam->assertSee('KELOMPOK TIM A');
        $responseTeam->assertSee('Test Participant Team A');

        // When setting is 'individual'
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => 'individual', 'updated_at' => now(), 'created_at' => now()]
        );

        $responseIndiv = $this->get(route('home'));
        $responseIndiv->assertOk();
        $responseIndiv->assertSee('Jadwal Kerja Minggu Ini');
        $responseIndiv->assertSee('Tampilan Perorangan');
        $responseIndiv->assertDontSee('KELOMPOK TIM A');
        $responseIndiv->assertSee('Test Participant Team A');
    }

    public function test_home_displays_schedule_modes_per_bidang(): void
    {
        $bidangAplikasi = Bidang::firstOrCreate(['nama' => 'Bidang Aplikasi Informatika']);
        $bidangInfrastruktur = Bidang::firstOrCreate(['nama' => 'Bidang Infrastruktur Teknologi']);

        $aplikasiUser = User::factory()->create([
            'nama' => 'Aplikasi Mixed Mode Intern',
            'email' => 'aplikasi.mixed@example.test',
            'bidang_id' => $bidangAplikasi->id,
            'bidang_magang' => $bidangAplikasi->nama,
            'grup' => 'A',
            'role' => 'user',
        ]);
        $aplikasiUser->jadwalMingguan()->create([
            'senin' => 'wfo',
            'selasa' => 'wfh',
            'rabu' => 'wfo',
            'kamis' => 'wfh',
            'jumat' => 'wfh',
        ]);

        $infrastrukturUser = User::factory()->create([
            'nama' => 'Infrastruktur Mixed Mode Intern',
            'email' => 'infrastruktur.mixed@example.test',
            'bidang_id' => $bidangInfrastruktur->id,
            'bidang_magang' => $bidangInfrastruktur->nama,
            'grup' => 'B',
            'role' => 'user',
        ]);
        $infrastrukturUser->jadwalMingguan()->create([
            'senin' => 'wfh',
            'selasa' => 'wfo',
            'rabu' => 'wfh',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ]);

        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'jadwal_landing_view'],
            ['nilai' => 'individual', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('md_pengaturan')->updateOrInsert(
            ['kunci' => 'jadwal_landing_view_bidang_'.$bidangAplikasi->id],
            ['nilai' => 'team', 'updated_at' => now(), 'created_at' => now()]
        );

        $response = $this->get(route('home'));
        $response->assertOk()
            ->assertSee('Sesuai Bidang')
            ->assertSee('Aplikasi Mixed Mode Intern')
            ->assertSee('Infrastruktur Mixed Mode Intern')
            ->assertSee('KELOMPOK TIM A')
            ->assertDontSee('KELOMPOK TIM B');
    }

    private function fakePng(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'test_png_');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
