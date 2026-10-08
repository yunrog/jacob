<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Request $request, Sale $sale): View
    {
        abort_unless(
            $request->user()->role === 'admin' || $sale->cashier_id === $request->user()->id,
            403
        );

        $sale->load('items');

        return view('cashier.receipt', compact('sale'));
    }
}
