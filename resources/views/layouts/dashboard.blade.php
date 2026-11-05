<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acara 32 - Integrasi & Pengujian Real-Time Broadcast</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px; margin: 0; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .btn { background: #007bff; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin: 15px 0; }
        .btn:hover { background: #0056b3; }
        .status { padding: 8px 12px; background-color: #e3f2fd; color: #1976d2; border-radius: 4px; font-weight: bold; }
        ul { list-style: none; padding: 0; }
        li { background: #f8f9fa; border-left: 4px solid #28a745; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="card">
        <h2>POS Dashboard - Acara 32</h2>
        <div class="status" id="connection-status">Menghubungkan ke Server Reverb...</div>

        <!-- Tombol simulasi pemicu transaksi dari Controller -->
        <button class="btn" onclick="triggerOrder()">+ Buat Transaksi Baru</button>

        <h3>Log Notifikasi Real-Time:</h3>
        <ul id="notification-list">
            <li style="border-left-color: #6c757d;">Belum ada transaksi...</li>
        </ul>
    </div>

    <!-- Script CDN Pusher & Laravel Echo -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"></script>

    <script>
        window.Pusher = Pusher;

        // Inisialisasi Koneksi ke Reverb (Port 8081)
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: 'my-app-key',
            wsHost: '127.0.0.1',
            wsPort: 8081,
            wssPort: 8081,
            forceTLS: false,
            enabledTransports: ['ws', 'wss']
        });

        document.getElementById('connection-status').innerText = 'Status: Reverb Terhubung!';

        // Listener Event Broadcast (Sesuai Prosedur Kerja Langkah 1)
        window.Echo.channel('orders')
            .listen('OrderPlaced', (e) => {
                console.log('Order Placed Event Received:', e);

                // Notifikasi visual berupa Alert sesuai modul
                alert(`[NOTIFIKASI REAL-TIME]\nOrder #${e.order.id} telah ditempatkan!\nTotal: Rp ${e.order.total}`);

                // Menambahkan item ke daftar visual di view
                const list = document.getElementById('notification-list');
                if (list.children[0] && list.children[0].innerText.includes('Belum ada transaksi')) {
                    list.innerHTML = '';
                }

                const newItem = document.createElement('li');
                newItem.innerHTML = `<strong>Order #${e.order.id}</strong> — Total: Rp ${e.order.total}`;
                list.prepend(newItem);
            });

        // Fungsi pemicu saat tombol diklik
        function triggerOrder() {
            fetch('/test-order')
                .then(res => res.json())
                .then(data => console.log('Respon Controller:', data));
        }
    </script>
</body>
</html>