<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QRMap — Navigation intérieure 2D/3D</title>
    <meta name="description" content="Transformez un simple QR code en guide interactif 2D/3D. Sans GPS, sans application.">

    <!-- Favicon (losange vert de la marque, SVG inline) -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect x='25' y='25' width='50' height='50' fill='%230AAB6E' transform='rotate(45 50 50)'/%3E%3C/svg%3E">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://qrmap.app">
    <meta property="og:site_name" content="QRMap">
    <meta property="og:title" content="QRMap — Le GPS s'arrête à l'entrée. QRMap commence là.">
    <meta property="og:description" content="Navigation intérieure sans application : scannez un QR code, suivez votre itinéraire pas à pas dans les hôpitaux, banques, universités, administrations et événements. Lancement le 6 juillet 2026.">
    <meta property="og:image" content="https://qrmap.app/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_FR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="QRMap — Le GPS s'arrête à l'entrée. QRMap commence là.">
    <meta name="twitter:description" content="Navigation intérieure sans application. Scannez, suivez, arrivez.">
    <meta name="twitter:image" content="https://qrmap.app/og-image.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#0AAB6E', dark: '#078a58', foreground: '#ffffff' },
                        ink: { DEFAULT: '#1A202C', 2: '#2D3748' },
                        mist: '#F4F8F6',
                        background: '#ffffff',
                        foreground: '#1A202C',
                        muted: { foreground: '#6B7280' },
                        border: 'rgba(26,32,44,0.10)',
                    },
                    fontFamily: {
                        display: ['Syne', 'sans-serif'],
                        body: ['DM Sans', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Analytics 4 -->
    <!-- TODO: demander l'ID de mesure au propriétaire, puis remplacer G-XXXXXXXXXX -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
        function trackEvent(name, params){ try { gtag('event', name, params || {}); } catch(e){} }
    </script>

    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }

        .grid-bg {
            background-image:
                linear-gradient(rgba(10,171,110,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(10,171,110,0.07) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .glow-green { box-shadow: 0 0 0 1px rgba(10,171,110,0.3), 0 10px 40px -10px rgba(10,171,110,0.5); }

        [data-reveal] { opacity: 0; }
        .animate-reveal { animation: reveal .7s cubic-bezier(.16,1,.3,1) forwards; }
        @keyframes reveal { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        .animate-float-slow { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }

        .animate-pulse-ring { animation: ring 2s ease-out infinite; }
        @keyframes ring { 0% { transform: scale(.8); opacity:.7; } 100% { transform: scale(2.2); opacity:0; } }
        .animate-pulse-dot { animation: dot 2s ease-in-out infinite; }
        @keyframes dot { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }

        .lucide { width: 1em; height: 1em; display: inline-block; vertical-align: -0.125em; }
    </style>
</head>
<body class="bg-background text-foreground">

<!-- NAVBAR -->
<header class="fixed top-0 inset-x-0 z-50 backdrop-blur-md bg-white/70 border-b border-border">
    <div class="mx-auto max-w-7xl px-5 sm:px-8 h-16 flex items-center justify-between">
        <a href="#top" class="flex items-center gap-2 font-display font-bold text-lg">
            <span class="text-primary text-xl">◆</span><span class="text-ink">QRMap</span>
        </a>
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-ink-2">
            <a href="#produit" class="hover:text-primary transition">Produit</a>
            <a href="#marches" class="hover:text-primary transition">Marchés</a>
            <a href="/demo" class="hover:text-primary transition inline-flex items-center gap-1"><i data-lucide="play" class="w-3.5 h-3.5"></i> Démo</a>
            <a href="/presentation/" onclick="trackEvent('click_presentation',{source:'navbar'})" class="hover:text-primary transition">Présentation</a>
            <a href="#tarifs" class="hover:text-primary transition">Tarifs</a>
            <a href="#faq" class="hover:text-primary transition">FAQ</a>
            <a href="#contact" class="hover:text-primary transition">Contact</a>
            <span class="flex items-center gap-1 text-xs font-semibold">
                <button type="button" onclick="setLang('fr')" data-lang-btn="fr" class="lang-btn text-primary">FR</button>
                <span class="text-ink-2/30">/</span>
                <button type="button" onclick="setLang('en')" data-lang-btn="en" class="lang-btn hover:text-primary">EN</button>
            </span>
        </nav>
        <div class="flex items-center gap-3">
            <a href="/presentation/" onclick="trackEvent('click_presentation',{source:'navbar'})" class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">
                Voir la présentation <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <!-- Bouton hamburger (mobile uniquement) -->
            <button id="menuBtn" onclick="toggleMenu()" aria-label="Ouvrir le menu" aria-expanded="false" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-border text-ink hover:border-primary transition">
                <i data-lucide="menu" id="menuIcon" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Menu mobile déroulant -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-border bg-white/95 backdrop-blur-md">
        <nav class="mx-auto max-w-7xl px-5 sm:px-8 py-4 flex flex-col gap-1 text-sm font-medium text-ink-2">
            <a href="#produit" onclick="closeMenu()" class="py-3 border-b border-border/60 hover:text-primary transition">Produit</a>
            <a href="#marches" onclick="closeMenu()" class="py-3 border-b border-border/60 hover:text-primary transition">Marchés</a>
            <a href="/demo" onclick="closeMenu();trackEvent('click_demo',{source:'mobile_menu'})" class="py-3 border-b border-border/60 hover:text-primary transition inline-flex items-center gap-1.5"> Démo</a>
            <a href="/presentation/" onclick="closeMenu();trackEvent('click_presentation',{source:'mobile_menu'})" class="py-3 border-b border-border/60 hover:text-primary transition"><i data-lucide="play" class="w-3.5 h-3.5"></i> Présentation</a>
            <a href="#tarifs" onclick="closeMenu()" class="py-3 border-b border-border/60 hover:text-primary transition">Tarifs</a>
            <a href="#faq" onclick="closeMenu()" class="py-3 border-b border-border/60 hover:text-primary transition">FAQ</a>
            <a href="#contact" onclick="closeMenu()" class="py-3 border-b border-border/60 hover:text-primary transition">Contact</a>
            <div class="py-3 flex items-center gap-2 text-sm font-semibold">
                <span class="text-muted-foreground">Langue :</span>
                <button type="button" onclick="setLang('fr');closeMenu()" data-lang-btn="fr" class="lang-btn text-primary">FR</button>
                <span class="text-ink-2/30">/</span>
                <button type="button" onclick="setLang('en');closeMenu()" data-lang-btn="en" class="lang-btn hover:text-primary">EN</button>
            </div>
        </nav>
    </div>
</header>

<!-- HERO -->
<section id="top" class="relative min-h-screen pt-32 pb-24 bg-ink text-white overflow-hidden">
    <div class="absolute inset-0 grid-bg opacity-60"></div>
    <div class="absolute -top-32 -right-32 w-[520px] h-[520px] rounded-full bg-primary/20 blur-[120px]"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-primary-dark/30 blur-[140px]"></div>

    <div class="absolute top-32 left-10 hidden lg:block animate-float-slow opacity-30"><i data-lucide="qr-code" class="w-16 h-16 text-primary"></i></div>
    <div class="absolute bottom-40 left-1/3 hidden lg:block animate-float-slow opacity-20" style="animation-delay:1.2s"><i data-lucide="qr-code" class="w-10 h-10 text-primary"></i></div>

    <div class="relative mx-auto max-w-7xl px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-medium text-primary mb-6">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Navigation intérieure · Safe buildings
            </div>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold leading-[1.05] tracking-tight">
                Le GPS s'arrête à l'entrée. <span class="text-primary">QRMap commence là.</span>
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-white/70 max-w-xl leading-relaxed">
                Transformez un simple QR code imprimé en guide interactif 2D/3D. Sans GPS. Sans application. Depuis n'importe quel smartphone.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="/demo" onclick="trackEvent('click_demo',{source:'hero'})" class="inline-flex items-center gap-2 rounded-full bg-primary px-7 py-4 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">Essayer la démo <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="#produit" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-7 py-4 text-sm font-semibold text-white hover:bg-white/10 transition">En savoir plus</a>
            </div>
            <div class="mt-10 flex items-center gap-6 text-xs text-white/50">
                <span class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-primary"></i> Sans GPS</span>
                <span class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-primary"></i> Sans app store</span>
                <span class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-primary"></i> Offline-ready</span>
            </div>
        </div>

        <!-- Phone mockup -->
        <div class="relative mx-auto">
            <div class="relative w-[300px] sm:w-[340px] h-[620px] rounded-[3rem] bg-gradient-to-b from-ink-2 to-black border border-white/10 p-4 shadow-2xl">
                <div class="absolute top-2 left-1/2 -translate-x-1/2 w-28 h-6 bg-black rounded-full"></div>
                <div class="w-full h-full rounded-[2.2rem] bg-mist overflow-hidden relative">
                    <div class="absolute top-0 inset-x-0 h-10 flex items-center justify-between px-5 text-[10px] text-ink-2 font-medium"><span>9:41</span><span>QRMap</span></div>
                    <svg viewBox="0 0 300 540" class="absolute inset-0 w-full h-full">
                        <defs>
                            <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="#0AAB6E" stroke-opacity="0.08" stroke-width="1"/></pattern>
                        </defs>
                        <rect width="300" height="540" fill="url(#grid)" />
                        <rect x="30" y="60" width="100" height="80" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <rect x="150" y="60" width="120" height="120" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <rect x="30" y="160" width="100" height="100" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <rect x="150" y="200" width="120" height="80" rx="4" fill="#0AAB6E" fill-opacity="0.08" stroke="#0AAB6E" stroke-width="1.5"/>
                        <rect x="30" y="280" width="240" height="60" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <rect x="30" y="360" width="110" height="120" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <rect x="160" y="360" width="110" height="120" rx="4" fill="white" stroke="#1A202C" stroke-opacity="0.15" stroke-width="1.5"/>
                        <path d="M 80 300 L 80 220 L 200 220" stroke="#0AAB6E" stroke-width="3" stroke-dasharray="6 6" fill="none" stroke-linecap="round"/>
                        <text x="40" y="105" font-size="12" fill="#1A202C" opacity="0.6" font-family="DM Sans">Accueil</text>
                        <text x="160" y="125" font-size="12" fill="#1A202C" opacity="0.6" font-family="DM Sans">Pédiatrie</text>
                        <text x="40" y="210" font-size="12" fill="#1A202C" opacity="0.6" font-family="DM Sans">Pharmacie</text>
                        <text x="160" y="245" font-size="14" fill="#0AAB6E" font-weight="600" font-family="DM Sans">Cardiologie</text>
                    </svg>
                    <div class="absolute" style="left:70px;top:330px">
                        <span class="absolute inset-0 w-5 h-5 rounded-full bg-primary animate-pulse-ring"></span>
                        <span class="relative block w-5 h-5 rounded-full bg-primary border-2 border-white shadow-lg animate-pulse-dot"></span>
                    </div>
                    <div class="absolute bottom-4 inset-x-4 rounded-2xl bg-white shadow-xl p-4 border border-border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/15 flex items-center justify-center"><i data-lucide="map-pin" class="w-5 h-5 text-primary"></i></div>
                            <div class="flex-1 min-w-0"><p class="text-[11px] text-muted-foreground">Destination</p><p class="text-sm font-semibold text-ink truncate">Service Cardiologie</p></div>
                            <span class="text-xs font-bold text-primary">2 min</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute -left-6 top-16 hidden sm:flex items-center gap-2 rounded-full bg-white text-ink px-4 py-2 text-xs font-semibold shadow-lg animate-float-slow">
                <i data-lucide="scan-line" class="w-4 h-4 text-primary"></i> QR scanné
            </div>
        </div>
    </div>
</section>

<!-- PROBLEM -->
<section class="relative bg-ink-2 text-white py-28">
    <div class="absolute inset-0 grid-bg opacity-30"></div>
    <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
        <div class="max-w-3xl" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">Le constat</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold leading-tight">Le GPS s'arrête à l'entrée des bâtiments.</h2>
        </div>
        <div class="mt-16 grid md:grid-cols-3 gap-6">
            <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 hover:border-primary/40 transition">
                <p class="font-display text-5xl font-bold text-primary leading-none">10 à 25 min</p>
                <p class="mt-3 text-white font-semibold">perdues par visiteur</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">dans les grands bâtiments à cause d'une signalétique insuffisante : hôpitaux, administrations, campus, centres commerciaux.</p>
            </div>
            <div data-reveal style="animation-delay:120ms" class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 hover:border-primary/40 transition">
                <p class="font-display text-5xl font-bold text-primary leading-none">0 solution</p>
                <p class="mt-3 text-white font-semibold">locale déployée</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">aucune navigation intérieure commerciale n'est déployée au Cameroun aujourd'hui, quel que soit le type de bâtiment.</p>
            </div>
            <div data-reveal style="animation-delay:240ms" class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 hover:border-primary/40 transition">
                <p class="font-display text-5xl font-bold text-primary leading-none">Des milliers</p>
                <p class="mt-3 text-white font-semibold">de visiteurs par jour</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">dans les hôpitaux, banques, administrations et grands événements — sans aucun système d'orientation dynamique.</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="produit" class="bg-mist py-28">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">Comment ça marche</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-ink leading-tight">3 étapes. Aucune infrastructure.</h2>
        </div>
        <div class="mt-20 relative">
            <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent"></div>
            <div class="grid md:grid-cols-3 gap-10">
                <div class="relative text-center" data-reveal>
                    <div class="relative mx-auto w-24 h-24 rounded-2xl bg-white border border-border flex items-center justify-center shadow-sm">
                        <i data-lucide="map" class="w-10 h-10 text-primary"></i>
                        <span class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-primary text-primary-foreground text-xs font-bold font-display flex items-center justify-center shadow">01</span>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-ink">On cartographie</h3>
                    <p class="mt-3 text-muted-foreground leading-relaxed max-w-xs mx-auto">Nous modélisons votre bâtiment : étages, salles, couloirs, points d'intérêt.</p>
                </div>
                <div class="relative text-center" data-reveal style="animation-delay:150ms">
                    <div class="relative mx-auto w-24 h-24 rounded-2xl bg-white border border-border flex items-center justify-center shadow-sm">
                        <i data-lucide="qr-code" class="w-10 h-10 text-primary"></i>
                        <span class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-primary text-primary-foreground text-xs font-bold font-display flex items-center justify-center shadow">02</span>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-ink">On installe les QR codes</h3>
                    <p class="mt-3 text-muted-foreground leading-relaxed max-w-xs mx-auto">Impression et placement aux points stratégiques. Aucun matériel spécifique.</p>
                </div>
                <div class="relative text-center" data-reveal style="animation-delay:300ms">
                    <div class="relative mx-auto w-24 h-24 rounded-2xl bg-white border border-border flex items-center justify-center shadow-sm">
                        <i data-lucide="smartphone" class="w-10 h-10 text-primary"></i>
                        <span class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-primary text-primary-foreground text-xs font-bold font-display flex items-center justify-center shadow">03</span>
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-ink">Vos visiteurs scannent</h3>
                    <p class="mt-3 text-muted-foreground leading-relaxed max-w-xs mx-auto">Un scan suffit pour ouvrir le plan interactif. Aucune installation requise.</p>
                </div>
            </div>
        </div>
        <div class="mt-16 flex justify-center" data-reveal>
            <div class="rounded-3xl bg-white border border-border p-8 flex flex-col items-center text-center shadow-sm max-w-xs">
                <div class="hidden md:block w-[160px] h-[160px]"><svg viewBox="0 0 29 29" xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges" width="100%" height="100%" fill="#0d0f14"><rect x="2" y="2" width="1" height="1"/><rect x="3" y="2" width="1" height="1"/><rect x="4" y="2" width="1" height="1"/><rect x="5" y="2" width="1" height="1"/><rect x="6" y="2" width="1" height="1"/><rect x="7" y="2" width="1" height="1"/><rect x="8" y="2" width="1" height="1"/><rect x="10" y="2" width="1" height="1"/><rect x="11" y="2" width="1" height="1"/><rect x="12" y="2" width="1" height="1"/><rect x="13" y="2" width="1" height="1"/><rect x="14" y="2" width="1" height="1"/><rect x="16" y="2" width="1" height="1"/><rect x="17" y="2" width="1" height="1"/><rect x="20" y="2" width="1" height="1"/><rect x="21" y="2" width="1" height="1"/><rect x="22" y="2" width="1" height="1"/><rect x="23" y="2" width="1" height="1"/><rect x="24" y="2" width="1" height="1"/><rect x="25" y="2" width="1" height="1"/><rect x="26" y="2" width="1" height="1"/><rect x="2" y="3" width="1" height="1"/><rect x="8" y="3" width="1" height="1"/><rect x="12" y="3" width="1" height="1"/><rect x="13" y="3" width="1" height="1"/><rect x="20" y="3" width="1" height="1"/><rect x="26" y="3" width="1" height="1"/><rect x="2" y="4" width="1" height="1"/><rect x="4" y="4" width="1" height="1"/><rect x="5" y="4" width="1" height="1"/><rect x="6" y="4" width="1" height="1"/><rect x="8" y="4" width="1" height="1"/><rect x="11" y="4" width="1" height="1"/><rect x="13" y="4" width="1" height="1"/><rect x="16" y="4" width="1" height="1"/><rect x="17" y="4" width="1" height="1"/><rect x="18" y="4" width="1" height="1"/><rect x="20" y="4" width="1" height="1"/><rect x="22" y="4" width="1" height="1"/><rect x="23" y="4" width="1" height="1"/><rect x="24" y="4" width="1" height="1"/><rect x="26" y="4" width="1" height="1"/><rect x="2" y="5" width="1" height="1"/><rect x="4" y="5" width="1" height="1"/><rect x="5" y="5" width="1" height="1"/><rect x="6" y="5" width="1" height="1"/><rect x="8" y="5" width="1" height="1"/><rect x="10" y="5" width="1" height="1"/><rect x="12" y="5" width="1" height="1"/><rect x="14" y="5" width="1" height="1"/><rect x="15" y="5" width="1" height="1"/><rect x="16" y="5" width="1" height="1"/><rect x="17" y="5" width="1" height="1"/><rect x="20" y="5" width="1" height="1"/><rect x="22" y="5" width="1" height="1"/><rect x="23" y="5" width="1" height="1"/><rect x="24" y="5" width="1" height="1"/><rect x="26" y="5" width="1" height="1"/><rect x="2" y="6" width="1" height="1"/><rect x="4" y="6" width="1" height="1"/><rect x="5" y="6" width="1" height="1"/><rect x="6" y="6" width="1" height="1"/><rect x="8" y="6" width="1" height="1"/><rect x="10" y="6" width="1" height="1"/><rect x="11" y="6" width="1" height="1"/><rect x="12" y="6" width="1" height="1"/><rect x="13" y="6" width="1" height="1"/><rect x="14" y="6" width="1" height="1"/><rect x="15" y="6" width="1" height="1"/><rect x="16" y="6" width="1" height="1"/><rect x="17" y="6" width="1" height="1"/><rect x="18" y="6" width="1" height="1"/><rect x="20" y="6" width="1" height="1"/><rect x="22" y="6" width="1" height="1"/><rect x="23" y="6" width="1" height="1"/><rect x="24" y="6" width="1" height="1"/><rect x="26" y="6" width="1" height="1"/><rect x="2" y="7" width="1" height="1"/><rect x="8" y="7" width="1" height="1"/><rect x="10" y="7" width="1" height="1"/><rect x="13" y="7" width="1" height="1"/><rect x="14" y="7" width="1" height="1"/><rect x="15" y="7" width="1" height="1"/><rect x="20" y="7" width="1" height="1"/><rect x="26" y="7" width="1" height="1"/><rect x="2" y="8" width="1" height="1"/><rect x="3" y="8" width="1" height="1"/><rect x="4" y="8" width="1" height="1"/><rect x="5" y="8" width="1" height="1"/><rect x="6" y="8" width="1" height="1"/><rect x="7" y="8" width="1" height="1"/><rect x="8" y="8" width="1" height="1"/><rect x="10" y="8" width="1" height="1"/><rect x="12" y="8" width="1" height="1"/><rect x="14" y="8" width="1" height="1"/><rect x="16" y="8" width="1" height="1"/><rect x="18" y="8" width="1" height="1"/><rect x="20" y="8" width="1" height="1"/><rect x="21" y="8" width="1" height="1"/><rect x="22" y="8" width="1" height="1"/><rect x="23" y="8" width="1" height="1"/><rect x="24" y="8" width="1" height="1"/><rect x="25" y="8" width="1" height="1"/><rect x="26" y="8" width="1" height="1"/><rect x="10" y="9" width="1" height="1"/><rect x="11" y="9" width="1" height="1"/><rect x="12" y="9" width="1" height="1"/><rect x="14" y="9" width="1" height="1"/><rect x="17" y="9" width="1" height="1"/><rect x="18" y="9" width="1" height="1"/><rect x="2" y="10" width="1" height="1"/><rect x="6" y="10" width="1" height="1"/><rect x="8" y="10" width="1" height="1"/><rect x="9" y="10" width="1" height="1"/><rect x="10" y="10" width="1" height="1"/><rect x="11" y="10" width="1" height="1"/><rect x="12" y="10" width="1" height="1"/><rect x="18" y="10" width="1" height="1"/><rect x="19" y="10" width="1" height="1"/><rect x="20" y="10" width="1" height="1"/><rect x="21" y="10" width="1" height="1"/><rect x="22" y="10" width="1" height="1"/><rect x="23" y="10" width="1" height="1"/><rect x="26" y="10" width="1" height="1"/><rect x="2" y="11" width="1" height="1"/><rect x="3" y="11" width="1" height="1"/><rect x="4" y="11" width="1" height="1"/><rect x="5" y="11" width="1" height="1"/><rect x="6" y="11" width="1" height="1"/><rect x="7" y="11" width="1" height="1"/><rect x="11" y="11" width="1" height="1"/><rect x="12" y="11" width="1" height="1"/><rect x="14" y="11" width="1" height="1"/><rect x="15" y="11" width="1" height="1"/><rect x="16" y="11" width="1" height="1"/><rect x="17" y="11" width="1" height="1"/><rect x="18" y="11" width="1" height="1"/><rect x="19" y="11" width="1" height="1"/><rect x="22" y="11" width="1" height="1"/><rect x="23" y="11" width="1" height="1"/><rect x="25" y="11" width="1" height="1"/><rect x="2" y="12" width="1" height="1"/><rect x="3" y="12" width="1" height="1"/><rect x="4" y="12" width="1" height="1"/><rect x="6" y="12" width="1" height="1"/><rect x="8" y="12" width="1" height="1"/><rect x="10" y="12" width="1" height="1"/><rect x="14" y="12" width="1" height="1"/><rect x="16" y="12" width="1" height="1"/><rect x="17" y="12" width="1" height="1"/><rect x="19" y="12" width="1" height="1"/><rect x="21" y="12" width="1" height="1"/><rect x="23" y="12" width="1" height="1"/><rect x="24" y="12" width="1" height="1"/><rect x="3" y="13" width="1" height="1"/><rect x="4" y="13" width="1" height="1"/><rect x="5" y="13" width="1" height="1"/><rect x="6" y="13" width="1" height="1"/><rect x="7" y="13" width="1" height="1"/><rect x="9" y="13" width="1" height="1"/><rect x="11" y="13" width="1" height="1"/><rect x="12" y="13" width="1" height="1"/><rect x="16" y="13" width="1" height="1"/><rect x="17" y="13" width="1" height="1"/><rect x="18" y="13" width="1" height="1"/><rect x="19" y="13" width="1" height="1"/><rect x="20" y="13" width="1" height="1"/><rect x="24" y="13" width="1" height="1"/><rect x="25" y="13" width="1" height="1"/><rect x="2" y="14" width="1" height="1"/><rect x="3" y="14" width="1" height="1"/><rect x="4" y="14" width="1" height="1"/><rect x="6" y="14" width="1" height="1"/><rect x="7" y="14" width="1" height="1"/><rect x="8" y="14" width="1" height="1"/><rect x="10" y="14" width="1" height="1"/><rect x="11" y="14" width="1" height="1"/><rect x="12" y="14" width="1" height="1"/><rect x="13" y="14" width="1" height="1"/><rect x="19" y="14" width="1" height="1"/><rect x="20" y="14" width="1" height="1"/><rect x="23" y="14" width="1" height="1"/><rect x="24" y="14" width="1" height="1"/><rect x="25" y="14" width="1" height="1"/><rect x="26" y="14" width="1" height="1"/><rect x="2" y="15" width="1" height="1"/><rect x="4" y="15" width="1" height="1"/><rect x="6" y="15" width="1" height="1"/><rect x="9" y="15" width="1" height="1"/><rect x="10" y="15" width="1" height="1"/><rect x="14" y="15" width="1" height="1"/><rect x="16" y="15" width="1" height="1"/><rect x="17" y="15" width="1" height="1"/><rect x="18" y="15" width="1" height="1"/><rect x="22" y="15" width="1" height="1"/><rect x="25" y="15" width="1" height="1"/><rect x="8" y="16" width="1" height="1"/><rect x="10" y="16" width="1" height="1"/><rect x="12" y="16" width="1" height="1"/><rect x="13" y="16" width="1" height="1"/><rect x="14" y="16" width="1" height="1"/><rect x="16" y="16" width="1" height="1"/><rect x="17" y="16" width="1" height="1"/><rect x="18" y="16" width="1" height="1"/><rect x="20" y="16" width="1" height="1"/><rect x="21" y="16" width="1" height="1"/><rect x="22" y="16" width="1" height="1"/><rect x="23" y="16" width="1" height="1"/><rect x="24" y="16" width="1" height="1"/><rect x="4" y="17" width="1" height="1"/><rect x="6" y="17" width="1" height="1"/><rect x="11" y="17" width="1" height="1"/><rect x="12" y="17" width="1" height="1"/><rect x="13" y="17" width="1" height="1"/><rect x="16" y="17" width="1" height="1"/><rect x="21" y="17" width="1" height="1"/><rect x="22" y="17" width="1" height="1"/><rect x="24" y="17" width="1" height="1"/><rect x="25" y="17" width="1" height="1"/><rect x="2" y="18" width="1" height="1"/><rect x="3" y="18" width="1" height="1"/><rect x="4" y="18" width="1" height="1"/><rect x="5" y="18" width="1" height="1"/><rect x="6" y="18" width="1" height="1"/><rect x="8" y="18" width="1" height="1"/><rect x="10" y="18" width="1" height="1"/><rect x="14" y="18" width="1" height="1"/><rect x="17" y="18" width="1" height="1"/><rect x="18" y="18" width="1" height="1"/><rect x="19" y="18" width="1" height="1"/><rect x="20" y="18" width="1" height="1"/><rect x="21" y="18" width="1" height="1"/><rect x="22" y="18" width="1" height="1"/><rect x="23" y="18" width="1" height="1"/><rect x="24" y="18" width="1" height="1"/><rect x="10" y="19" width="1" height="1"/><rect x="16" y="19" width="1" height="1"/><rect x="18" y="19" width="1" height="1"/><rect x="22" y="19" width="1" height="1"/><rect x="2" y="20" width="1" height="1"/><rect x="3" y="20" width="1" height="1"/><rect x="4" y="20" width="1" height="1"/><rect x="5" y="20" width="1" height="1"/><rect x="6" y="20" width="1" height="1"/><rect x="7" y="20" width="1" height="1"/><rect x="8" y="20" width="1" height="1"/><rect x="10" y="20" width="1" height="1"/><rect x="12" y="20" width="1" height="1"/><rect x="15" y="20" width="1" height="1"/><rect x="16" y="20" width="1" height="1"/><rect x="18" y="20" width="1" height="1"/><rect x="20" y="20" width="1" height="1"/><rect x="22" y="20" width="1" height="1"/><rect x="2" y="21" width="1" height="1"/><rect x="8" y="21" width="1" height="1"/><rect x="14" y="21" width="1" height="1"/><rect x="16" y="21" width="1" height="1"/><rect x="17" y="21" width="1" height="1"/><rect x="18" y="21" width="1" height="1"/><rect x="22" y="21" width="1" height="1"/><rect x="23" y="21" width="1" height="1"/><rect x="24" y="21" width="1" height="1"/><rect x="25" y="21" width="1" height="1"/><rect x="26" y="21" width="1" height="1"/><rect x="2" y="22" width="1" height="1"/><rect x="4" y="22" width="1" height="1"/><rect x="5" y="22" width="1" height="1"/><rect x="6" y="22" width="1" height="1"/><rect x="8" y="22" width="1" height="1"/><rect x="10" y="22" width="1" height="1"/><rect x="11" y="22" width="1" height="1"/><rect x="12" y="22" width="1" height="1"/><rect x="13" y="22" width="1" height="1"/><rect x="15" y="22" width="1" height="1"/><rect x="16" y="22" width="1" height="1"/><rect x="18" y="22" width="1" height="1"/><rect x="19" y="22" width="1" height="1"/><rect x="20" y="22" width="1" height="1"/><rect x="21" y="22" width="1" height="1"/><rect x="22" y="22" width="1" height="1"/><rect x="23" y="22" width="1" height="1"/><rect x="24" y="22" width="1" height="1"/><rect x="25" y="22" width="1" height="1"/><rect x="26" y="22" width="1" height="1"/><rect x="2" y="23" width="1" height="1"/><rect x="4" y="23" width="1" height="1"/><rect x="5" y="23" width="1" height="1"/><rect x="6" y="23" width="1" height="1"/><rect x="8" y="23" width="1" height="1"/><rect x="12" y="23" width="1" height="1"/><rect x="14" y="23" width="1" height="1"/><rect x="16" y="23" width="1" height="1"/><rect x="19" y="23" width="1" height="1"/><rect x="20" y="23" width="1" height="1"/><rect x="21" y="23" width="1" height="1"/><rect x="24" y="23" width="1" height="1"/><rect x="25" y="23" width="1" height="1"/><rect x="26" y="23" width="1" height="1"/><rect x="2" y="24" width="1" height="1"/><rect x="4" y="24" width="1" height="1"/><rect x="5" y="24" width="1" height="1"/><rect x="6" y="24" width="1" height="1"/><rect x="8" y="24" width="1" height="1"/><rect x="12" y="24" width="1" height="1"/><rect x="13" y="24" width="1" height="1"/><rect x="14" y="24" width="1" height="1"/><rect x="15" y="24" width="1" height="1"/><rect x="16" y="24" width="1" height="1"/><rect x="17" y="24" width="1" height="1"/><rect x="18" y="24" width="1" height="1"/><rect x="19" y="24" width="1" height="1"/><rect x="20" y="24" width="1" height="1"/><rect x="23" y="24" width="1" height="1"/><rect x="25" y="24" width="1" height="1"/><rect x="2" y="25" width="1" height="1"/><rect x="8" y="25" width="1" height="1"/><rect x="13" y="25" width="1" height="1"/><rect x="15" y="25" width="1" height="1"/><rect x="19" y="25" width="1" height="1"/><rect x="21" y="25" width="1" height="1"/><rect x="22" y="25" width="1" height="1"/><rect x="23" y="25" width="1" height="1"/><rect x="24" y="25" width="1" height="1"/><rect x="25" y="25" width="1" height="1"/><rect x="2" y="26" width="1" height="1"/><rect x="3" y="26" width="1" height="1"/><rect x="4" y="26" width="1" height="1"/><rect x="5" y="26" width="1" height="1"/><rect x="6" y="26" width="1" height="1"/><rect x="7" y="26" width="1" height="1"/><rect x="8" y="26" width="1" height="1"/><rect x="10" y="26" width="1" height="1"/><rect x="11" y="26" width="1" height="1"/><rect x="12" y="26" width="1" height="1"/><rect x="14" y="26" width="1" height="1"/><rect x="15" y="26" width="1" height="1"/><rect x="17" y="26" width="1" height="1"/><rect x="19" y="26" width="1" height="1"/><rect x="20" y="26" width="1" height="1"/><rect x="24" y="26" width="1" height="1"/><rect x="25" y="26" width="1" height="1"/><rect x="26" y="26" width="1" height="1"/></svg></div>
                <a href="/demo" onclick="trackEvent('click_demo',{source:'qr_card_mobile'})" class="md:hidden inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">Ouvrir la démo <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <p class="mt-5 text-sm font-semibold text-ink">Scannez, comme vos visiteurs le feront.</p>
                <p class="mt-1 text-xs text-muted-foreground">Ce QR ouvre la démo QRMap.</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="relative bg-ink text-white py-28">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-primary/10 blur-[140px]"></div>
    <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
        <div class="max-w-2xl" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">Produit</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold leading-tight">Une plateforme. Six super-pouvoirs.</h2>
        </div>
        <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- feature cards -->
            <div data-reveal class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="map" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">Navigation 2D interactive</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Plans d'étage dynamiques mis à jour en temps réel.</p>
            </div>
            <div data-reveal style="animation-delay:80ms" class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="building-2" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">Visualisation immersive</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Rendu du bâtiment et exploration libre.</p>
            </div>
            <div data-reveal style="animation-delay:160ms" class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="smartphone" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">PWA offline-ready</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Fonctionne en 2G/3G. Aucun téléchargement sur app store.</p>
            </div>
            <div data-reveal style="animation-delay:240ms" class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="bar-chart-3" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">Tableau de bord analytique</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Statistiques de scans en temps réel, données d'affluence, rapports.</p>
            </div>
            <div data-reveal style="animation-delay:320ms" class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="network" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">Architecture multi-sites</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Une plateforme pour un réseau hospitalier, une université ou une banque.</p>
            </div>
            <div data-reveal style="animation-delay:400ms" class="group rounded-2xl bg-white/[0.04] border border-white/10 border-l-4 border-l-primary p-7 hover:bg-white/[0.07] hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center mb-5"><i data-lucide="siren" class="w-6 h-6 text-primary"></i></div>
                <h3 class="font-display text-lg font-bold">Module évacuation d'urgence</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Guidage dynamique vers les sorties, conforme sécurité incendie.</p>
            </div>
        </div>
    </div>
</section>

<!-- MARKETS -->
<section id="marches" class="bg-mist py-28">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">Marchés</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-ink leading-tight">Conçu pour les infrastructures qui comptent.</h2>
        </div>
        <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div data-reveal class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="heart-pulse" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Santé</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Hôpitaux, cliniques, centres d'urgence.</p>
            </div>
            <div data-reveal style="animation-delay:100ms" class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="banknote" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Banques</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Agences multi-niveaux, expérience client.</p>
            </div>
            <div data-reveal style="animation-delay:200ms" class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="landmark" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Administrations</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Ministères, mairies, CNPS.</p>
            </div>
            <div data-reveal style="animation-delay:300ms" class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="graduation-cap" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Universités</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Campus, grandes écoles.</p>
            </div>
            <div data-reveal style="animation-delay:400ms" class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="building-2" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Entreprises</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Bâtiments privés à multiniveaux.</p>
            </div>
            <div data-reveal style="animation-delay:500ms" class="group rounded-2xl bg-white border border-border p-7 hover:border-primary hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-5"><i data-lucide="tent" class="w-7 h-7 text-primary"></i></div>
                <h3 class="font-display text-xl font-bold text-ink">Événements</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Foires, salons, conférences, concerts : un plan temporaire déployé en quelques jours.</p>
            </div>
        </div>
    </div>
</section>

<!-- STATS BANNER -->
<section class="bg-primary text-primary-foreground py-20 relative overflow-hidden">
    <div class="absolute inset-0 grid-bg opacity-30"></div>
    <div class="relative mx-auto max-w-7xl px-5 sm:px-8 grid grid-cols-2 lg:grid-cols-4 gap-10">
        <div data-reveal class="text-center lg:text-left"><p class="font-display text-4xl sm:text-5xl font-bold leading-none">&lt; 2 min</p><p class="mt-3 font-semibold">Délai d'orientation visé</p><p class="mt-1 text-sm text-white/70">vs 10–25 min aujourd'hui</p></div>
        <div data-reveal style="animation-delay:100ms" class="text-center lg:text-left"><p class="font-display text-4xl sm:text-5xl font-bold leading-none">200 sites</p><p class="mt-3 font-semibold">Marché adressable</p><p class="mt-1 text-sm text-white/70">au Cameroun</p></div>
        <div data-reveal style="animation-delay:200ms" class="text-center lg:text-left"><p class="font-display text-4xl sm:text-5xl font-bold leading-none">84%</p><p class="mt-3 font-semibold">Pénétration mobile</p><p class="mt-1 text-sm text-white/70">au Cameroun</p></div>
        <div data-reveal style="animation-delay:300ms" class="text-center lg:text-left"><p class="font-display text-4xl sm:text-5xl font-bold leading-none">0 FCFA</p><p class="mt-3 font-semibold">Matériel spécifique</p><p class="mt-1 text-sm text-white/70">requis</p></div>
    </div>
</section>

<!-- PRICING -->
<section id="tarifs" class="bg-mist py-28">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">Tarifs</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-ink leading-tight">Une offre pour chaque bâtiment.</h2>
            <p class="mt-5 text-muted-foreground leading-relaxed">Chaque forfait comprend une <strong class="text-ink">installation</strong> (paiement unique) et un <strong class="text-ink">abonnement annuel</strong> à la plateforme. Le tarif s'adapte à la taille de votre site.</p>
        </div>

        <div class="mt-16 grid lg:grid-cols-3 gap-6 items-stretch">

            <!-- STARTER -->
            <div data-reveal class="flex flex-col rounded-3xl bg-white border border-border p-8">
                <p class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Starter</p>
                <h3 class="mt-2 font-display text-2xl font-bold text-ink">Le site unique</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Pour une clinique, une agence bancaire ou une administration à site unique.</p>
                <div class="mt-6 pb-6 border-b border-border">
                    <p class="font-display text-3xl font-bold text-ink">Sur devis</p>
                    <p class="text-xs text-muted-foreground mt-1">Installation + abonnement annuel</p>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-ink-2 flex-1">
                    <li class="flex gap-3"><i data-lucide="building" class="w-5 h-5 text-primary shrink-0"></i>1 bâtiment · jusqu'à 3 étages</li>
                    <li class="flex gap-3"><i data-lucide="qr-code" class="w-5 h-5 text-primary shrink-0"></i>Jusqu'à 10 QR codes</li>
                    <li class="flex gap-3"><i data-lucide="map-pin" class="w-5 h-5 text-primary shrink-0"></i>Jusqu'à 20 destinations</li>
                    <li class="flex gap-3"><i data-lucide="map" class="w-5 h-5 text-primary shrink-0"></i>Navigation 2D interactive</li>
                    <li class="flex gap-3"><i data-lucide="refresh-cw" class="w-5 h-5 text-primary shrink-0"></i>Mises à jour à distance</li>
                    <li class="flex gap-3"><i data-lucide="message-circle" class="w-5 h-5 text-primary shrink-0"></i>Support email & WhatsApp</li>
                </ul>
                <a href="#contact" onclick="trackEvent('click_pricing',{plan:'starter'})" class="mt-8 inline-flex w-full justify-center rounded-full border border-ink px-6 py-3.5 text-sm font-semibold text-ink hover:bg-ink hover:text-white transition">Demander un devis</a>
            </div>

            <!-- PRO (mis en avant) -->
            <div data-reveal style="animation-delay:100ms" class="relative flex flex-col rounded-3xl bg-ink text-white p-8 border border-primary glow-green lg:scale-[1.03]">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-bold text-primary-foreground whitespace-nowrap">Le plus choisi</span>
                <p class="text-sm font-semibold text-primary uppercase tracking-wider">Pro</p>
                <h3 class="mt-2 font-display text-2xl font-bold">Le bâtiment complexe</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Pour un hôpital, un centre commercial, une université ou un grand siège.</p>
                <div class="mt-6 pb-6 border-b border-white/10">
                    <p class="font-display text-3xl font-bold">Sur devis</p>
                    <p class="text-xs text-white/50 mt-1">Installation + abonnement annuel</p>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-white/85 flex-1">
                    <li class="flex gap-3"><i data-lucide="building-2" class="w-5 h-5 text-primary shrink-0"></i>Jusqu'à 3 bâtiments · étages étendus</li>
                    <li class="flex gap-3"><i data-lucide="qr-code" class="w-5 h-5 text-primary shrink-0"></i>Jusqu'à 25 QR codes</li>
                    <li class="flex gap-3"><i data-lucide="map-pin" class="w-5 h-5 text-primary shrink-0"></i>Jusqu'à 50 destinations</li>
                    <li class="flex gap-3"><i data-lucide="box" class="w-5 h-5 text-primary shrink-0"></i>Tout le Starter</li>
                    <li class="flex gap-3"><i data-lucide="bar-chart-3" class="w-5 h-5 text-primary shrink-0"></i>Tableau de bord analytique</li>
                    <li class="flex gap-3"><i data-lucide="siren" class="w-5 h-5 text-primary shrink-0"></i>Module évacuation d'urgence</li>
                    <li class="flex gap-3"><i data-lucide="zap" class="w-5 h-5 text-primary shrink-0"></i>Support prioritaire</li>
                </ul>
                <a href="#contact" onclick="trackEvent('click_pricing',{plan:'pro'})" class="mt-8 inline-flex w-full justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition">Demander un devis</a>
            </div>

            <!-- ENTERPRISE -->
            <div data-reveal style="animation-delay:200ms" class="flex flex-col rounded-3xl bg-white border border-border p-8">
                <p class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Enterprise</p>
                <h3 class="mt-2 font-display text-2xl font-bold text-ink">Le réseau multi-sites</h3>
                <p class="mt-2 text-sm text-muted-foreground leading-relaxed">Pour un réseau hospitalier, un groupe bancaire ou une université multi-campus.</p>
                <div class="mt-6 pb-6 border-b border-border">
                    <p class="font-display text-3xl font-bold text-ink">Sur mesure</p>
                    <p class="text-xs text-muted-foreground mt-1">Tarification dédiée</p>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-ink-2 flex-1">
                    <li class="flex gap-3"><i data-lucide="network" class="w-5 h-5 text-primary shrink-0"></i>Bâtiments, étages, QR & destinations illimités</li>
                    <li class="flex gap-3"><i data-lucide="layout-dashboard" class="w-5 h-5 text-primary shrink-0"></i>Tout le Pro + gestion multi-sites centralisée</li>
                    <li class="flex gap-3"><i data-lucide="plug" class="w-5 h-5 text-primary shrink-0"></i>Intégrations sur mesure</li>
                    <li class="flex gap-3"><i data-lucide="user-check" class="w-5 h-5 text-primary shrink-0"></i>Accompagnement dédié</li>
                    <li class="flex gap-3"><i data-lucide="shield-check" class="w-5 h-5 text-primary shrink-0"></i>Engagement de service (SLA)</li>
                </ul>
                <a href="#contact" onclick="trackEvent('click_pricing',{plan:'enterprise'})" class="mt-8 inline-flex w-full justify-center rounded-full border border-ink px-6 py-3.5 text-sm font-semibold text-ink hover:bg-ink hover:text-white transition">Nous consulter</a>
            </div>
        </div>

        <p class="mt-10 text-center text-sm text-muted-foreground max-w-2xl mx-auto">
            Besoin de dépasser les limites d'un forfait (QR codes ou destinations supplémentaires) ? C'est possible : <a href="#contact" class="text-primary hover:underline">parlons de votre projet</a>.
        </p>
    </div>
</section>

<!-- À PROPOS -->
<section id="apropos" class="relative bg-ink text-white py-28 overflow-hidden">
    <div class="absolute inset-0 grid-bg opacity-30"></div>
    <div class="relative mx-auto max-w-3xl px-5 sm:px-8 text-center" data-reveal>
        <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">À propos</p>
        <h2 class="font-display text-4xl sm:text-5xl font-bold leading-tight">Conçu au Cameroun, pensé pour tous.</h2>
        <p class="mt-8 text-lg text-white/70 leading-relaxed">QRMap est né d'un constat simple : chaque jour, des milliers de visiteurs se perdent dans nos hôpitaux, banques, universités et administrations, faute de repères. Nous avons créé la première solution de navigation intérieure conçue pour nos réalités : mobile-first, légère, sans application à installer et sans infrastructure coûteuse. Notre ambition : qu'on ne se perde plus jamais dans un bâtiment, à Yaoundé comme partout ailleurs.</p>
        <!-- TODO: ajouter photo/équipe quand disponible -->
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="bg-mist py-28">
    <div class="mx-auto max-w-3xl px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto" data-reveal>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-4">FAQ</p>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-ink leading-tight">Questions fréquentes.</h2>
        </div>
        <div class="mt-14 space-y-4">
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>Faut-il installer une application ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Non. QRMap fonctionne directement dans le navigateur de votre téléphone. Scannez le QR code, le plan s'ouvre immédiatement.</p>
            </details>
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>Faut-il une connexion internet ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Une connexion suffit au premier chargement. QRMap est conçu pour les connexions lentes (2G/3G) et reste utilisable ensuite même si le réseau est instable à l'intérieur du bâtiment.</p>
            </details>
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>Ça marche sur tous les téléphones ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Oui, sur tout smartphone capable de scanner un QR code — soit via l'appareil photo, soit via une application de scan. Aucun téléphone récent ou haut de gamme n'est nécessaire.</p>
            </details>
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>Combien de temps prend le déploiement ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Selon la taille du bâtiment, de quelques jours à quelques semaines : cartographie, génération des QR codes, impression et pose. Aucun capteur, aucune balise, aucun câblage.</p>
            </details>
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>Et si mon bâtiment change (travaux, nouveaux services) ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Le plan est mis à jour à distance depuis notre plateforme. Les QR codes déjà affichés restent valables : ils pointent toujours vers la version à jour.</p>
            </details>
            <details class="group rounded-2xl bg-white border border-border px-6 py-5 transition hover:border-primary/40" data-reveal>
                <summary class="flex items-center justify-between cursor-pointer list-none font-display font-bold text-ink text-lg"><span>QRMap fonctionne-t-il pour un événement temporaire ?</span><i data-lucide="plus" class="w-5 h-5 text-primary shrink-0 transition-transform group-open:rotate-45"></i></summary>
                <p class="mt-4 text-muted-foreground leading-relaxed">Oui. Foires, salons, conférences, concerts : nous déployons un plan temporaire en quelques jours, retiré à la fin de l'événement.</p>
            </details>
        </div>
    </div>
</section>

<!-- CTA -->
<section id="contact" class="relative bg-ink text-white py-32 overflow-hidden">
    <div class="absolute inset-0 grid-bg opacity-40"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full bg-primary/20 blur-[160px]"></div>
    <div class="relative mx-auto max-w-4xl px-5 sm:px-8 text-center" data-reveal>
        <h2 class="font-display text-4xl sm:text-6xl font-bold leading-tight">Prêt à cartographier <span class="text-primary">votre bâtiment</span> ?</h2>
        <p class="mt-6 text-lg text-white/70 max-w-2xl mx-auto leading-relaxed">Rejoignez les organisations qui transforment l'expérience de leurs visiteurs.</p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-5">
            <a href="mailto:contact@qrmap.app" class="inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-base font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">Planifier une démonstration gratuite <i data-lucide="arrow-right" class="w-5 h-5"></i></a>
            <a href="#" class="text-sm font-medium text-white/70 hover:text-primary underline underline-offset-4">Télécharger le dossier de présentation</a>
        </div>

        <!-- Formulaire de capture de leads -->
        <!-- TODO: brancher sur un backend/CRM plus tard -->
        <div class="mt-14 max-w-2xl mx-auto text-left rounded-2xl border border-white/10 bg-white/[0.04] p-8 backdrop-blur">
            <p class="font-display text-xl font-bold text-white">Parlez-nous de votre bâtiment</p>
            <p class="mt-2 text-sm text-white/60">Remplissez ce formulaire, nous vous recontactons rapidement.</p>
            <form id="leadForm" class="mt-6 grid sm:grid-cols-2 gap-4" onsubmit="return sendLead(event)">
                <div class="sm:col-span-1">
                    <label class="block text-xs text-white/50 mb-1.5">Nom complet *</label>
                    <input name="nom" type="text" required class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-primary focus:outline-none transition" placeholder="Votre nom">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-xs text-white/50 mb-1.5">Organisation *</label>
                    <input name="org" type="text" required class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-primary focus:outline-none transition" placeholder="Votre structure">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-xs text-white/50 mb-1.5">Téléphone / WhatsApp *</label>
                    <input name="tel" type="tel" required class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-primary focus:outline-none transition" placeholder="+237 6XX XXX XXX">
                </div>
                <div class="sm:col-span-1">
                    <label class="block text-xs text-white/50 mb-1.5">Email</label>
                    <input name="email" type="email" class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-primary focus:outline-none transition" placeholder="vous@exemple.com">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-white/50 mb-1.5">Type de bâtiment</label>
                    <select name="type" class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white focus:border-primary focus:outline-none transition">
                        <option class="bg-ink" value="Hôpital / Clinique">Hôpital / Clinique</option>
                        <option class="bg-ink" value="Banque">Banque</option>
                        <option class="bg-ink" value="Administration">Administration</option>
                        <option class="bg-ink" value="Université / École">Université / École</option>
                        <option class="bg-ink" value="Centre commercial">Centre commercial</option>
                        <option class="bg-ink" value="Événement (foire, salon, concert)">Événement (foire, salon, concert)</option>
                        <option class="bg-ink" value="Autre">Autre</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-white/50 mb-1.5">Message</label>
                    <textarea name="message" rows="3" class="w-full rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-primary focus:outline-none transition resize-none" placeholder="Décrivez votre besoin..."></textarea>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-primary px-8 py-3.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">
                        Être recontacté <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- MENTIONS LÉGALES + CONFIDENTIALITÉ -->
<section id="legal" class="bg-mist border-t border-border py-20">
    <div class="mx-auto max-w-4xl px-5 sm:px-8 grid md:grid-cols-2 gap-10 text-sm text-muted-foreground leading-relaxed">
        <div id="mentions">
            <p class="text-primary font-semibold text-xs uppercase tracking-wider mb-3">Mentions légales</p>
            <p><strong class="text-ink">Éditeur :</strong> QRMap, Yaoundé, Cameroun.</p>
            <p class="mt-2"><strong class="text-ink">Contact :</strong> <a class="text-primary hover:underline" href="mailto:contact@qrmap.app">contact@qrmap.app</a> · WhatsApp <a class="text-primary hover:underline" href="https://wa.me/237695249549">+237 695 249 549</a>.</p>
            <p class="mt-2"><strong class="text-ink">Hébergeur :</strong> <!-- TODO: renseigner l'hébergeur retenu --> à renseigner.</p>
        </div>
        <div id="confidentialite">
            <p class="text-primary font-semibold text-xs uppercase tracking-wider mb-3">Confidentialité</p>
            <p>Les informations transmises via le formulaire (nom, organisation, téléphone, email, message) sont utilisées <strong class="text-ink">uniquement pour vous recontacter</strong>.</p>
            <p class="mt-2">Elles ne sont ni revendues, ni cédées à des tiers. Vous pouvez demander leur suppression à tout moment via <a class="text-primary hover:underline" href="mailto:contact@qrmap.app">contact@qrmap.app</a>.</p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-ink-2 text-white/70 border-t-2 border-primary">
    <div class="mx-auto max-w-7xl px-5 sm:px-8 py-16 grid md:grid-cols-4 gap-10">
        <div class="md:col-span-2">
            <div class="flex items-center gap-2 font-display font-bold text-xl text-white"><span class="text-primary text-2xl">◆</span> QRMap</div>
            <p class="mt-4 text-sm max-w-sm leading-relaxed">Le GPS s'arrête à l'entrée. QRMap commence là.</p>
            <p class="mt-6 text-xs text-white/40">Navigation intérieure 2D/3D · Sans GPS · Sans installation · Activé par un scan QR</p>
            <!-- Réseaux sociaux -->
            <!-- TODO: remplacer par les URLs des pages LinkedIn et Facebook une fois créées -->
            <div class="mt-6 flex items-center gap-3">
                <a href="#" aria-label="LinkedIn" class="w-9 h-9 rounded-full border border-white/15 flex items-center justify-center text-white/70 hover:text-primary hover:border-primary transition"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
                <a href="#" aria-label="Facebook" class="w-9 h-9 rounded-full border border-white/15 flex items-center justify-center text-white/70 hover:text-primary hover:border-primary transition"><i data-lucide="facebook" class="w-4 h-4"></i></a>
            </div>
        </div>
        <div>
            <p class="font-display font-bold text-white mb-4">Navigation</p>
            <ul class="space-y-2 text-sm">
                <li><a href="#produit" class="hover:text-primary">Produit</a></li>
                <li><a href="#marches" class="hover:text-primary">Marchés</a></li>
                <li><a href="#tarifs" class="hover:text-primary">Tarifs</a></li>
                <li><a href="#apropos" class="hover:text-primary">À propos</a></li>
                <li><a href="#contact" class="hover:text-primary">Contact</a></li>
            </ul>
        </div>
        <div>
            <p class="font-display font-bold text-white mb-4">Contact</p>
            <ul class="space-y-2 text-sm">
                <li>Gare marchande - Yaoundé, Cameroun</li>
                <li><a class="hover:text-primary" href="mailto:contact@qrmap.app">contact@qrmap.app</a></li>
                <li><a class="hover:text-primary" href="https://wa.me/237695249549">WhatsApp : +237 695 249 549</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-5 sm:px-8 py-6 text-xs text-white/40 flex flex-wrap items-center justify-between gap-3">
            <p>© 2026 QRMap · Gare marchande - Yaoundé, Cameroun · contact@qrmap.app</p>
            <p class="flex items-center gap-3"><a href="#mentions" class="hover:text-primary">Mentions légales</a><span class="text-white/20">·</span><a href="#confidentialite" class="hover:text-primary">Confidentialité</a><span class="text-white/20">·</span><span>Made with ◆ in Africa</span></p>
        </div>
    </div>
</footer>

<!-- Bouton flottant WhatsApp -->
<a href="https://wa.me/237695249549" target="_blank" rel="noopener" aria-label="Contacter sur WhatsApp" onclick="trackEvent('click_whatsapp_float')"
   class="fixed bottom-6 right-6 z-[9999] w-14 h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-2xl hover:scale-110 transition-transform">
    <i data-lucide="message-circle" class="w-7 h-7"></i>
</a>

<script>
    // Menu mobile (hamburger)
    function toggleMenu(){
        const m = document.getElementById('mobileMenu');
        const btn = document.getElementById('menuBtn');
        const open = m.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.getElementById('menuIcon').setAttribute('data-lucide', open ? 'x' : 'menu');
        lucide.createIcons();
    }
    function closeMenu(){
        const m = document.getElementById('mobileMenu');
        if (!m.classList.contains('hidden')) {
            m.classList.add('hidden');
            document.getElementById('menuBtn').setAttribute('aria-expanded','false');
            document.getElementById('menuIcon').setAttribute('data-lucide','menu');
            lucide.createIcons();
        }
    }

    // Icônes Lucide
    lucide.createIcons();

    // Formulaire de leads -> WhatsApp pré-rempli (sans backend)
    // TODO: brancher sur un backend/CRM plus tard
    function sendLead(e){
        e.preventDefault();
        const f = e.target;
        const nom = f.nom.value.trim();
        const org = f.org.value.trim();
        const tel = f.tel.value.trim();
        const email = f.email.value.trim();
        const type = f.type.value;
        const message = f.message.value.trim();

        let txt = `Bonjour QRMap, je suis ${nom} de ${org}. `;
        txt += `Type de bâtiment : ${type}. `;
        if (message) txt += `${message}. `;
        txt += `Me recontacter au ${tel}.`;
        if (email) txt += ` Email : ${email}.`;

        trackEvent('submit_lead_form',{type:type});
        const url = 'https://wa.me/237695249549?text=' + encodeURIComponent(txt);
        window.open(url, '_blank');
        return false;
    }

    // Reveal on scroll
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('animate-reveal'); io.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
</script>

<script>
// ===== Traduction FR <-> EN (échange de texte, sans toucher au HTML) =====
(function(){
    const DICT = {"Produit": "Product", "Marchés": "Markets", "Démo": "Demo", "Présentation": "Presentation", "Tarifs": "Pricing", "FAQ": "FAQ", "Contact": "Contact", "Essayer la démo": "Try the demo", "Navigation intérieure · Safe buildings": "Indoor navigation · Safe buildings", "Le GPS s'arrête à l'entrée.": "GPS stops at the entrance.", "QRMap commence là.": "QRMap takes over.", "Transformez un simple QR code imprimé en guide interactif 2D/3D. Sans GPS. Sans application. Depuis n'importe quel smartphone.": "Turn a simple printed QR code into an interactive 2D/3D guide. No GPS. No app. From any smartphone.", "Voir la présentation": "View presentation", "En savoir plus": "Learn more", "Sans GPS": "No GPS", "Sans app store": "No app store", "Offline-ready": "Offline-ready", "Destination": "Destination", "Service Cardiologie": "Cardiology Dept.", "2 min": "2 min", "QR scanné": "QR scanned", "Le constat": "The problem", "Le GPS s'arrête à l'entrée des bâtiments.": "GPS stops at the door of buildings.", "10 à 25 min": "10 to 25 min", "perdues par visiteur": "lost per visitor", "dans les grands bâtiments à cause d'une signalétique insuffisante : hôpitaux, administrations, campus, centres commerciaux.": "in large buildings due to poor signage: hospitals, government offices, campuses, shopping malls.", "0 solution": "0 solution", "locale déployée": "deployed locally", "aucune navigation intérieure commerciale n'est déployée au Cameroun aujourd'hui, quel que soit le type de bâtiment.": "no commercial indoor navigation is deployed in Cameroon today, whatever the building type.", "Des milliers": "Thousands", "de visiteurs par jour": "of visitors per day", "dans les hôpitaux, banques, administrations et grands événements — sans aucun système d'orientation dynamique.": "in hospitals, banks, government offices and large events — with no dynamic wayfinding system.", "Comment ça marche": "How it works", "3 étapes. Aucune infrastructure.": "3 steps. No infrastructure.", "On cartographie": "We map it", "Nous modélisons votre bâtiment : étages, salles, couloirs, points d'intérêt.": "We model your building: floors, rooms, corridors, points of interest.", "On installe les QR codes": "We install the QR codes", "Impression et placement aux points stratégiques. Aucun matériel spécifique.": "Printed and placed at key points. No special hardware.", "Vos visiteurs scannent": "Your visitors scan", "Un scan suffit pour ouvrir le plan interactif. Aucune installation requise.": "One scan opens the interactive map. No install required.", "Ouvrir la démo": "Open the demo", "Scannez, comme vos visiteurs le feront.": "Scan it, just like your visitors will.", "Ce QR ouvre la démo QRMap.": "This QR opens the QRMap demo.", "Une plateforme. Six super-pouvoirs.": "One platform. Six superpowers.", "Navigation 2D interactive": "Interactive 2D navigation", "Plans d'étage dynamiques mis à jour en temps réel.": "Dynamic floor plans updated in real time.", "Visualisation 3D immersive": "Immersive 3D visualization", "Rendu THREE.js du bâtiment, rotation et exploration libre.": "THREE.js building render, free rotation and exploration.", "PWA offline-ready": "Offline-ready PWA", "Fonctionne en 2G/3G. Aucun téléchargement sur app store.": "Works on 2G/3G. No app store download.", "Tableau de bord analytique": "Analytics dashboard", "Statistiques de scans en temps réel, données d'affluence, rapports.": "Real-time scan stats, footfall data, reports.", "Architecture multi-sites": "Multi-site architecture", "Une plateforme pour un réseau hospitalier, une université ou une banque.": "One platform for a hospital network, a university or a bank.", "Module évacuation d'urgence": "Emergency evacuation module", "Guidage dynamique vers les sorties, conforme sécurité incendie.": "Dynamic guidance to exits, fire-safety compliant.", "Conçu pour les infrastructures qui comptent.": "Built for the infrastructure that matters.", "Santé": "Healthcare", "Hôpitaux, cliniques, centres d'urgence.": "Hospitals, clinics, emergency centers.", "Banques": "Banks", "Agences multi-niveaux, expérience client.": "Multi-level branches, customer experience.", "Administrations": "Government", "Ministères, mairies, CNPS.": "Ministries, city halls, public agencies.", "Universités": "Universities", "Campus, grandes écoles.": "Campuses, higher-education schools.", "Entreprises": "Enterprises", "Bâtiments privés à multiniveaux.": "Private multi-level buildings.", "Événements": "Events", "Foires, salons, conférences, concerts : un plan temporaire déployé en quelques jours.": "Fairs, expos, conferences, concerts: a temporary map deployed in a few days.", "< 2 min": "< 2 min", "Délai d'orientation visé": "Target wayfinding time", "vs 10–25 min aujourd'hui": "vs 10–25 min today", "200 sites": "200 sites", "Marché adressable": "Addressable market", "au Cameroun": "in Cameroon", "Pénétration mobile": "Mobile penetration", "0 FCFA": "0 FCFA", "Matériel spécifique": "Special hardware", "requis": "required", "Une offre pour chaque bâtiment.": "A plan for every building.", "installation": "installation", "abonnement annuel": "annual subscription", "à la plateforme. Le tarif s'adapte à la taille de votre site.": "to the platform. Pricing scales with the size of your site.", "Starter": "Starter", "Le site unique": "The single site", "Pour une clinique, une agence bancaire ou une administration à site unique.": "For a clinic, a bank branch or a single-site government office.", "Sur devis": "On quote", "Installation + abonnement annuel": "Installation + annual subscription", "1 bâtiment · jusqu'à 3 étages": "1 building · up to 3 floors", "Jusqu'à 10 QR codes": "Up to 10 QR codes", "Jusqu'à 20 destinations": "Up to 20 destinations", "Mises à jour à distance": "Remote updates", "Support email & WhatsApp": "Email & WhatsApp support", "Demander un devis": "Request a quote", "Le plus choisi": "Most popular", "Pro": "Pro", "Le bâtiment complexe": "The complex building", "Pour un hôpital, un centre commercial, une université ou un grand siège.": "For a hospital, a mall, a university or a large HQ.", "Jusqu'à 3 bâtiments · étages étendus": "Up to 3 buildings · extended floors", "Jusqu'à 40 QR codes": "Up to 40 QR codes", "Jusqu'à 60 destinations": "Up to 60 destinations", "Tout le Starter + vue 3D immersive": "Everything in Starter + immersive 3D view", "Support prioritaire": "Priority support", "Enterprise": "Enterprise", "Le réseau multi-sites": "The multi-site network", "Pour un réseau hospitalier, un groupe bancaire ou une université multi-campus.": "For a hospital network, a banking group or a multi-campus university.", "Sur mesure": "Custom", "Tarification dédiée": "Dedicated pricing", "Bâtiments, étages, QR & destinations illimités": "Unlimited buildings, floors, QR codes & destinations", "Tout le Pro + gestion multi-sites centralisée": "Everything in Pro + centralized multi-site management", "Intégrations sur mesure": "Custom integrations", "Accompagnement dédié": "Dedicated onboarding", "Engagement de service (SLA)": "Service-level agreement (SLA)", "Nous consulter": "Contact us", "parlons de votre projet": "let's talk about your project", "À propos": "About", "Conçu au Cameroun, pensé pour l'Afrique.": "Built in Cameroon, made for Africa.", "QRMap est né d'un constat simple : chaque jour, des milliers de visiteurs se perdent dans nos hôpitaux, banques, universités et administrations, faute de repères. Nous avons créé la première solution de navigation intérieure conçue pour nos réalités : mobile-first, légère, sans application à installer et sans infrastructure coûteuse. Notre ambition : qu'on ne se perde plus jamais dans un bâtiment, à Yaoundé comme partout en Afrique.": "QRMap was born from a simple observation: every day, thousands of visitors get lost in our hospitals, banks, universities and government offices, for lack of guidance. We built the first indoor navigation solution designed for our realities: mobile-first, lightweight, with no app to install and no costly infrastructure. Our ambition: that no one ever gets lost in a building again, in Yaoundé as everywhere in Africa.", "Questions fréquentes.": "Frequently asked questions.", "Faut-il installer une application ?": "Do I need to install an app?", "Non. QRMap fonctionne directement dans le navigateur de votre téléphone. Scannez le QR code, le plan s'ouvre immédiatement.": "No. QRMap runs directly in your phone's browser. Scan the QR code and the map opens instantly.", "Faut-il une connexion internet ?": "Do I need an internet connection?", "Une connexion suffit au premier chargement. QRMap est conçu pour les connexions lentes (2G/3G) et reste utilisable ensuite même si le réseau est instable à l'intérieur du bâtiment.": "A connection is only needed for the first load. QRMap is built for slow connections (2G/3G) and stays usable even if the network is unstable inside the building.", "Ça marche sur tous les téléphones ?": "Does it work on all phones?", "Oui, sur tout smartphone capable de scanner un QR code — soit via l'appareil photo, soit via une application de scan. Aucun téléphone récent ou haut de gamme n'est nécessaire.": "Yes, on any smartphone that can scan a QR code — either via the camera or a scanning app. No recent or high-end phone required.", "Combien de temps prend le déploiement ?": "How long does deployment take?", "Selon la taille du bâtiment, de quelques jours à quelques semaines : cartographie, génération des QR codes, impression et pose. Aucun capteur, aucune balise, aucun câblage.": "Depending on the building size, from a few days to a few weeks: mapping, QR code generation, printing and installation. No sensors, no beacons, no wiring.", "Et si mon bâtiment change (travaux, nouveaux services) ?": "What if my building changes (renovations, new services)?", "Le plan est mis à jour à distance depuis notre plateforme. Les QR codes déjà affichés restent valables : ils pointent toujours vers la version à jour.": "The map is updated remotely from our platform. QR codes already posted stay valid: they always point to the latest version.", "QRMap fonctionne-t-il pour un événement temporaire ?": "Does QRMap work for a temporary event?", "Oui. Foires, salons, conférences, concerts : nous déployons un plan temporaire en quelques jours, retiré à la fin de l'événement.": "Yes. Fairs, expos, conferences, concerts: we deploy a temporary map in a few days, removed at the end of the event.", "votre bâtiment": "your building", "Les premiers déploiements pilotes sont en préparation. Rejoignez les organisations qui transforment l'expérience de leurs visiteurs.": "The first pilot deployments are underway. Join the organizations transforming their visitors' experience.", "Planifier une démonstration gratuite": "Schedule a free demo", "Télécharger le dossier de présentation": "Download the pitch deck", "Parlez-nous de votre bâtiment": "Tell us about your building", "Remplissez ce formulaire, nous vous recontactons rapidement.": "Fill in this form and we'll get back to you quickly.", "Nom complet *": "Full name *", "Organisation *": "Organization *", "Téléphone / WhatsApp *": "Phone / WhatsApp *", "Email": "Email", "Type de bâtiment": "Building type", "Hôpital / Clinique": "Hospital / Clinic", "Banque": "Bank", "Administration": "Government office", "Université / École": "University / School", "Centre commercial": "Shopping mall", "Événement (foire, salon, concert)": "Event (fair, expo, concert)", "Autre": "Other", "Message": "Message", "Être recontacté": "Get contacted", "Mentions légales": "Legal notice", "Éditeur :": "Publisher:", "QRMap, Yaoundé, Cameroun.": "QRMap, Yaoundé, Cameroon.", "Contact :": "Contact:", "· WhatsApp": "· WhatsApp", "Hébergeur :": "Host:", "à renseigner.": "to be provided.", "Confidentialité": "Privacy", "Les informations transmises via le formulaire (nom, organisation, téléphone, email, message) sont utilisées": "The information submitted via the form (name, organization, phone, email, message) is used", "uniquement pour vous recontacter": "solely to contact you back", "Elles ne sont ni revendues, ni cédées à des tiers. Vous pouvez demander leur suppression à tout moment via": "It is never resold or shared with third parties. You may request its deletion at any time via", "Le GPS s'arrête à l'entrée. QRMap commence là.": "GPS stops at the entrance. QRMap takes over.", "Navigation intérieure 2D/3D · Sans GPS · Sans installation · Activé par un scan QR": "Indoor navigation 2D/3D · No GPS · No install · Powered by a QR scan", "Navigation": "Navigation", "Yaoundé, Cameroun": "Yaoundé, Cameroon", "WhatsApp : +237 695 249 549": "WhatsApp: +237 695 249 549", "© 2026 QRMap · Yaoundé, Cameroun · contact@qrmap.app": "© 2026 QRMap · Yaoundé, Cameroon · contact@qrmap.app", "Made with ◆ in Africa": "Made with ◆ in Africa", "Votre nom": "Your name", "Votre structure": "Your organization", "+237 6XX XXX XXX": "+237 6XX XXX XXX", "vous@exemple.com": "you@example.com", "Décrivez votre besoin...": "Describe your need..."};           // FR -> EN
    let snapshot = null;             // textes FR d'origine (nœuds + placeholders)

    function collectNodes(){
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode(n){
                if(!n.nodeValue || !n.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                const p = n.parentNode;
                if(p && (p.nodeName === 'SCRIPT' || p.nodeName === 'STYLE')) return NodeFilter.FILTER_REJECT;
                return NodeFilter.FILTER_ACCEPT;
            }
        });
        const nodes = []; let n;
        while(n = walker.nextNode()) nodes.push(n);
        return nodes;
    }

    function snap(){
        // mémorise le FR d'origine une seule fois
        const nodes = collectNodes();
        snapshot = { text: nodes.map(n => ({node:n, fr:n.nodeValue})),
                     ph: Array.from(document.querySelectorAll('[placeholder]')).map(e => ({el:e, fr:e.getAttribute('placeholder')})) };
    }

    window.setLang = function(lang){
        if(!snapshot) snap();
        const toEN = (lang === 'en');
        // Textes
        snapshot.text.forEach(item => {
            const raw = item.fr;
            const key = raw.trim();
            if(toEN && DICT[key]){
                item.node.nodeValue = raw.replace(key, DICT[key]);
            } else {
                item.node.nodeValue = raw; // restaure FR
            }
        });
        // Placeholders
        snapshot.ph.forEach(item => {
            const key = (item.fr||'').trim();
            item.el.setAttribute('placeholder', (toEN && DICT[key]) ? DICT[key] : item.fr);
        });
        // <html lang> + mémorisation
        document.documentElement.setAttribute('lang', lang);
        try { localStorage.setItem('qrmap-lang', lang); } catch(e){}
        // état visuel des boutons
        document.querySelectorAll('[data-lang-btn]').forEach(b => {
            const on = b.getAttribute('data-lang-btn') === lang;
            b.classList.toggle('text-primary', on);
            b.classList.toggle('font-bold', on);
        });
        try { trackEvent('switch_lang', { lang: lang }); } catch(e){}
    };

    // Appliquer la préférence mémorisée au chargement (défaut FR)
    document.addEventListener('DOMContentLoaded', function(){
        let saved = 'fr';
        try { saved = localStorage.getItem('qrmap-lang') || 'fr'; } catch(e){}
        if(saved === 'en') setLang('en'); else setLang('fr');
    });
})();
</script>
</body>
</html>