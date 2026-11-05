<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard POS - Notifikasi Real-Time</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 40px 20px;
        }
        .card {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 720px;
            margin: auto;
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Dashboard POS - Acara 32</h1>
        <p>Notifikasi transaksi akan muncul di sini saat event broadcast diterima.</p>
    </main>
    @include('partials.order-notifications')
</body>
</html>