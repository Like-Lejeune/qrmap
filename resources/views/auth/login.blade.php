{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4fffb0">
    <title>Connexion — QRMap</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0d0f14; --bg2: #13161e; --bg3: #1a1e28;
            --border: rgba(255,255,255,0.08);
            --accent: #4fffb0; --text: #f0f2f5; --muted: #8892a4;
            --danger: #ff5f5f;
            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }
        body {
            background: var(--bg); color: var(--text);
            font-family: var(--font-body); min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
        }
        /* Decorative background */
        body::before {
            content: '';
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 20%, rgba(79,255,176,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 80% 80%, rgba(79,159,255,0.05) 0%, transparent 60%);
        }
        .auth-card {
            position: relative; z-index: 1;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            width: 100%; max-width: 400px;
        }
        .logo {
            font-family: var(--font-head); font-weight: 800; font-size: 1.4rem;
            color: var(--accent); display: flex; align-items: center; gap: 10px;
            justify-content: center; margin-bottom: 2rem;
        }
        .logo::before {
            content: ''; width: 14px; height: 14px; background: var(--accent);
            border-radius: 3px; transform: rotate(45deg); display: inline-block;
        }
        h1 { font-family: var(--font-head); font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 0.4rem; }
        .subtitle { color: var(--muted); font-size: 13px; text-align: center; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em; color: var(--muted); margin-bottom: 6px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; background: var(--bg3); border: 1px solid var(--border);
            border-radius: 8px; padding: 11px 14px; color: var(--text);
            font-family: var(--font-body); font-size: 14px;
            transition: border-color 0.2s; outline: none;
        }
        input:focus { border-color: var(--accent); }
        .form-check { display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem; }
        .form-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer; }
        .form-check label { font-size: 13px; text-transform: none; letter-spacing: 0; color: var(--muted); margin-bottom: 0; cursor: pointer; }
        .btn-submit {
            width: 100%; padding: 12px; background: var(--accent); color: #0d0f14;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: background 0.2s; margin-bottom: 1rem;
        }
        .btn-submit:hover { background: #3de89d; }
        .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 1rem; border: 1px solid; }
        .alert-error { background: rgba(255,95,95,0.07); border-color: rgba(255,95,95,0.25); color: var(--danger); }
        .divider { display: flex; align-items: center; gap: 12px; margin: 1.2rem 0; color: var(--muted); font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .link { color: var(--accent); text-decoration: none; font-size: 13px; }
        .link:hover { text-decoration: underline; }
        .footer-links { text-align: center; font-size: 13px; color: var(--muted); }
        .error-msg { color: var(--danger); font-size: 11px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">QRMap</div>
    <h1>Connexion</h1>
    <p class="subtitle">Accédez à votre espace d'administration</p>

    @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
    <div class="alert" style="background:rgba(79,255,176,0.07);border-color:rgba(79,255,176,0.2);color:var(--accent);">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Adresse e-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@qrmap.com">
            @error('email')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required placeholder="••••••••">
            @error('password')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
        <div class="form-check">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Se souvenir de moi</label>
        </div>
        <button type="submit" class="btn-submit">Se connecter</button>
    </form>

    @if(Route::has('password.request'))
    <div class="footer-links">
        <a href="{{ route('password.request') }}" class="link">Mot de passe oublié ?</a>
    </div>
    @endif

    @if(Route::has('register'))
    <div class="divider">ou</div>
    <div class="footer-links">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="link">Créer un compte</a>
    </div>
    @endif
</div>
</body>
</html>