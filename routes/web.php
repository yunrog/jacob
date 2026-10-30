<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/sales', [CashierController::class, 'store'])->name('cashier.sales.store');
    Route::get('/cashier/sales/{sale}/receipt', [ReceiptController::class, 'show'])->name('cashier.sales.receipt');
    Route::resource('products', ProductController::class)->only(['index', 'create', 'store']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class)->except(['index', 'create', 'store']);
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('/admin', fn () => redirect()->route('dashboard'))->name('admin');
});

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'kasir'
        ? redirect()->route('cashier.index')
        : redirect()->route('dashboard');
});
