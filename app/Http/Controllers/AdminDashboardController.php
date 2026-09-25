<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
            'stock' => Product::sum('stock'),
        ];

        $latestProducts = Product::with(['category', 'brand'])->latest()->take(5)->get();

        $chartData = Category::with('products')
            ->get()
            ->map(function ($category) {
                return [
                    'label' => $category->name,
                    'value' => $category->products->sum('stock'),
                ];
            })
            ->filter(fn ($item) => $item['value'] > 0)
            ->values();

        $maxChartValue = $chartData->max('value') ?? 0;

        return view('dashboard', compact('stats', 'latestProducts', 'chartData', 'maxChartValue'));
    }
}
