<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\OrderPlaced;
use App\Models\User;

class OrderController extends Controller
{
    /**
     * Menampilkan halaman dashboard
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Memproses pembentukan transaksi dan memicu Event Broadcast (Langkah 2 BKPM)
     */
    public function store(Request $request)
    {
        // 1. Simulasi data transaksi (Order)
        $order = (object)[
            'id'    => rand(100, 999),
            'total' => $request->total ?? 150000
        ];

        // 2. Ambil user yang sedang login atau fallback ke User pertama
        $user = auth()->user() ?? User::first() ?? (object)['id' => 1, 'name' => 'Admin'];

        // 3. Panggil (Trigger) Event OrderPlaced ke Reverb
        event(new OrderPlaced($order, $user));

        // 4. Return respon JSON
        return response()->json([
            'status'  => 'success',
            'message' => 'Transaksi berhasil disimpan & event dipicu!',
            'order'   => $order
        ]);
    }
}