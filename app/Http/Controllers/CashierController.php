<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock', 'image'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'code' => sprintf('BRG%03d', $product->id),
                'barcode' => sprintf('899000000%03d', $product->id),
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => $product->stock,
                'image' => $product->image,
            ])
            ->values();

        return view('cashier.index', compact('products'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'other_fee' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Tunai,QRIS,Debit,Kredit,E-Wallet,Transfer'],
            'customer_type' => ['required', 'in:Umum,Pelanggan Member,Member VIP'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_contact' => ['nullable', 'string', 'max:50'],
        ]);

        $sale = DB::transaction(function () use ($request, $validated): Sale {
            $quantities = collect($validated['items'])
                ->mapWithKeys(fn (array $item) => [(int) $item['id'] => (int) $item['qty']]);
            $products = Product::query()
                ->whereIn('id', $quantities->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $quantities->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu barang sudah tidak tersedia. Muat ulang halaman kasir.',
                ]);
            }

            $subtotal = 0.0;
            $items = [];

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}.",
                    ]);
                }

                $unitPrice = round((float) $product->price, 2);
                $itemSubtotal = round($unitPrice * $quantity, 2);
                $subtotal += $itemSubtotal;
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $percentageDiscount = round($subtotal * (float) $validated['discount_percent'] / 100, 2);
            $discount = min(
                $subtotal,
                $percentageDiscount + (float) $validated['discount_amount']
            );
            $tax = round((float) $validated['tax'], 2);
            $otherFee = round((float) $validated['other_fee'], 2);
            $total = round(max(0, $subtotal - $discount + $tax + $otherFee), 2);
            $amountPaid = round((float) $validated['amount_paid'], 2);

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Uang pembayaran masih kurang dari total transaksi.',
                ]);
            }

            $sale = Sale::create([
                'transaction_number' => 'TRX-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'cashier_id' => $request->user()->id,
                'cashier_name' => $request->user()->name,
                'customer_type' => $validated['customer_type'],
                'customer_name' => isset($validated['customer_name']) ? trim($validated['customer_name']) : null,
                'customer_contact' => $validated['customer_contact'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => round($discount, 2),
                'tax' => $tax,
                'other_fee' => $otherFee,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change_amount' => round($amountPaid - $total, 2),
                'payment_method' => $validated['payment_method'],
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement('stock', $item['quantity']);
            }

            return $sale->load('items');
        });

        return response()->json([
            'sale' => [
                'transaction_number' => $sale->transaction_number,
                'created_at' => $sale->created_at->format('d/m/Y H:i'),
                'cashier_name' => $sale->cashier_name,
                'customer_type' => $sale->customer_type,
                'customer_name' => $sale->customer_name,
                'customer_contact' => $sale->customer_contact,
                'subtotal' => (float) $sale->subtotal,
                'discount_amount' => (float) $sale->discount_amount,
                'tax' => (float) $sale->tax,
                'other_fee' => (float) $sale->other_fee,
                'total' => (float) $sale->total,
                'amount_paid' => (float) $sale->amount_paid,
                'change_amount' => (float) $sale->change_amount,
                'payment_method' => $sale->payment_method,
                'receipt_url' => route('cashier.sales.receipt', $sale),
                'items' => $sale->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }
}
