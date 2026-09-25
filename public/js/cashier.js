(() => {
    const products = Array.isArray(window.cashierProducts) ? window.cashierProducts : [];
    const state = {
        cart: [],
        paymentMethod: 'Tunai',
        heldTransactions: JSON.parse(localStorage.getItem('cashierHeldTransactions') || '[]'),
    };

    const $ = (id) => document.getElementById(id);
    const searchInput = $('searchProduct');
    const resultBox = $('productResults');
    const cartBody = $('cartBody');
    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Math.max(0, value));
    const numberValue = (id) => Number.parseFloat($(id).value) || 0;

    function getGrandTotal() {
        const subtotal = state.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        const discount = subtotal * Math.min(100, Math.max(0, numberValue('discountPercent'))) / 100 + Math.max(0, numberValue('discountAmount'));
        return Math.max(0, subtotal - discount + numberValue('tax') + numberValue('otherFee'));
    }

    function updateChange() {
        const difference = numberValue('payment') - getGrandTotal();
        const changeBox = $('changeBox');
        changeBox.classList.toggle('short-payment', difference < 0);
        $('changeLabel').textContent = difference < 0 ? 'UANG KURANG' : 'KEMBALIAN';
        $('change').textContent = formatRupiah(Math.abs(difference));
    }

    function calculateTotal() {
        const totalQty = state.cart.reduce((sum, item) => sum + item.qty, 0);
        const subtotal = state.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        $('totalQty').textContent = totalQty;
        $('itemCount').textContent = `${totalQty} Item`;
        $('subtotal').textContent = formatRupiah(subtotal);
        $('grandTotal').textContent = formatRupiah(getGrandTotal());
        renderQuickPayments();
        updateChange();
    }

    function renderCart() {
        if (state.cart.length === 0) {
            cartBody.innerHTML = '<tr><td colspan="6" class="empty-cart">Keranjang masih kosong.<br>Silakan cari atau scan barang.</td></tr>';
            calculateTotal();
            return;
        }

        cartBody.innerHTML = state.cart.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${escapeHtml(item.name)}</strong><small class="product-code">${escapeHtml(item.code)} - ${escapeHtml(item.barcode)}</small></td>
                <td>${formatRupiah(item.price)}</td>
                <td><div class="qty-control"><button type="button" data-action="decrease" data-id="${item.id}">-</button><input type="number" min="1" value="${item.qty}" data-action="set-quantity" data-id="${item.id}"><button type="button" data-action="increase" data-id="${item.id}">+</button></div></td>
                <td class="align-right"><strong>${formatRupiah(item.price * item.qty)}</strong></td>
                <td><button type="button" class="remove-button" data-action="remove" data-id="${item.id}" aria-label="Hapus ${escapeHtml(item.name)}">&times;</button></td>
            </tr>
        `).join('');
        calculateTotal();
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
    }

    function showResults() {
        const keyword = searchInput.value.trim().toLowerCase();
        if (!keyword) {
            resultBox.style.display = 'none';
            return;
        }
        const results = products.filter((product) => [product.name, product.code, product.barcode].some((value) => String(value).toLowerCase().includes(keyword))).slice(0, 8);
        resultBox.innerHTML = results.length ? results.map((product) => `<button type="button" class="product-item" data-product-id="${product.id}"><span><span class="product-name">${escapeHtml(product.name)}</span><span class="product-code">${escapeHtml(product.code)} - ${escapeHtml(product.barcode)} &bull; Stok ${product.stock}</span></span><span class="product-price">${formatRupiah(product.price)}</span></button>`).join('') : '<div class="product-item">Barang tidak ditemukan</div>';
        resultBox.style.display = 'block';
    }

    function addToCart(productId) {
        const product = products.find((item) => item.id === Number(productId));
        if (!product) return;
        const existing = state.cart.find((item) => item.id === product.id);
        if (existing) {
            if (existing.qty < product.stock) existing.qty += 1;
        } else {
            state.cart.push({ ...product, qty: 1 });
        }
        searchInput.value = '';
        resultBox.style.display = 'none';
        renderCart();
        searchInput.focus();
    }

    function updateQuantity(productId, quantity) {
        const item = state.cart.find((entry) => entry.id === Number(productId));
        if (!item) return;
        item.qty = Math.min(item.stock, Math.max(1, Number.parseInt(quantity, 10) || 1));
        renderCart();
    }

    function adjustQuantity(productId, amount) {
        const item = state.cart.find((entry) => entry.id === Number(productId));
        if (!item) return;
        if (item.qty + amount <= 0) {
            state.cart = state.cart.filter((entry) => entry.id !== item.id);
        } else {
            item.qty = Math.min(item.stock, item.qty + amount);
        }
        renderCart();
    }

    function renderQuickPayments() {
        const total = getGrandTotal();
        const amounts = total > 0 ? [...new Set([Math.ceil(total / 10000) * 10000, Math.ceil(total / 50000) * 50000, Math.ceil(total / 100000) * 100000].filter((amount) => amount >= total))] : [];
        $('quickPayments').innerHTML = amounts.map((amount) => `<button type="button" data-payment="${amount}">${formatRupiah(amount)}</button>`).join('');
    }

    function resetTransaction() {
        state.cart = [];
        $('payment').value = '';
        ['discountPercent', 'discountAmount', 'tax', 'otherFee'].forEach((id) => { $(id).value = 0; });
        renderCart();
    }

    function holdTransaction() {
        if (!state.cart.length) return window.alert('Tidak ada transaksi untuk ditahan.');
        state.heldTransactions.push({ id: Date.now(), number: $('transactionNumber').textContent, cart: state.cart, total: getGrandTotal(), customer: $('customerType').value });
        localStorage.setItem('cashierHeldTransactions', JSON.stringify(state.heldTransactions));
        resetTransaction();
        updateHeldIndicator();
        window.alert('Transaksi berhasil ditahan.');
    }

    function updateHeldIndicator() {
        $('heldBar').hidden = state.heldTransactions.length === 0;
        $('heldCount').textContent = `${state.heldTransactions.length} transaksi`;
    }

    function renderHeldList() {
        $('heldList').innerHTML = state.heldTransactions.length ? state.heldTransactions.map((transaction) => `<div class="held-entry"><div><strong>${transaction.number}</strong><small>${transaction.cart.reduce((sum, item) => sum + item.qty, 0)} item &bull; ${formatRupiah(transaction.total)}</small></div><button type="button" class="button button-primary" data-resume-id="${transaction.id}">Lanjutkan</button></div>`).join('') : '<p class="product-code">Belum ada transaksi yang ditahan.</p>';
    }

    function resumeTransaction(id) {
        const transaction = state.heldTransactions.find((entry) => entry.id === Number(id));
        if (!transaction) return;
        state.cart = transaction.cart;
        state.heldTransactions = state.heldTransactions.filter((entry) => entry.id !== transaction.id);
        localStorage.setItem('cashierHeldTransactions', JSON.stringify(state.heldTransactions));
        $('customerType').value = transaction.customer;
        renderCart();
        updateHeldIndicator();
        $('heldDialog').close();
    }

    searchInput.addEventListener('input', showResults);
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const firstResult = resultBox.querySelector('[data-product-id]');
            if (firstResult) { event.preventDefault(); addToCart(firstResult.dataset.productId); }
        }
    });
    $('searchButton').addEventListener('click', showResults);
    resultBox.addEventListener('click', (event) => { const button = event.target.closest('[data-product-id]'); if (button) addToCart(button.dataset.productId); });
    cartBody.addEventListener('click', (event) => { const target = event.target.closest('[data-action]'); if (!target) return; const id = target.dataset.id; if (target.dataset.action === 'increase') adjustQuantity(id, 1); if (target.dataset.action === 'decrease') adjustQuantity(id, -1); if (target.dataset.action === 'remove') { state.cart = state.cart.filter((item) => item.id !== Number(id)); renderCart(); } });
    cartBody.addEventListener('change', (event) => { if (event.target.dataset.action === 'set-quantity') updateQuantity(event.target.dataset.id, event.target.value); });
    ['discountPercent', 'discountAmount', 'tax', 'otherFee'].forEach((id) => $(id).addEventListener('input', calculateTotal));
    $('payment').addEventListener('input', updateChange);
    $('quickPayments').addEventListener('click', (event) => { const button = event.target.closest('[data-payment]'); if (button) { $('payment').value = button.dataset.payment; updateChange(); } });
    $('exactPayment').addEventListener('click', () => { $('payment').value = getGrandTotal(); updateChange(); });
    $('paymentMethods').addEventListener('click', (event) => { const button = event.target.closest('[data-method]'); if (!button) return; state.paymentMethod = button.dataset.method; document.querySelectorAll('[data-method]').forEach((item) => item.classList.toggle('active', item === button)); });
    $('holdButton').addEventListener('click', holdTransaction);
    $('cancelButton').addEventListener('click', () => { if (state.cart.length && window.confirm('Batalkan transaksi ini?')) resetTransaction(); });
    $('payButton').addEventListener('click', () => { const total = getGrandTotal(); const payment = numberValue('payment'); if (!state.cart.length) return window.alert('Keranjang masih kosong.'); if (payment < total) return window.alert('Uang pembayaran masih kurang.'); window.alert(`Pembayaran berhasil!\n\nTotal: ${formatRupiah(total)}\nBayar: ${formatRupiah(payment)}\nKembali: ${formatRupiah(payment - total)}\nMetode: ${state.paymentMethod}`); window.print(); resetTransaction(); });
    $('resumeHeld').addEventListener('click', () => { renderHeldList(); $('heldDialog').showModal(); });
    $('closeHeld').addEventListener('click', () => $('heldDialog').close());
    $('heldList').addEventListener('click', (event) => { const button = event.target.closest('[data-resume-id]'); if (button) resumeTransaction(button.dataset.resumeId); });
    document.addEventListener('click', (event) => { if (!event.target.closest('.search-field')) resultBox.style.display = 'none'; });
    document.addEventListener('keydown', (event) => { if (event.key === 'F2') { event.preventDefault(); searchInput.focus(); } if (event.key === 'F4') { event.preventDefault(); $('payment').focus(); } if (event.key === 'Escape' && state.cart.length) { if (window.confirm('Batalkan transaksi ini?')) resetTransaction(); } });

    $('currentDate').textContent = new Date().toLocaleString('id-ID');
    updateHeldIndicator();
    renderCart();
})();
