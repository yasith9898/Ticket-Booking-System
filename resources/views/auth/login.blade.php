<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Unified CRM</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-light);
        }
        .login-card {
            background: #fff;
            padding: 40px 44px;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        .logo {
            font-size: 22px;
            font-weight: bold;
            color: var(--primary);
            text-align: center;
            margin-bottom: 8px;
        }
        h1 {
            margin: 0 0 24px;
            font-size: 18px;
            color: #34495e;
            text-align: center;
            font-weight: 600;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .error { color: #e74c3c; font-size: 13px; margin-top: 6px; }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: var(--secondary);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }
        button[type="submit"]:hover { background: #2980b9; }
        .hint { font-size: 12px; color: #888; text-align: center; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">Unified System</div>
        <h1>Staff Login</h1>
        <form method="POST" action="{{ route('login.perform') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-weight:normal;cursor:pointer;">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <button type="submit">Sign in</button>
        </form>
        <p class="hint">Default: admin@crm.local / password</p>
    </div>
</body>
</html>
