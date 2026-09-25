<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_requires_authentication(): void
    {
        $response = $this->get('/cashier');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_cashier_displays_available_products(): void
    {
        $user = User::factory()->create();
        Product::create([
            'name' => 'Indomie Goreng',
            'price' => 3500,
            'stock' => 12,
        ]);
        Product::create([
            'name' => 'Out of Stock Product',
            'price' => 1000,
            'stock' => 0,
        ]);

        $response = $this->actingAs($user)->get('/cashier');

        $response->assertOk()
            ->assertSee('Kasir Retail POS')
            ->assertSee('Indomie Goreng')
            ->assertDontSee('Out of Stock Product');
    }
}
