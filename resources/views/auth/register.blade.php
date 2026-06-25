{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — QRMap</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg:#0d0f14;--bg2:#13161e;--bg3:#1a1e28;--border:rgba(255,255,255,0.08);--accent:#4fffb0;--text:#f0f2f5;--muted:#8892a4;--danger:#ff5f5f;--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif; }
        body { background:var(--bg);color:var(--text);font-family:var(--font-body);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem; }
        body::before { content:'';position:fixed;inset:0;pointer-events:none;background:radial-gradient(ellipse 60% 50% at 80% 20%,rgba(79,255,176,0.06) 0%,transparent 60%),radial-gradient(ellipse 50% 40% at 20% 80%,rgba(79,159,255,0.05) 0%,transparent 60%); }
        .auth-card { position:relative;z-index:1;background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:2.5rem 2rem;width:100%;max-width:420px; }
        .logo { font-family:var(--font-head);font-weight:800;font-size:1.4rem;color:var(--accent);display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom:2rem; }
        .logo::before { content:'';width:14px;height:14px;background:var(--accent);border-radius:3px;transform:rotate(45deg);display:inline-block; }
        h1 { font-family:var(--font-head);font-size:1.5rem;font-weight:700;text-align:center;margin-bottom:0.4rem; }
        .subtitle { color:var(--muted);font-size:13px;text-align:center;margin-bottom:2rem; }
        .form-group { margin-bottom:1.1rem; }
        label { display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--muted);margin-bottom:6px; }
        input { width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:11px 14px;color:var(--text);font-family:var(--font-body);font-size:14px;transition:border-color 0.2s;outline:none; }
        input:focus { border-color:var(--accent); }
        .btn-submit { width:100%;padding:12px;background:var(--accent);color:#0d0f14;border:none;border-radius:8px;font-size:14px;font-weight:600;font-family:var(--font-body);cursor:pointer;transition:background 0.2s;margin-bottom:1rem; }
        .btn-submit:hover { background:#3de89d; }
        .error-msg { color:var(--danger);font-size:11px;margin-top:4px; }
        .footer-links { text-align:center;font-size:13px;color:var(--muted);margin-top:1rem; }
        .link { color:var(--accent);text-decoration:none; }
        .link:hover { text-decoration:underline; }
        .form-row { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        @media(max-width:480px) { .form-row { grid-template-columns:1fr; } }
        .strength-bar { height:3px;border-radius:2px;background:var(--bg3);margin-top:6px;overflow:hidden; }
        .strength-fill { height:100%;border-radius:2px;transition:width 0.3s,background 0.3s; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">QRMap</div>
    <h1>Créer un compte</h1>
    <p class="subtitle">Commencez à cartographier vos bâtiments</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="Jean">
                @error('first_name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Dupont">
                @error('last_name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label>Adresse e-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@monsite.com">
            @error('email')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" id="pwd" required placeholder="8 caractères minimum" oninput="checkStrength(this.value)">
            <div class="strength-bar"><div class="strength-fill" id="strength-fill" style="width:0%"></div></div>
            @error('password')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin-bottom:1.5rem;">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Créer mon compte</button>
    </form>

    <div class="footer-links">
        Déjà un compte ? <a href="{{ route('login') }}" class="link">Se connecter</a>
    </div>
</div>
<script>
function checkStrength(val) {
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['', '#ff5f5f', '#ffb84f', '#4f9fff', '#4fffb0'];
    const widths = ['0%', '25%', '50%', '75%', '100%'];
    const fill = document.getElementById('strength-fill');
    fill.style.width = widths[score];
    fill.style.background = colors[score] || '#ff5f5f';
}
</script>
</body>
</html>