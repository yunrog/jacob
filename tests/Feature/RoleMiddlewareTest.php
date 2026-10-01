<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_route(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_user_without_required_role_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden()
            ->assertSeeText('ANDA TIDAK MEMILIKI AKSES.');
    }

    public function test_user_with_required_role_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Halaman Admin');
    }

    public function test_database_seeder_assigns_admin_role_to_demo_admin(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@jacob.test',
            'role' => 'admin',
        ]);
    }
}