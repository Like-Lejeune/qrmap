<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4fffb0" id="meta-theme">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"> {{-- fallback anciens navigateurs --}}
    <title>@yield('title', 'QRMap')</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script>
        (function(){
            var s=localStorage.getItem('qrmap-theme');
            var d=window.matchMedia('(prefers-color-scheme: dark)').matches;
            if(s==='light'||(!s&&!d)) document.documentElement.classList.add('light');
        })();
    </script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --bg:#0d0f14;--bg2:#13161e;--bg3:#1a1e28;
            --border:rgba(255,255,255,0.08);
            --accent:#4fffb0;--accent2:#4f9fff;
            --text:#f0f2f5;--muted:#8892a4;
            --danger:#ff5f5f;--warning:#ffb84f;
            --shadow:rgba(0,0,0,0.5);
            --radius:12px;
            --font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;
        }

        html.light{
            --bg:#f0f2f7;--bg2:#ffffff;--bg3:#e8ebf2;
            --border:rgba(0,0,0,0.09);
            --accent:#0aab6e;--accent2:#1a7fd4;
            --text:#111827;--muted:#6b7280;
            --danger:#dc2626;--warning:#d97706;
            --shadow:rgba(0,0,0,0.1);
        }

        html.light tr:hover td{background:rgba(0,0,0,0.02)}
        html.light .btn-primary{color:#ffffff}
        html.light .btn-primary:hover{background:#089159;border-color:#089159}
        html.light .nav-avatar{background:rgba(10,171,110,0.1);border-color:rgba(10,171,110,0.3)}
        html.light .card:hover{border-color:rgba(10,171,110,0.2)}
        html.light select option{background:#ffffff;color:#111827}
        html.light .alert-success{background:rgba(10,171,110,0.07);border-color:rgba(10,171,110,0.2)}
        html.light .badge-green{background:rgba(10,171,110,0.1);border-color:rgba(10,171,110,0.2)}
        html.light #pwa-banner{box-shadow:0 4px 20px rgba(0,0,0,0.12)}

        .theme-toggle{background:none;border:1px solid var(--border);border-radius:8px;color:var(--muted);cursor:pointer;font-size:14px;padding:5px 10px;line-height:1;transition:all 0.2s;flex-shrink:0}
        .theme-toggle:hover{border-color:var(--accent);color:var(--accent)}
        .icon-dark{display:inline}.icon-light{display:none}
        html.light .icon-dark{display:none}html.light .icon-light{display:inline}

        body{background:var(--bg);color:var(--text);font-family:var(--font-body);font-size:15px;line-height:1.6;min-height:100vh;transition:background 0.2s,color 0.2s}
        nav{background:var(--bg2);border-bottom:1px solid var(--border);padding:0 1.5rem;display:flex;align-items:center;height:60px;position:sticky;top:0;z-index:100;gap:0;overflow-x:auto;transition:background 0.2s}
        .nav-logo{font-family:var(--font-head);font-weight:800;font-size:1.1rem;color:var(--accent);text-decoration:none;display:flex;align-items:center;gap:8px;margin-right:1.2rem;white-space:nowrap;flex-shrink:0}
        .nav-logo::before{content:'';width:10px;height:10px;background:var(--accent);border-radius:2px;transform:rotate(45deg);display:inline-block}
        .nav-link{color:var(--muted);text-decoration:none;font-size:13px;font-weight:500;padding:0 12px;height:60px;display:flex;align-items:center;border-bottom:2px solid transparent;transition:color 0.2s,border-color 0.2s;white-space:nowrap;flex-shrink:0}
        .nav-link:hover{color:var(--text)}.nav-link.active{color:var(--text);border-bottom-color:var(--accent)}
        .nav-search{flex:1;max-width:280px;margin:0 10px;position:relative;min-width:0}
        .nav-search input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:7px 12px 7px 30px;color:var(--text);font-size:13px;font-family:var(--font-body);outline:none;transition:border-color 0.2s,background 0.2s}
        .nav-search input:focus{border-color:var(--accent)}.nav-search input::placeholder{color:var(--muted)}
        .nav-search::before{content:'🔍';position:absolute;left:9px;top:50%;transform:translateY(-50%);font-size:11px;pointer-events:none}
        .nav-spacer{flex:1;min-width:4px}
        .nav-user{display:flex;align-items:center;gap:6px;flex-shrink:0;margin-left:6px}
        .nav-avatar{width:28px;height:28px;border-radius:50%;background:rgba(79,255,176,0.15);border:1px solid rgba(79,255,176,0.3);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--accent)}
        .nav-logout{background:none;border:none;color:var(--muted);cursor:pointer;font-size:12px;font-family:var(--font-body);padding:5px 9px;border-radius:6px;transition:all 0.2s}
        .nav-logout:hover{color:var(--danger);background:rgba(255,95,95,0.08)}
        .container{max-width:1100px;margin:0 auto;padding:2rem}
        .page-header{margin-bottom:2rem;display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .page-title{font-family:var(--font-head);font-size:1.8rem;font-weight:700}.page-title span{color:var(--accent)}
        .page-sub{color:var(--muted);font-size:14px;margin-top:4px}
        .card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;transition:border-color 0.2s,background 0.2s}.card:hover{border-color:rgba(79,255,176,0.18)}
        .card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem}
        .card-title{font-family:var(--font-head);font-size:1rem;font-weight:600;margin-bottom:.5rem}
        .card-meta{color:var(--muted);font-size:13px;margin-bottom:1rem}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;font-family:var(--font-body);cursor:pointer;text-decoration:none;transition:all 0.2s;border:1px solid transparent}
        .btn-primary{background:var(--accent);color:#0d0f14;border-color:var(--accent)}.btn-primary:hover{background:#3de89d}
        .btn-outline{background:transparent;color:var(--text);border-color:var(--border)}.btn-outline:hover{border-color:var(--accent);color:var(--accent)}
        .btn-danger{background:transparent;color:var(--danger);border-color:rgba(255,95,95,0.3)}.btn-danger:hover{background:rgba(255,95,95,0.1)}
        .btn-sm{padding:5px 12px;font-size:12px}.btn-actions{display:flex;gap:8px;flex-wrap:wrap}
        .badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.04em}
        .badge-green{background:rgba(79,255,176,.1);color:var(--accent);border:1px solid rgba(79,255,176,.2)}
        .badge-blue{background:rgba(79,159,255,.1);color:var(--accent2);border:1px solid rgba(79,159,255,.2)}
        .badge-gray{background:rgba(136,146,164,.1);color:var(--muted);border:1px solid var(--border)}
        .badge-orange{background:rgba(255,184,79,.1);color:var(--warning);border:1px solid rgba(255,184,79,.2)}
        .form-group{margin-bottom:1.25rem}
        label{display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px}
        input,textarea,select{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:var(--font-body);font-size:14px;transition:border-color 0.2s,background 0.2s;outline:none}
        input:focus,textarea:focus,select:focus{border-color:var(--accent)}select option{background:var(--bg3)}
        textarea{resize:vertical;min-height:80px}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.form-row.three{grid-template-columns:1fr 1fr 1fr}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);padding:10px 12px;border-bottom:1px solid var(--border)}
        td{padding:12px;border-bottom:1px solid var(--border);font-size:14px}tr:last-child td{border-bottom:none}tr:hover td{background:rgba(255,255,255,.02)}
        .table-wrap{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:background 0.2s}
        .alert{padding:12px 16px;border-radius:8px;font-size:14px;margin-bottom:1.5rem;border:1px solid}
        .alert-success{background:rgba(79,255,176,.07);border-color:rgba(79,255,176,.2);color:var(--accent)}
        .alert-error{background:rgba(255,95,95,.07);border-color:rgba(255,95,95,.2);color:var(--danger)}
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:2rem}
        .stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.2rem 1.5rem;transition:background 0.2s}
        .stat-value{font-family:var(--font-head);font-size:2rem;font-weight:700;color:var(--accent)}
        .stat-label{font-size:12px;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.06em}
        .divider{border:none;border-top:1px solid var(--border);margin:1.5rem 0}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:1.5rem;flex-wrap:wrap}
        .breadcrumb a{color:var(--muted);text-decoration:none}.breadcrumb a:hover{color:var(--accent)}.breadcrumb .sep{color:var(--border)}
        .qr-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;text-align:center}
        .qr-card img{width:180px;height:180px;border-radius:8px;background:white;padding:8px}
        .pos-marker{position:absolute;width:16px;height:16px;background:var(--accent);border-radius:50%;border:3px solid white;box-shadow:0 0 0 4px rgba(79,255,176,.3);transform:translate(-50%,-50%);animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{box-shadow:0 0 0 4px rgba(79,255,176,.3)}50%{box-shadow:0 0 0 8px rgba(79,255,176,.1)}}
        #pwa-banner{display:none;position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:12px 20px;font-size:13px;z-index:999;align-items:center;gap:12px;box-shadow:0 4px 20px var(--shadow);max-width:380px;width:calc(100% - 2rem)}
        #pwa-banner.show{display:flex}
        @media(max-width:640px){.form-row,.form-row.three{grid-template-columns:1fr}.page-header{flex-direction:column;align-items:flex-start}.container{padding:1rem}nav{padding:0 1rem}.nav-search{max-width:120px}}
    </style>
    @stack('styles')
</head>
<body>
<nav>
    <a href="{{ route('dashboard') }}" class="nav-logo">QRMap</a>
    <a href="#" class="nav-link {{ request()->routeIs('sites.*','buildings.*','floors.*') ? 'active' : '' }}">Sites</a>
    <a href="{{ route('plan-editor.index') }}" class="nav-link {{ request()->routeIs('plan-editor.*') ? 'active' : '' }}">Éditeur</a>
    <a href="{{ route('qrcodes.index') }}" class="nav-link {{ request()->routeIs('qrcodes.*') && !request()->routeIs('qrcodes.print') ? 'active' : '' }}">QR Codes</a>
    <a href="{{ route('qrcodes.print') }}" class="nav-link {{ request()->routeIs('qrcodes.print') ? 'active' : '' }}">Impression</a>
    <a href="{{ route('stats.index') }}" class="nav-link {{ request()->routeIs('stats.*') ? 'active' : '' }}">Stats</a>
    <div class="nav-spacer"></div>
    <a href="{{ route('export.qrcodes') }}" class="nav-link" style="font-size:12px;padding:0 10px;" title="Exporter CSV">↓ CSV</a>
    <button class="theme-toggle" onclick="toggleTheme()" title="Changer le thème" aria-label="Basculer thème clair / sombre">
        <span class="icon-dark">☀️</span>
        <span class="icon-light">🌙</span>
    </button>
    @auth
    <div class="nav-user">
        <div class="nav-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="nav-logout">Quitter</button>
        </form>
    </div>
    @endauth
</nav>
<div class="container">
    @if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">✕ {{ session('error') }}</div>@endif
    @yield('content')
</div>
<div id="pwa-banner">
    <div style="flex:1"><div style="font-weight:600;margin-bottom:2px">Installer QRMap</div><div style="color:var(--muted);font-size:12px">Accès rapide depuis l'écran d'accueil</div></div>
    <button id="pwa-install-btn" class="btn btn-primary btn-sm">Installer</button>
    <button onclick="document.getElementById('pwa-banner').classList.remove('show')" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px;padding:4px">✕</button>
</div>
<script>
function toggleTheme(){
    var isLight=document.documentElement.classList.toggle('light');
    localStorage.setItem('qrmap-theme',isLight?'light':'dark');
    document.querySelector('meta[name="theme-color"]').content=isLight?'#0aab6e':'#4fffb0';
}
if('serviceWorker'in navigator)window.addEventListener('load',function(){navigator.serviceWorker.register('/sw.js').catch(function(){})});
var deferredPrompt;
window.addEventListener('beforeinstallprompt',function(e){e.preventDefault();deferredPrompt=e;document.getElementById('pwa-banner').classList.add('show')});
document.getElementById('pwa-install-btn').addEventListener('click',function(){if(deferredPrompt){deferredPrompt.prompt();deferredPrompt.userChoice.then(function(){document.getElementById('pwa-banner').classList.remove('show');deferredPrompt=null})}});
window.addEventListener('appinstalled',function(){document.getElementById('pwa-banner').classList.remove('show')});
document.addEventListener('keydown',function(e){if((e.ctrlKey||e.metaKey)&&e.key==='k'){e.preventDefault();var s=document.querySelector('.nav-search input');if(s)s.focus()}});
</script>
@stack('scripts')
</body>
</html>