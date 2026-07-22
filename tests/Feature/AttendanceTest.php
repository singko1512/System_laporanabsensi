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

        // Seed default Admin PIN in memory database for testing
        Pengaturan::create([
            'kunci' => 'pin_admin',
            'nilai' => Hash::make('123456'),
        ]);
    }

    /**
     * Test user routes can be accessed.
     */
    public function test_user_routes_can_be_accessed(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response = $this->get(route('absensi.form'));
        $response->assertStatus(200);

        $response = $this->get(route('absensi.rekap'));
        $response->assertStatus(200);
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
            'pin' => '123456',
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
     * Test admin can perform CRUD operations on employees.
     */
    public function test_admin_can_crud_employees(): void
    {
        // Authenticate admin session
        $this->withSession(['admin_authenticated' => true]);

        // 1. Create User
        $response = $this->post(route('admin.user.store'), [
            'nama' => 'John Doe',
            'nip_atau_id' => '12345',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('md_user', [
            'nama' => 'John Doe',
            'nip_atau_id' => '12345',
        ]);

        $user = User::where('nip_atau_id', '12345')->first();

        // 2. Update User
        $response = $this->post(route('admin.user.update', $user->id), [
            'nama' => 'John Doe Edited',
            'nip_atau_id' => '12345-edited',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('md_user', [
            'id' => $user->id,
            'nama' => 'John Doe Edited',
            'nip_atau_id' => '12345-edited',
        ]);

        // 3. Delete User
        $response = $this->get(route('admin.user.destroy', $user->id));
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('md_user', [
            'id' => $user->id,
        ]);
    }
}
