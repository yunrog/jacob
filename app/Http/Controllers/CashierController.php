<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
}
