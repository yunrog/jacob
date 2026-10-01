<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            place-items: center;
            background: #191f2d;
            color: #dce3ed;
            font-family: system-ui, sans-serif;
        }

        main {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px;
            font-size: 16px;
        }

        strong {
            font-weight: 700;
        }

        .divider {
            width: 1px;
            height: 22px;
            background: #8791a1;
        }

        p {
            margin: 0;
            font-weight: 400;
        }
    </style>
</head>
<body>
    <main aria-label="Akses ditolak">
        <strong>403</strong>
        <span class="divider" aria-hidden="true"></span>
        <p>ANDA TIDAK MEMILIKI AKSES.</p>
    </main>
</body>
</html>