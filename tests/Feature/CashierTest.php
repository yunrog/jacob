<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
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
        $user = User::factory()->create(['role' => 'kasir']);
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

    public function test_cashier_has_a_working_add_product_menu(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->get('/cashier')
            ->assertOk()
            ->assertSee('Tambah Barang')
            ->assertSee(route('products.create', ['return_to' => 'cashier']));

        $this->get(route('products.create', ['return_to' => 'cashier']))
            ->assertOk()
            ->assertSee('name="return_to" value="cashier"', false)
            ->assertSee('Simpan Barang');
    }

    public function test_product_created_from_cashier_returns_ready_to_sell(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->post(route('products.store'), [
                'name' => 'Beras Premium',
                'price' => 75000,
                'stock' => 8,
                'return_to' => 'cashier',
            ]);

        $response->assertRedirect(route('cashier.index'));
        $response->assertSessionHas('success', 'Barang berhasil ditambahkan dan siap dijual.');
        $this->assertDatabaseHas('products', [
            'name' => 'Beras Premium',
            'stock' => 8,
        ]);

        $this->get(route('cashier.index'))
            ->assertOk()
            ->assertSee('Beras Premium')
            ->assertSee('Barang berhasil ditambahkan dan siap dijual.');
    }

    public function test_cashier_can_complete_a_sale_and_decrease_stock(): void
    {
        $user = User::factory()->create(['name' => 'Kasir Test', 'role' => 'kasir']);
        $product = Product::create([
            'name' => 'Indomie Goreng',
            'price' => 3500,
            'stock' => 12,
        ]);

        $response = $this->actingAs($user)->postJson('/cashier/sales', [
            'items' => [['id' => $product->id, 'qty' => 2]],
            'discount_percent' => 10,
            'discount_amount' => 0,
            'tax' => 500,
            'other_fee' => 0,
            'amount_paid' => 7000,
            'payment_method' => 'Tunai',
            'customer_type' => 'Umum',
            'customer_name' => 'Budi Santoso',
            'customer_contact' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('sale.subtotal', 7000)
            ->assertJsonPath('sale.discount_amount', 700)
            ->assertJsonPath('sale.total', 6800)
            ->assertJsonPath('sale.change_amount', 200)
            ->assertJsonPath('sale.customer_name', 'Budi Santoso')
            ->assertJsonPath('sale.receipt_url', route('cashier.sales.receipt', Sale::query()->value('id')));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);
        $this->assertDatabaseHas('sales', [
            'cashier_id' => $user->id,
            'customer_name' => 'Budi Santoso',
            'subtotal' => 7000,
            'total' => 6800,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'subtotal' => 7000,
        ]);
    }

    public function test_cashier_can_open_only_their_own_printable_receipt(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $otherCashier = User::factory()->create(['role' => 'kasir']);
        $sale = Sale::create([
            'transaction_number' => 'TRX-TEST-RECEIPT',
            'cashier_id' => $cashier->id,
            'cashier_name' => $cashier->name,
            'customer_type' => 'Umum',
            'customer_name' => 'Budi Santoso',
            'subtotal' => 3500,
            'discount_amount' => 0,
            'tax' => 0,
            'other_fee' => 0,
            'total' => 3500,
            'amount_paid' => 5000,
            'change_amount' => 1500,
            'payment_method' => 'Tunai',
        ]);
        $sale->items()->create([
            'product_name' => 'Beras Premium',
            'quantity' => 1,
            'unit_price' => 3500,
            'subtotal' => 3500,
        ]);

        $this->actingAs($cashier)
            ->get(route('cashier.sales.receipt', $sale))
            ->assertOk()
            ->assertSee('Beras Premium')
            ->assertSee('TRX-TEST-RECEIPT')
            ->assertSee('MENIA')
            ->assertSee('Nama pelanggan: Budi Santoso')
            ->assertSee('Cetak Struk')
            ->assertSee('receipt-end', false)
            ->assertSee('id="printReceipt"', false)
            ->assertSee("addEventListener('click'", false)
            ->assertSee('@page { size: 80mm ${pageHeightMm}mm; margin: 0; }', false)
            ->assertSee('getBoundingClientRect().height', false)
            ->assertSee('Math.max(81, Math.ceil(', false)
            ->assertSee('width: 66mm', false)
            ->assertSee('Arial, Helvetica, sans-serif', false)
            ->assertSee('font-size: 9.5pt', false)
            ->assertSee('font-weight: 700', false)
            ->assertSee('print-color-adjust: exact', false)
            ->assertSee('position: fixed !important', false)
            ->assertSee('top: 0 !important', false)
            ->assertSee('max-height: ${pageHeightMm}mm !important', false);

        $this->actingAs($otherCashier)
            ->get(route('cashier.sales.receipt', $sale))
            ->assertForbidden();
    }

    public function test_cashier_cannot_sell_more_than_available_stock(): void
    {
        $product = Product::create([
            'name' => 'Barang Stok Terbatas',
            'price' => 2500,
            'stock' => 1,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->postJson('/cashier/sales', [
                'items' => [['id' => $product->id, 'qty' => 2]],
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax' => 0,
                'other_fee' => 0,
                'amount_paid' => 5000,
                'payment_method' => 'Tunai',
                'customer_type' => 'Umum',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_cashier_cannot_complete_an_underpaid_sale(): void
    {
        $product = Product::create([
            'name' => 'Barang Pembayaran Kurang',
            'price' => 2500,
            'stock' => 3,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->postJson('/cashier/sales', [
                'items' => [['id' => $product->id, 'qty' => 1]],
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax' => 0,
                'other_fee' => 0,
                'amount_paid' => 2000,
                'payment_method' => 'QRIS',
                'customer_type' => 'Umum',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount_paid');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }
}
