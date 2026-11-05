<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Carbon\Carbon;

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

    /**
     * Cetak Struk Otomatis - Studi Kasus Toko Kopi Kita (Acara 36)
     */
    public function printKopiKita(Request $request)
    {
        try {
            // Gunakan DummyPrintConnector untuk simulasi pengujian tanpa printer fisik
            $connector = new DummyPrintConnector();
            $printer = new Printer($connector);

            // 1. Header Struk (Nama Toko & Tanggal/Waktu)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("TOKO KOPI KITA\n");
            $printer->setEmphasis(false);
            $printer->text("Jl. Pemuda No. 45, Jember\n");
            $printer->text("Waktu: " . Carbon::now()->format('d-m-Y H:i:s') . "\n");
            $printer->text("================================\n");

            // 2. Daftar Item, Kuantitas, dan Harga
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $items = [
                ['name' => 'Kopi Susu Aren', 'qty' => 2, 'price' => 18000],
                ['name' => 'Americano',     'qty' => 1, 'price' => 15000],
                ['name' => 'Croissant',     'qty' => 1, 'price' => 20000],
            ];

            $subtotal = 0;
            foreach ($items as $item) {
                $totalItem = $item['qty'] * $item['price'];
                $subtotal += $totalItem;

                $printer->text($item['name'] . "\n");
                $printer->text("  " . $item['qty'] . " x " . number_format($item['price'], 0, ',', '.') .
                               " = Rp " . number_format($totalItem, 0, ',', '.') . "\n");
            }

            $printer->text("--------------------------------\n");

            // 3. Hitung Diskon & Total Pembayaran
            $diskon = 5000;
            $totalBayar = $subtotal - $diskon;

            $printer->text("Subtotal:         Rp " . number_format($subtotal, 0, ',', '.') . "\n");
            $printer->text("Diskon:          -Rp " . number_format($diskon, 0, ',', '.') . "\n");

            $printer->setEmphasis(true);
            $printer->text("Total Bayar:      Rp " . number_format($totalBayar, 0, ',', '.') . "\n");
            $printer->setEmphasis(false);
            $printer->text("================================\n");

            // 4. Footer Struk
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Nikmati Kopi Anda Hari Ini!\n");
            $printer->text("Terima Kasih Atas Kunjungan Anda\n\n");

            // 5. Potong Kertas & Tutup Koneksi
            $printer->cut();
            $printer->close();

            return response()->json([
                'status'  => 'success',
                'message' => 'Struk Toko Kopi Kita berhasil dicetak (Simulasi/Testing Sukses)!',
                'data'    => [
                    'toko'        => 'Toko Kopi Kita',
                    'waktu'       => Carbon::now()->format('d-m-Y H:i:s'),
                    'subtotal'    => $subtotal,
                    'diskon'      => $diskon,
                    'total_bayar' => $totalBayar
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage()
            ], 500);
        }
    }
}