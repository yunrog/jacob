<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@jacob.test',
            'password' => Hash::make('admin123'),
        ]);
    }

    public function test_products_index_displays_products(): void
    {
        Product::create([
            'name' => 'Laptop',
            'price' => 15000000,
            'stock' => 10,
        ]);

        $this->actingAs(User::first());

        $response = $this->get('/products');

        $response->assertStatus(200)
            ->assertSee('Laptop');
    }

    public function test_user_can_create_a_product(): void
    {
        $this->actingAs(User::first());

        $response = $this->post('/products', [
            'name' => 'Mouse',
            'price' => 250000,
            'stock' => 25,
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Mouse',
            'price' => 250000,
            'stock' => 25,
        ]);
    }

    public function test_user_can_upload_product_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::first());

        $response = $this->post('/products', [
            'name' => 'Keyboard',
            'price' => 600000,
            'stock' => 15,
            'image' => UploadedFile::fake()->create('keyboard.jpg', 50, 'image/jpeg'),
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Keyboard',
            'price' => 600000,
            'stock' => 15,
        ]);
        $this->assertDatabaseHas('products', [
            'image' => 'products/' . basename(Product::first()->image),
        ]);
    }

    public function test_products_can_be_filtered_by_keyword(): void
    {
        Product::create([
            'name' => 'Gaming Mouse',
            'price' => 450000,
            'stock' => 10,
        ]);

        Product::create([
            'name' => 'Office Chair',
            'price' => 900000,
            'stock' => 5,
        ]);

        $this->actingAs(User::first());

        $response = $this->get('/products?search=gaming');

        $response->assertStatus(200)
            ->assertSee('Gaming Mouse')
            ->assertDontSee('Office Chair');
    }

    public function test_root_redirects_to_dashboard_for_authenticated_user(): void
    {
        $this->actingAs(User::first());

        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }
}
