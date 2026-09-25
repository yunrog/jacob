<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir Retail POS</title>
    <link rel="stylesheet" href="{{ asset('css/cashier.css') }}">
</head>
<body>
    <div class="pos-shell">
        <header class="pos-header">
            <div>
                <a class="brand-link" href="{{ route('dashboard') }}">MENIA</a>
                <div class="store-info">jalan menia km 7</div>
            </div>
            <div class="transaction-info">
                <span>No. Transaksi</span>
                <strong id="transactionNumber">TRX-{{ now()->format('Ymd') }}-001</strong>
                <time id="currentDate"></time>
            </div>
        </header>

        <main class="pos-main">
            <section class="workspace">
                <div class="card search-card">
                    <div class="card-heading">
                        <div>
                            <span class="eyebrow">Transaksi baru</span>
                            <h1>Tambah Barang</h1>
                        </div>
                        <span class="shortcut-chip">F2 Cari</span>
                    </div>
                    <div class="search-row">
                        <div class="search-field">
                            <input type="search" id="searchProduct" placeholder="Cari nama barang / kode / barcode..." autocomplete="off" autofocus>
                            <div class="product-results" id="productResults"></div>
                        </div>
                        <button class="button button-primary" id="searchButton" type="button">Cari</button>
                    </div>
                    <div class="customer-grid">
                        <label> Pelanggan
                            <select id="customerType">
                                <option>Umum</option>
                                <option>Pelanggan Member</option>
                                <option>Member VIP</option>
                            </select>
                        </label>
                        <label>No. Member / HP
                            <input type="text" id="customerContact" placeholder="Opsional">
                        </label>
                    </div>
                </div>

                <div class="card cart-card">
                    <div class="card-heading compact">
                        <h2>Keranjang Belanja</h2>
                        <strong id="itemCount">0 Item</strong>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>No</th><th>Barang</th><th>Harga</th><th>Qty</th><th class="align-right">Subtotal</th><th></th></tr>
                            </thead>
                            <tbody id="cartBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="held-bar" id="heldBar" hidden>
                    <div><strong>Transaksi ditahan</strong><span id="heldCount">0 transaksi</span></div>
                    <button class="button button-ghost" id="resumeHeld" type="button">Lihat / Lanjutkan</button>
                </div>
            </section>

            <aside class="payment-panel">
                <div class="card summary-card">
                    <div class="card-heading compact"><h2>Ringkasan Pembayaran</h2></div>
                    <div class="summary-list">
                        <div class="summary-row"><span>Total Item</span><strong id="totalQty">0</strong></div>
                        <div class="summary-row"><span>Subtotal</span><strong id="subtotal">Rp 0</strong></div>
                        <label class="summary-row"><span>Diskon (%)</span><input type="number" id="discountPercent" min="0" max="100" value="0"></label>
                        <label class="summary-row"><span>Diskon (Rp)</span><input type="number" id="discountAmount" min="0" value="0"></label>
                        <label class="summary-row"><span>Pajak / PPN</span><input type="number" id="tax" min="0" value="0"></label>
                        <label class="summary-row"><span>Biaya Lain</span><input type="number" id="otherFee" min="0" value="0"></label>
                    </div>
                    <div class="total-box"><span>TOTAL AKHIR</span><strong id="grandTotal">Rp 0</strong></div>

                    <div class="payment-box">
                        <label for="payment">Uang Dibayar</label>
                        <input class="payment-input" type="number" id="payment" min="0" placeholder="0">
                        <div class="payment-heading"><span>Nominal Cepat</span><button type="button" class="text-button" id="exactPayment">Uang Pas</button></div>
                        <div class="quick-payments" id="quickPayments"></div>
                        <label for="paymentMethod">Metode Pembayaran</label>
                        <div class="payment-methods" id="paymentMethods">
                            <button type="button" class="active" data-method="Tunai">Tunai</button>
                            <button type="button" data-method="QRIS">QRIS</button>
                            <button type="button" data-method="Debit">Debit</button>
                            <button type="button" data-method="Kredit">Kredit</button>
                            <button type="button" data-method="E-Wallet">E-Wallet</button>
                            <button type="button" data-method="Transfer">Transfer</button>
                        </div>
                        <div class="change-box" id="changeBox"><span id="changeLabel">KEMBALIAN</span><strong id="change">Rp 0</strong></div>
                    </div>

                    <div class="action-grid">
                        <button class="button button-warning" id="holdButton" type="button">Tahan</button>
                        <button class="button button-danger" id="cancelButton" type="button">Batal</button>
                        <button class="button button-success pay-button" id="payButton" type="button">BAYAR &amp; CETAK</button>
                    </div>
                </div>
            </aside>
        </main>

        <footer class="pos-footer"><span>Kasir: {{ auth()->user()->name }}</span><span>F2 Cari Barang &bull; F4 Bayar &bull; ESC Batal</span></footer>
    </div>

    <dialog id="heldDialog" class="held-dialog">
        <div class="dialog-heading"><h2>Transaksi Ditahan</h2><button type="button" class="icon-button" id="closeHeld">&times;</button></div>
        <div id="heldList"></div>
    </dialog>

    <script>
        window.cashierProducts = @json($products);
    </script>
    <script src="{{ asset('js/cashier.js') }}"></script>
</body>
</html>
