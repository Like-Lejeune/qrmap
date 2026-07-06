<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QRMap — Lancement imminent</title>
<meta name="description" content="Compte à rebours jusqu'au lancement officiel de QRMap.">
<meta property="og:title" content="QRMap — Lancement imminent">
<meta property="og:description" content="Le 6 juillet à 12h. Préparez-vous.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --ink:#0d0f14;
        --primary:{{ $primaryColor ?? '#4fffb0' }};
        --display:'Space Grotesk',sans-serif;
        --mono:'Space Mono','DM Mono',monospace;
        --body:'DM Sans',sans-serif;
    }
    html,body{height:100%}
    body{background:var(--ink);color:#fff;font-family:var(--body);overflow-x:hidden}

    main{position:relative;min-height:100vh;overflow:hidden}

    /* fonds lumineux */
    .grid-bg{position:absolute;inset:0;opacity:.4;
        background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
        background-size:46px 46px;pointer-events:none}
    .glow{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none}
    .glow.tr{top:-128px;right:-128px;width:480px;height:480px;opacity:.30;background:radial-gradient(circle,var(--primary),transparent 60%)}
    .glow.bl{bottom:-160px;left:-128px;width:520px;height:520px;opacity:.25;background:radial-gradient(circle,var(--primary),transparent 60%)}

    /* QR flottants */
    .qr-layer{position:absolute;inset:0;z-index:0;color:color-mix(in srgb,var(--primary) 40%,transparent);pointer-events:none}
    .qr-float{position:absolute;animation:qrFloat linear infinite}
    @keyframes qrFloat{
        0%{transform:translateY(0) rotate(0deg);opacity:.5}
        50%{transform:translateY(-26px) rotate(8deg);opacity:.85}
        100%{transform:translateY(0) rotate(0deg);opacity:.5}
    }
    .qr-layer svg{width:100%;height:100%;display:block}

    /* tracé animé */
    .path-layer{position:absolute;inset:0;z-index:0;width:100%;height:100%;pointer-events:none}
    .path-travel{animation:pathDraw 6s linear infinite}
    @keyframes pathDraw{from{stroke-dashoffset:1720}to{stroke-dashoffset:0}}
    .waypoint{animation:wpPulse 2.4s ease-in-out infinite}
    @keyframes wpPulse{0%,100%{r:4;opacity:.6}50%{r:8;opacity:1}}

    /* contenu */
    section.hero{position:relative;z-index:10;margin:0 auto;display:flex;min-height:100vh;max-width:1024px;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}

    .badge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.05);padding:6px 16px;font-size:12px;font-weight:500;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.8);backdrop-filter:blur(6px);margin-bottom:32px}
    .ring{position:relative;display:inline-flex;width:8px;height:8px}
    .ring .pulse{position:absolute;inset:0;border-radius:50%;background:var(--primary);animation:pulseRing 1.6s cubic-bezier(0,0,.2,1) infinite}
    .ring .dot{position:relative;width:8px;height:8px;border-radius:50%;background:var(--primary)}
    @keyframes pulseRing{0%{transform:scale(1);opacity:.7}80%,100%{transform:scale(2.6);opacity:0}}

    h1{font-family:var(--display);font-weight:600;font-size:clamp(2.75rem,8vw,5.5rem);line-height:1.06;letter-spacing:-.03em}
    .lead{margin-top:24px;max-width:640px;font-size:clamp(1rem,2.5vw,1.125rem);color:rgba(255,255,255,.7);line-height:1.6}
    .lead strong{color:#fff;font-weight:600}

    .countdown{margin-top:56px;display:grid;width:100%;max-width:768px;grid-template-columns:repeat(2,1fr);gap:12px}
    @media(min-width:640px){.countdown{grid-template-columns:repeat(4,1fr);gap:20px}}
    .unit{position:relative;overflow:hidden;border-radius:18px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);padding:24px 16px;backdrop-filter:blur(6px);transition:border-color .25s,background .25s,transform .25s;display:flex;flex-direction:column;align-items:center}
    .unit:hover{border-color:color-mix(in srgb,var(--primary) 50%,transparent);background:rgba(255,255,255,.07);transform:translateY(-3px)}
    .unit::after{content:'';position:absolute;left:50%;bottom:0;transform:translateX(-50%);width:0;height:2px;background:var(--primary);transition:width .3s;border-radius:2px}
    .unit:hover::after{width:60%}
    @media(min-width:640px){.unit{padding:28px}}
    .unit .num{font-family:var(--mono);font-weight:700;font-size:clamp(2.25rem,6.5vw,4rem);line-height:1;letter-spacing:-.04em;color:#fff}
    .unit .lbl{margin-top:10px;font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.22em;color:rgba(255,255,255,.5)}
    @media(min-width:640px){.unit .lbl{font-size:14px}}

    .done{margin-top:56px;border-radius:16px;border:1px solid color-mix(in srgb,var(--primary) 40%,transparent);background:color-mix(in srgb,var(--primary) 10%,transparent);padding:32px 40px;font-family:var(--display);font-size:clamp(1.5rem,4vw,1.875rem);font-weight:600;backdrop-filter:blur(6px)}

    .cta-row{margin-top:40px;display:flex;gap:12px;flex-wrap:wrap;justify-content:center}
    .cta{display:inline-flex;align-items:center;gap:10px;border-radius:999px;font-family:var(--body);font-size:15px;font-weight:600;padding:14px 26px;text-decoration:none;backdrop-filter:blur(6px);transition:background .25s,border-color .25s,transform .25s,box-shadow .25s;border:1px solid transparent}
    .cta svg{width:18px;height:18px;transition:transform .25s}
    .cta:hover{transform:translateY(-2px)}
    .cta-primary{background:var(--primary);color:#0d0f14;border-color:var(--primary)}
    .cta-primary:hover{filter:brightness(1.08);box-shadow:0 12px 30px -10px color-mix(in srgb,var(--primary) 60%,transparent)}
    .cta-ghost{background:color-mix(in srgb,var(--primary) 12%,transparent);border-color:color-mix(in srgb,var(--primary) 40%,transparent);color:#fff}
    .cta-ghost:hover{border-color:var(--primary);background:color-mix(in srgb,var(--primary) 18%,transparent)}
    .cta-ghost:hover svg{transform:translateX(3px)}
    /* note infos + contact */
    .info-note{margin-top:22px;display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.55)}
    .info-note button{background:none;border:none;color:var(--primary);font-family:var(--body);font-size:13px;font-weight:600;cursor:pointer;text-decoration:underline;text-underline-offset:3px;padding:0}
    .info-note button:hover{filter:brightness(1.15)}

    .contact-overlay{position:fixed;inset:0;z-index:50;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(5,7,10,.7);backdrop-filter:blur(4px)}
    .contact-overlay.show{display:flex}
    .contact-card{width:100%;max-width:380px;background:#13161e;border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:26px 24px;text-align:center;animation:cardIn .25s ease}
    @keyframes cardIn{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:none}}
    .contact-card h3{font-family:var(--display);font-weight:600;font-size:1.3rem;margin-bottom:6px}
    .contact-card p{font-size:13px;color:rgba(255,255,255,.6);line-height:1.5;margin-bottom:20px}
    .contact-actions{display:flex;flex-direction:column;gap:10px}
    .contact-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border-radius:12px;padding:13px 18px;font-family:var(--body);font-size:14px;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,.12);transition:transform .2s,border-color .2s,background .2s}
    .contact-btn:hover{transform:translateY(-2px)}
    .contact-btn svg{width:18px;height:18px;flex-shrink:0}
    .contact-btn.wa{background:rgba(37,211,102,.12);border-color:rgba(37,211,102,.4);color:#25d366}
    .contact-btn.wa:hover{background:rgba(37,211,102,.2)}
    .contact-btn.mail{background:color-mix(in srgb,var(--primary) 12%,transparent);border-color:color-mix(in srgb,var(--primary) 40%,transparent);color:#fff}
    .contact-btn.mail:hover{border-color:var(--primary)}
    .contact-close{margin-top:16px;background:none;border:none;color:rgba(255,255,255,.45);font-family:var(--body);font-size:13px;cursor:pointer}
    .contact-close:hover{color:rgba(255,255,255,.7)}

    .tagline{margin-top:48px;font-size:12px;text-transform:uppercase;letter-spacing:.3em;color:rgba(255,255,255,.4)}

    .hidden{display:none}
</style>
</head>
<body>
<main>
    <div class="grid-bg" aria-hidden="true"></div>
    <div class="glow tr" aria-hidden="true"></div>
    <div class="glow bl" aria-hidden="true"></div>

    {{-- QR flottants --}}
    <div class="qr-layer" id="qrLayer" aria-hidden="true"></div>

    {{-- Tracé animé --}}
    <svg class="path-layer" viewBox="0 0 1200 800" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="pathGrad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="var(--primary)" stop-opacity="0"/>
                <stop offset="50%" stop-color="var(--primary)" stop-opacity="0.9"/>
                <stop offset="100%" stop-color="var(--primary)" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <path d="M 50 700 L 250 700 L 250 500 L 500 500 L 500 300 L 800 300 L 800 550 L 1050 550 L 1050 150"
              fill="none" stroke="var(--primary)" stroke-opacity="0.18" stroke-width="2" stroke-dasharray="6 8"/>
        <path d="M 50 700 L 250 700 L 250 500 L 500 500 L 500 300 L 800 300 L 800 550 L 1050 550 L 1050 150"
              fill="none" stroke="url(#pathGrad)" stroke-width="3" stroke-linecap="round"
              stroke-dasharray="120 1600" class="path-travel"/>
        <circle cx="50"   cy="700" r="5" fill="var(--primary)" class="waypoint" style="animation-delay:0s"/>
        <circle cx="250"  cy="500" r="5" fill="var(--primary)" class="waypoint" style="animation-delay:.6s"/>
        <circle cx="500"  cy="300" r="5" fill="var(--primary)" class="waypoint" style="animation-delay:1.2s"/>
        <circle cx="800"  cy="550" r="5" fill="var(--primary)" class="waypoint" style="animation-delay:1.8s"/>
        <circle cx="1050" cy="150" r="5" fill="var(--primary)" class="waypoint" style="animation-delay:2.4s"/>
    </svg>

    <section class="hero">
        <div class="badge">
            <span class="ring"><span class="pulse"></span><span class="dot"></span></span>
            Lancement imminent
        </div>

        <h1>QRMap arrive</h1>
        <p class="lead">
            La navigation intérieure 2D/3D pensée pour vous. Sans GPS, sans application.
            Rendez-vous le <strong>6 juillet à 12h00</strong>.
        </p>

        <div class="countdown" id="countdown">
            <div class="unit"><div class="num" id="days">00</div><div class="lbl">Jours</div></div>
            <div class="unit"><div class="num" id="hours">00</div><div class="lbl">Heures</div></div>
            <div class="unit"><div class="num" id="minutes">00</div><div class="lbl">Minutes</div></div>
            <div class="unit"><div class="num" id="seconds">00</div><div class="lbl">Secondes</div></div>
        </div>

        <div class="done hidden" id="done">🎉 C'est lancé ! Bienvenue sur QRMap.</div>

        <div class="cta-row">
            <a class="cta cta-primary" href="{{ $presentationUrl ?? url('/presentation') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Voir la présentation
            </a>
            <a class="cta cta-ghost" href="{{ $demoUrl ?? url('/demo') }}">
                Essayer la démo
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        <div class="info-note">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            Une question&nbsp;? <button type="button" onclick="openContact()">Contactez-nous</button>
        </div>

        <p class="tagline">Le GPS s'arrête à l'entrée. QRMap commence là.</p>
    </section>

    {{-- Modale de contact --}}
    <div class="contact-overlay" id="contactOverlay" onclick="if(event.target===this)closeContact()">
        <div class="contact-card" role="dialog" aria-modal="true" aria-labelledby="contactTitle">
            <h3 id="contactTitle">Informations supplémentaires</h3>
            <p>Une question sur QRMap&nbsp;? Écrivez-nous, on vous répond rapidement.</p>
            <div class="contact-actions">
                <a class="contact-btn wa" href="https://wa.me/{{ $whatsapp ?? '237695249549' }}" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.7.9-.9 1.1-.2.2-.3.2-.6.1-1.5-.7-2.5-1.3-3.5-3-.3-.5.3-.4.8-1.4.1-.2 0-.4 0-.5 0-.1-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.5c-.2 0-.5.1-.7.3-.3.3-1 .9-1 2.3s1 2.7 1.2 2.9c.1.2 2 3.1 4.9 4.3 1.8.8 2.5.8 3.4.7.5-.1 1.7-.7 1.9-1.4.2-.6.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 0 0-8.6 15l-1.3 4.7L7 20.4A10 10 0 1 0 12 2"/></svg>
                    Discuter sur WhatsApp
                </a>
                <a class="contact-btn mail" href="mailto:{{ $email ?? 'contact@qrmap.app' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                    {{ $email ?? 'contact@qrmap.app' }}
                </a>
            </div>
            <button class="contact-close" onclick="closeContact()">Fermer</button>
        </div>
    </div>
</main>

<script>
// ===== Compte à rebours =====
const LAUNCH_DATE = new Date("2026-07-06T12:00:00+01:00").getTime();
const $ = id => document.getElementById(id);
const pad = n => String(n).padStart(2, "0");

function tick(){
    const diff = Math.max(0, LAUNCH_DATE - Date.now());
    if(diff === 0){
        $('countdown').classList.add('hidden');
        $('done').classList.remove('hidden');
        clearInterval(timer);
        return;
    }
    $('days').textContent    = pad(Math.floor(diff / 86400000));
    $('hours').textContent   = pad(Math.floor((diff / 3600000) % 24));
    $('minutes').textContent = pad(Math.floor((diff / 60000) % 60));
    $('seconds').textContent = pad(Math.floor((diff / 1000) % 60));
}

// ===== QR flottants (motif déterministe par seed) =====
function qrTile(seed){
    const NS = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(NS,'svg');
    svg.setAttribute('viewBox','0 0 70 70');
    // finder patterns (3 coins)
    [[0,0],[50,0],[0,50]].forEach(([x,y])=>{
        const outer = document.createElementNS(NS,'rect');
        outer.setAttribute('x',x);outer.setAttribute('y',y);outer.setAttribute('width',20);outer.setAttribute('height',20);
        outer.setAttribute('fill','none');outer.setAttribute('stroke','currentColor');outer.setAttribute('stroke-width',3);
        svg.appendChild(outer);
        const inner = document.createElementNS(NS,'rect');
        inner.setAttribute('x',x+7);inner.setAttribute('y',y+7);inner.setAttribute('width',6);inner.setAttribute('height',6);
        inner.setAttribute('fill','currentColor');
        svg.appendChild(inner);
    });
    // modules de données
    for(let i=0;i<49;i++){
        const v = Math.sin(seed*9.13 + i*2.71)*10000;
        if((v - Math.floor(v)) <= 0.5) continue;
        const cx = i % 7, cy = Math.floor(i/7);
        if((cx<3&&cy<3)||(cx>3&&cy<3)||(cx<3&&cy>3)) continue; // éviter les finders
        const r = document.createElementNS(NS,'rect');
        r.setAttribute('x',cx*10);r.setAttribute('y',cy*10);r.setAttribute('width',8);r.setAttribute('height',8);
        r.setAttribute('fill','currentColor');
        svg.appendChild(r);
    }
    return svg;
}

(function renderQRs(){
    const qrs = [
        {top:'8%',left:'6%',size:90,delay:0,dur:14,seed:1},
        {top:'18%',left:'82%',size:70,delay:3,dur:18,seed:2},
        {top:'62%',left:'4%',size:110,delay:6,dur:16,seed:3},
        {top:'72%',left:'78%',size:80,delay:2,dur:20,seed:4},
        {top:'40%',left:'90%',size:55,delay:8,dur:22,seed:5},
        {top:'85%',left:'45%',size:65,delay:4,dur:17,seed:6},
    ];
    const layer = $('qrLayer');
    qrs.forEach(q=>{
        const d = document.createElement('div');
        d.className = 'qr-float';
        d.style.top = q.top; d.style.left = q.left;
        d.style.width = q.size+'px'; d.style.height = q.size+'px';
        d.style.animationDelay = q.delay+'s';
        d.style.animationDuration = q.dur+'s';
        d.appendChild(qrTile(q.seed));
        layer.appendChild(d);
    });
})();

function openContact(){ document.getElementById('contactOverlay').classList.add('show'); }
function closeContact(){ document.getElementById('contactOverlay').classList.remove('show'); }
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeContact(); });

tick();
const timer = setInterval(tick, 1000);
</script>
</body>
</html>