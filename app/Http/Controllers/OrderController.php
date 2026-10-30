<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function invoice($id)
    {
        // Buat objek dummy/mock data invoice secara langsung tanpa query ke database
        $invoice = (object) [
            'id' => $id,
            'created_at' => now(),
            'total' => 150000,
            'items' => collect([
                (object) [
                    'product' => (object) ['name' => 'Sepatu Casual Sneaker'],
                    'price' => 100000,
                    'quantity' => 1,
                    'total' => 100000,
                ],
                (object) [
                    'product' => (object) ['name' => 'Kaos Polos Cotton Combed'],
                    'price' => 50000,
                    'quantity' => 1,
                    'total' => 50000,
                ]
            ])
        ];

        // Generate PDF dari view invoices.invoice
        $pdf = Pdf::loadView('invoices.invoice', ['invoice' => $invoice]);

        // Unduh file PDF
        return $pdf->download('invoice_' . $id . '.pdf');
    }
}