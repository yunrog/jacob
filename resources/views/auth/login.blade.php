<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
            color: #111827;
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            padding: 32px;
        }
        h1 {
            margin: 0 0 8px;
            text-align: center;
            font-size: 30px;
        }
        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 24px;
        }
        .field {
            margin-bottom: 18px;
        }
        .field label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .field input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }
        .error {
            color: #dc2626;
            margin-top: 6px;
            font-size: 13px;
        }
        .btn {
            width: 100%;
            border: none;
            background: #2563eb;
            color: white;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Admin Login</h1>
            <div class="subtitle">Welcome back</div>

            @if($errors->any())
                <div class="error" style="margin-bottom: 16px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
