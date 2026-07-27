<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default Admin PIN in memory database for testing.
        Pengaturan::updateOrCreate(
            ['kunci' => 'pin_admin'],
            ['nilai' => Hash::make('180909')]
        );
    }

    /**
     * Test user routes can be accessed.
     */
    public function test_user_routes_can_be_accessed(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response = $this->get(route('absensi.form'));
        $response->assertRedirect(route('absensi.index', ['tab' => 'form']));

        $response = $this->get(route('absensi.rekap'));
        $response->assertRedirect(route('absensi.index', ['tab' => 'rekap']));
    }

    /**
     * Test admin dashboard redirects to home if unauthenticated.
     */
    public function test_admin_dashboard_redirects_if_unauthenticated(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * Test admin authentication with correct PIN.
     */
    public function test_admin_can_login_with_correct_pin(): void
    {
        $response = $this->post(route('admin.login'), [
            'pin' => '180909',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('admin_authenticated', true);
    }

    /**
     * Test admin authentication fails with incorrect PIN.
     */
    public function test_admin_cannot_login_with_incorrect_pin(): void
    {
        $response = $this->post(route('admin.login'), [
            'pin' => 'wrongpin',
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
        // Authenticate admin session
        $this->withSession(['admin_authenticated' => true]);

        // 1. Create User
        $response = $this->post(route('admin.user.store'), [
            'nama' => 'John Doe',
            'email' => 'john@example.test',
            'pembimbing_magang' => 'Rina Kartika',
            'bidang_magang' => 'Backend Developer',
            'tanggal_mulai_magang' => '2026-01-01',
            'tanggal_selesai_magang' => '2026-06-30',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseHas('md_user', [
            'nama' => 'John Doe',
            'email' => 'john@example.test',
            'pembimbing_magang' => 'Rina Kartika',
            'bidang_magang' => 'Backend Developer',
        ]);

        $user = User::where('email', 'john@example.test')->first();

        // 2. Update User
        $response = $this->post(route('admin.user.update', $user->id), [
            'nama' => 'John Doe Edited',
            'email' => 'john.edited@example.test',
            'pembimbing_magang' => 'Dimas Prakoso',
            'bidang_magang' => 'Quality Assurance',
            'tanggal_mulai_magang' => '2026-02-01',
            'tanggal_selesai_magang' => '2026-07-31',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseHas('md_user', [
            'id' => $user->id,
            'nama' => 'John Doe Edited',
            'email' => 'john.edited@example.test',
            'pembimbing_magang' => 'Dimas Prakoso',
            'bidang_magang' => 'Quality Assurance',
        ]);

        // 3. Delete User
        $response = $this->get(route('admin.user.destroy', $user->id));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'pegawai']));
        $this->assertDatabaseMissing('md_user', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test WFH attendance does not require camera photo.
     */
    public function test_wfh_attendance_does_not_require_camera_photo(): void
    {
        $user = User::create([
            'nama' => 'Alice Intern',
            'email' => 'alice@example.test',
            'bidang_magang' => 'Backend Developer',
        ]);

        \App\Models\MasterData::seedDefaults();

        $response = $this->post(route('absensi.store'), [
            'user_id' => $user->id,
            'status' => 'wfh',
            'lokasi_latitude' => -6.2087,
            'lokasi_longitude' => 106.8456,
            'laporan' => 'Working on backend tasks',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('md_absensi', [
            'user_id' => $user->id,
            'status_id' => \App\Models\MasterData::idFor(\App\Models\MasterData::ABSENSI_STATUS, 'wfh'),
            'laporan' => 'Working on backend tasks',
        ]);
    }

    /**
     * Test Hadir attendance still requires camera photo.
     */
    public function test_hadir_attendance_still_requires_camera_photo(): void
    {
        $user = User::create([
            'nama' => 'Bob Intern',
            'email' => 'bob@example.test',
            'bidang_magang' => 'Frontend Developer',
        ]);

        \App\Models\MasterData::seedDefaults();

        $response = $this->post(route('absensi.store'), [
            'user_id' => $user->id,
            'status' => 'hadir',
            'laporan' => 'Working on frontend tasks',
        ]);

        $response->assertSessionHasErrors(['foto_kamera']);
    }

    /**
     * Test note completion flow (user completion and admin confirmation).
     */
    public function test_note_completion_flow(): void
    {
        $user = User::create([
            'nama' => 'Charlie Intern',
            'email' => 'charlie@example.test',
            'bidang_magang' => 'Quality Assurance',
        ]);

        \App\Models\MasterData::seedDefaults();

        $project = \App\Models\Project::create([
            'user_id' => $user->id,
            'nama' => 'QA Project',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'status' => 'aktif',
        ]);

        $note = \App\Models\ProjectNote::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'tanggal' => now()->toDateString(),
            'kategori' => 'sedang',
            'judul' => 'Write test cases',
        ]);

        // User marks it complete
        $response = $this->post(route('timeline.note.complete', $note->id), [
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $note->refresh();
        $this->assertNotNull($note->user_selesai_pada);
        $this->assertNull($note->selesai_pada);

        // Admin marks/confirms it complete
        $response2 = $this->withSession(['admin_authenticated' => true])
            ->post(route('timeline.note.complete', $note->id));

        $response2->assertRedirect();
        $note->refresh();
        $this->assertNotNull($note->user_selesai_pada);
        $this->assertNotNull($note->selesai_pada);
    }

    /**
     * Test admin can perform CRUD operations on divisions (Bidang).
     */
    public function test_admin_can_crud_bidang(): void
    {
        $this->withSession(['admin_authenticated' => true]);

        // 1. Create Bidang
        $response = $this->post(route('admin.bidang.store'), [
            'nama' => 'Mobile Developer',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_bidang', [
            'nama' => 'Mobile Developer',
        ]);

        $bidang = \App\Models\Bidang::where('nama', 'Mobile Developer')->first();

        // 2. Update Bidang
        $response = $this->post(route('admin.bidang.update', $bidang->id), [
            'nama' => 'Mobile Apps Developer',
        ]);
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseHas('md_bidang', [
            'id' => $bidang->id,
            'nama' => 'Mobile Apps Developer',
        ]);

        // 3. Destroy Bidang
        $response = $this->get(route('admin.bidang.destroy', $bidang->id));
        $response->assertRedirect(route('admin.dashboard', ['tab' => 'bidang']));
        $this->assertDatabaseMissing('md_bidang', [
            'id' => $bidang->id,
        ]);
    }
}
