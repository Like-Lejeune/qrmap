<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QRMap — Navigation intérieure 2D/3D</title>
    <meta name="description" content="Transformez un simple QR code en guide interactif 2D/3D. Sans GPS, sans application.">

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
            <a href="#tarifs" class="hover:text-primary transition">Tarifs</a>
            <a href="#contact" class="hover:text-primary transition">Contact</a>
        </nav>
        <a href="/demo" class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">
            Essayer la démo <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
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
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Africa-first · Safe buildings
            </div>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold leading-[1.05] tracking-tight">
                La navigation intérieure conçue pour <span class="text-primary"> pour vous.</span>
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-white/70 max-w-xl leading-relaxed">
                Transformez un simple QR code imprimé en guide interactif 2D/3D. Sans GPS. Sans application. Depuis n'importe quel smartphone.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="#contact" class="inline-flex items-center gap-2 rounded-full bg-primary px-7 py-4 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">Voir la démo <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
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
                <p class="mt-3 text-white font-semibold">perdues par patient</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">à cause d'une signalétique défaillante dans les hôpitaux.</p>
            </div>
            <div data-reveal style="animation-delay:120ms" class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 hover:border-primary/40 transition">
                <p class="font-display text-5xl font-bold text-primary leading-none">0 solution</p>
                <p class="mt-3 text-white font-semibold">locale déployée</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">aucune navigation intérieure commerciale au Cameroun aujourd'hui.</p>
            </div>
            <div data-reveal style="animation-delay:240ms" class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 hover:border-primary/40 transition">
                <p class="font-display text-5xl font-bold text-primary leading-none">800 à 2 000</p>
                <p class="mt-3 text-white font-semibold">patients par jour</p>
                <p class="mt-3 text-white/60 text-sm leading-relaxed">dans les hôpitaux de référence, sans système directionnel dynamique.</p>
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
                <h3 class="font-display text-lg font-bold">Visualisation 3D immersive</h3>
                <p class="mt-2 text-sm text-white/60 leading-relaxed">Rendu THREE.js du bâtiment, rotation et exploration libre.</p>
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
        <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
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
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-ink leading-tight">Choississez votre plan.</h2>
        </div>
        <div class="mt-16 grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
            <div data-reveal class="rounded-3xl bg-white border border-border p-10">
                <p class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Installation</p>
                <h3 class="mt-3 font-display text-2xl font-bold text-ink">Mise en service</h3>
                <p class="mt-6 font-display text-4xl font-bold text-ink">à partir de 500 000<span class="text-lg font-semibold text-muted-foreground">FCFA</span></p>
                <p class="text-sm text-muted-foreground">Paiement unique</p>
                <ul class="mt-8 space-y-3 text-sm text-ink-2">
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Modélisation 3D du bâtiment</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Configuration sur mesure</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Génération des QR codes</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Formation administrateurs</li>
                </ul>
                <a href="#contact" class="mt-10 inline-flex w-full justify-center rounded-full border border-ink px-6 py-3.5 text-sm font-semibold text-ink hover:bg-ink hover:text-white transition">Demander un devis</a>
            </div>
            <div data-reveal style="animation-delay:100ms" class="relative rounded-3xl bg-ink text-white p-10 border border-primary glow-green">
                <span class="absolute -top-3 left-10 rounded-full bg-primary px-3 py-1 text-xs font-bold text-primary-foreground">Récurrent</span>
                <p class="text-sm font-semibold text-primary uppercase tracking-wider">Abonnement SaaS</p>
                <h3 class="mt-3 font-display text-2xl font-bold">Plateforme annuelle</h3>
                <p class="mt-6 font-display text-4xl font-bold">300 000 – 800 000 <span class="text-lg font-semibold text-white/60">FCFA / an</span></p>
                <p class="text-sm text-white/60">Renouvelable</p>
                <ul class="mt-8 space-y-3 text-sm text-white/80">
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Hébergement sécurisé</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Mises à jour continues</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Support technique</li>
                    <li class="flex gap-3"><i data-lucide="check" class="w-5 h-5 text-primary shrink-0"></i>Accès tableau de bord analytique</li>
                </ul>
                <a href="#contact" class="mt-10 inline-flex w-full justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground hover:bg-primary-dark transition">Demander un devis</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section id="contact" class="relative bg-ink text-white py-32 overflow-hidden">
    <div class="absolute inset-0 grid-bg opacity-40"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full bg-primary/20 blur-[160px]"></div>
    <div class="relative mx-auto max-w-4xl px-5 sm:px-8 text-center" data-reveal>
        <h2 class="font-display text-4xl sm:text-6xl font-bold leading-tight">Prêt à cartographier <span class="text-primary">votre bâtiment</span> ?</h2>
        <p class="mt-6 text-lg text-white/70 max-w-2xl mx-auto leading-relaxed">Les 2 premiers sites pilotes sont en cours de déploiement. Rejoignez les organisations qui transforment l'expérience de leurs visiteurs.</p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-5">
            <a href="mailto:contact@qrmap.cm" class="inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-base font-semibold text-primary-foreground hover:bg-primary-dark transition glow-green">Planifier une démonstration gratuite <i data-lucide="arrow-right" class="w-5 h-5"></i></a>
            <a href="#" class="text-sm font-medium text-white/70 hover:text-primary underline underline-offset-4">Télécharger le dossier de présentation</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-ink-2 text-white/70 border-t-2 border-primary">
    <div class="mx-auto max-w-7xl px-5 sm:px-8 py-16 grid md:grid-cols-4 gap-10">
        <div class="md:col-span-2">
            <div class="flex items-center gap-2 font-display font-bold text-xl text-white"><span class="text-primary text-2xl">◆</span> QRMap</div>
            <p class="mt-4 text-sm max-w-sm leading-relaxed">Le GPS s'arrête à l'entrée. QRMap commence là où les autres s'arrêtent.</p>
            <p class="mt-6 text-xs text-white/40">Navigation intérieure 2D/3D · Sans GPS · Sans installation · Activé par un scan QR</p>
        </div>
        <div>
            <p class="font-display font-bold text-white mb-4">Navigation</p>
            <ul class="space-y-2 text-sm">
                <li><a href="#produit" class="hover:text-primary">Produit</a></li>
                <li><a href="#marches" class="hover:text-primary">Marchés</a></li>
                <li><a href="#tarifs" class="hover:text-primary">Tarifs</a></li>
                <li><a href="#" class="hover:text-primary">À propos</a></li>
                <li><a href="#contact" class="hover:text-primary">Contact</a></li>
            </ul>
        </div>
        <div>
            <p class="font-display font-bold text-white mb-4">Contact</p>
            <ul class="space-y-2 text-sm">
                <li>Yaoundé, Cameroun</li>
                <li><a class="hover:text-primary" href="mailto:contact@qrmap.cm">contact@qrmap.cm</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-5 sm:px-8 py-6 text-xs text-white/40 flex flex-wrap items-center justify-between gap-3">
            <p>© 2025 QRMap · Yaoundé, Cameroun · contact@qrmap.cm</p>
            <p>Made with ◆ in Africa</p>
        </div>
    </div>
</footer>

<script>
    // Icônes Lucide
    lucide.createIcons();

    // Reveal on scroll
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('animate-reveal'); io.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
</script>
</body>
</html>