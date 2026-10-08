<?php

namespace Tests\Feature;

use App\Models\Product;
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
            ->assertRedirect('/dashboard');
    }

    public function test_cashier_is_limited_to_pos_and_product_entry(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'name' => 'Produk Test',
            'price' => 1000,
            'stock' => 1,
        ]);
        $this->actingAs($cashier);

        $this->get('/cashier')->assertOk();
        $this->get('/products')->assertOk()->assertDontSee('Delete');
        $this->get('/products/create')->assertOk();
        $this->get('/dashboard')->assertForbidden();
        $this->get('/brands')->assertForbidden();
        $this->get('/categories')->assertForbidden();
        $this->get(route('products.edit', $product))->assertForbidden();
    }

    public function test_admin_can_access_dashboard_inventory_and_cashier(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::create([
            'name' => 'Produk Admin Test',
            'price' => 1500,
            'stock' => 2,
        ]);
        $this->actingAs($admin);

        $this->get('/dashboard')->assertOk();
        $this->get('/products')->assertOk()->assertSee('Delete');
        $this->get('/brands')->assertOk();
        $this->get('/categories')->assertOk();
        $this->get('/cashier')->assertOk();
    }

    public function test_database_seeder_assigns_admin_role_to_demo_admin(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@jacob.test',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kasir@jacob.test',
            'role' => 'kasir',
        ]);
    }

    public function test_cashier_login_redirects_to_pos(): void
    {
        User::factory()->create([
            'email' => 'kasir-login@jacob.test',
            'password' => 'password',
            'role' => 'kasir',
        ]);

        $this->post('/login', [
            'email' => 'kasir-login@jacob.test',
            'password' => 'password',
        ])->assertRedirect('/cashier');
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin-login@jacob.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => 'admin-login@jacob.test',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }

    public function test_cashier_can_log_out_and_return_to_login(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($cashier)
            ->from('/cashier')
            ->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/cashier')->assertRedirect('/login');
    }
}
