<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS v4.0 – Login</title>
    <link rel="stylesheet" href="/css/atlas.css">
    <style>
        /* ── Extra styles for username/password form ── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
        .form-input {
            width: 100%;
            box-sizing: border-box;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            padding: 10px 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(14,165,233,.15);
            background: #FFFFFF;
        }
        .form-input::placeholder { color: #94A3B8; }
        .input-hint {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        .alert-danger {
            background: rgba(220,38,38,.08);
            border: 1px solid rgba(220,38,38,.25);
            border-radius: 8px;
            color: var(--danger);
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .login-divider {
            border: none;
            border-top: 1px solid var(--divider);
            margin: 18px 0;
        }
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-text">ATLAS</div>
            <div class="logo-sub">Aplikasi Talent &amp; Learning Analytic System</div>
        </div>

        <h2>Login User</h2>

        @if(session('error'))
            <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" autocomplete="off">
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    class="form-input"
                    placeholder="Masukkan Username"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    autofocus
                    required
                >
                <div class="input-hint">Gunakan nama sebelum tanda @ pada email Anda</div>
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:4px;">
                Masuk &rarr;
            </button>
        </form>

        <hr class="login-divider">
        <p class="text-sm text-muted" style="text-align:center;font-size:11px;">
            ATLAS v4.0 Prototype &bull; BPKP
        </p>
    </div>
</div>
</body>
</html>
