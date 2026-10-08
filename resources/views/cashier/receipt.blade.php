<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $sale->transaction_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #f1f5f9; font: 11px/1.25 Arial, sans-serif; }
        .receipt { width: 72mm; margin: 24px auto; padding: 3mm; background: #fff; }
        .center { text-align: center; }
        .muted { color: #444; }
        h1 { margin: 0; font-size: 16px; }
        p { margin: 1px 0; overflow-wrap: anywhere; }
        hr { margin: 4px 0; border: 0; border-top: 1px dashed #555; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { padding: 1px 0; vertical-align: top; overflow-wrap: anywhere; }
        td:last-child { width: 25mm; padding-left: 1mm; text-align: right; overflow-wrap: anywhere; }
        .item-detail td { padding: 0 0 2px; color: #444; font-size: 10px; }
        .totals p { display: flex; justify-content: space-between; gap: 2mm; }
        .totals p span, .totals p strong { min-width: 0; overflow-wrap: anywhere; }
        .totals p span:last-child, .totals p strong:last-child { text-align: right; }
        .totals strong { font-size: 12px; }
        .receipt-footer { margin-top: 4px; }
        .receipt-end { height: 1mm; margin-top: 2mm; border-top: 1px dashed #111; }
        .screen-actions { display: flex; justify-content: center; gap: 8px; margin: 0 auto 24px; }
        .screen-actions a, .screen-actions button { padding: 9px 14px; border: 1px solid #cbd5e1; border-radius: 7px; color: #111; background: #fff; font: inherit; text-decoration: none; cursor: pointer; }
        body.print-layout { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 80mm; max-width: 80mm; margin: 0; padding: 0; overflow-x: hidden; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; font-weight: 500; line-height: 1.25; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body.print-layout .receipt, body.print-layout .receipt * { color: #000 !important; }
        body.print-layout hr, body.print-layout .receipt-end { border-color: #000; }
        body.print-layout .receipt { flex: 0 0 auto; width: 66mm; margin: 0; padding: 2mm 0 0; }
        body.print-layout h1 { font-size: 14pt; }
        body.print-layout .item-detail td { font-size: 8.5pt; }
        body.print-layout .totals strong { font-size: 10pt; font-weight: 700; }
        body.print-layout .screen-actions { display: none; }
        @media print {
            html, body { width: 80mm; max-width: 80mm; margin: 0; padding: 0; overflow-x: hidden; background: #fff; }
            body { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; }
            body { color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; font-weight: 500; line-height: 1.25; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .receipt, .receipt * { color: #000 !important; }
            hr, .receipt-end { border-color: #000; }
            .receipt { flex: 0 0 auto; width: 66mm; margin: 0; padding: 2mm 0 0; }
            h1 { font-size: 14pt; }
            .item-detail td { font-size: 8.5pt; }
            .totals strong { font-size: 10pt; font-weight: 700; }
            .screen-actions { display: none !important; }
            tr, .totals, .sale-info { break-inside: avoid; page-break-inside: avoid; }
            p, td { overflow-wrap: anywhere; }
        }
    </style>
</head>
<body>
    <main class="receipt">
        <header class="center">
            <h1>MENIA</h1>
            <p>jalan menia km 7</p>
        </header>

        <hr>
        <section class="sale-info">
            <p>{{ $sale->transaction_number }} · {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            <p>Kasir: {{ $sale->cashier_name }} · {{ $sale->customer_type }}</p>
            @if ($sale->customer_name)
                <p>Nama pelanggan: {{ $sale->customer_name }}</p>
            @endif
            @if ($sale->customer_contact)
                <p>Kontak: {{ $sale->customer_contact }}</p>
            @endif
        </section>
        <hr>

        <table aria-label="Rincian barang">
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="item-detail">
                        <td colspan="2">{{ $item->quantity }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
        <section class="totals">
            <p><span>Subtotal</span><span>Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</span></p>
            @if ((float) $sale->discount_amount > 0)
                <p><span>Diskon</span><span>Rp {{ number_format((float) $sale->discount_amount, 0, ',', '.') }}</span></p>
            @endif
            @if ((float) $sale->tax > 0)
                <p><span>Pajak / PPN</span><span>Rp {{ number_format((float) $sale->tax, 0, ',', '.') }}</span></p>
            @endif
            @if ((float) $sale->other_fee > 0)
                <p><span>Biaya lain</span><span>Rp {{ number_format((float) $sale->other_fee, 0, ',', '.') }}</span></p>
            @endif
            <hr>
            <p><strong>TOTAL</strong><strong>Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</strong></p>
            <p><span>Bayar</span><span>Rp {{ number_format((float) $sale->amount_paid, 0, ',', '.') }}</span></p>
            <p><span>Kembali</span><span>Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</span></p>
            <p><span>Metode</span><span>{{ $sale->payment_method }}</span></p>
        </section>
        <p class="center receipt-footer">Terima kasih telah berbelanja.</p>
        <div class="receipt-end" aria-label="Batas akhir struk"></div>
    </main>

    <nav class="screen-actions" aria-label="Aksi struk">
        <button type="button" id="printReceipt">Cetak Struk</button>
        <a href="{{ route('cashier.index') }}">Kembali ke Kasir</a>
    </nav>

    <script>
        const printButton = document.getElementById('printReceipt');
        printButton.addEventListener('click', () => {
            document.body.classList.add('print-layout');
            const receipt = document.querySelector('.receipt');
            const pageHeightMm = Math.max(81, Math.ceil(receipt.getBoundingClientRect().height * 25.4 / 96) + 1);
            const printStyle = document.createElement('style');
            printStyle.textContent = `
                @page { size: 80mm ${pageHeightMm}mm; margin: 0; }
                @media print {
                    html, body {
                        width: 80mm !important;
                        height: ${pageHeightMm}mm !important;
                        min-height: 0 !important;
                        max-height: ${pageHeightMm}mm !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        overflow: hidden !important;
                    }
                    body {
                        display: block !important;
                    }
                    body .receipt {
                        position: fixed !important;
                        top: 0 !important;
                        left: 50% !important;
                        transform: translateX(-50%) !important;
                        width: 66mm !important;
                        height: auto !important;
                        max-height: none !important;
                        margin: 0 !important;
                        padding: 2mm 0 0 !important;
                        overflow: visible !important;
                    }
                }
            `;
            document.head.append(printStyle);

            window.addEventListener('afterprint', () => {
                document.body.classList.remove('print-layout');
                printStyle.remove();
            }, { once: true });

            window.print();
        });
    </script>
</body>
</html>
