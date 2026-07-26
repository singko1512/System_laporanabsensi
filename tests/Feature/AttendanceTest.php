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
}
