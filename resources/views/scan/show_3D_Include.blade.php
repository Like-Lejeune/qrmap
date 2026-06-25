<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>{{ $qrcode->location_label }} — QRMap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ===================== THEME TOKENS ===================== */
        :root {
            /* Dark (par défaut) */
            --bg: #0d0f14; --bg2: #13161e; --bg3: #1a1e28;
            --border: rgba(255,255,255,0.08);
            --accent: #4fffb0; --accent-strong: #1fcf8a; --accent2: #4f9fff;
            --accent-soft: rgba(79,255,176,0.10); --accent-soft2: rgba(79,255,176,0.22);
            --text: #f0f2f5; --muted: #8892a4;

            /* Plan / pièces */
            --grid-line: rgba(255,255,255,0.045);
            --outline: rgba(79,255,176,0.18);
            --room-fill: rgba(255,255,255,0.025);
            --room-fill-muted: rgba(255,255,255,0.015);
            --room-stroke: rgba(255,255,255,0.12);
            --room-text: #c4cbd8;
            --qr-fill: #f0f2f5; --qr-text: #0d0f14;
            --walker-fill: #0d0f14;

            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
            color-scheme: dark;
        }
        :root[data-theme="light"] {
            --bg: #f3f5f9; --bg2: #ffffff; --bg3: #eef1f6;
            --border: rgba(15,23,42,0.08);
            --accent: #16a34a; --accent-strong: #15803d; --accent2: #2563eb;
            --accent-soft: rgba(22,163,74,0.08); --accent-soft2: rgba(22,163,74,0.18);
            --text: #0f172a; --muted: #64748b;

            --grid-line: rgba(15,23,42,0.045);
            --outline: rgba(15,23,42,0.10);
            --room-fill: #ffffff;
            --room-fill-muted: #f1f4f8;
            --room-stroke: #dbe1ea;
            --room-text: #475569;
            --qr-fill: #0f172a; --qr-text: #ffffff;
            --walker-fill: #ffffff;
            color-scheme: light;
        }

        body {
            background: var(--bg); color: var(--text);
            font-family: var(--font-body); min-height: 100vh;
            transition: background 0.25s ease, color 0.25s ease;
        }

        /* ===================== HEADER ===================== */
        .scan-header {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.25s ease, border-color 0.25s ease;
        }
        .logo { font-family: var(--font-head); font-weight: 800; font-size: 1rem; color: var(--accent); display: flex; align-items: center; gap: 8px; }
        .logo::before { content: ''; width: 8px; height: 8px; background: var(--accent); border-radius: 2px; transform: rotate(45deg); display: inline-block; }
        .header-right { display: flex; align-items: center; gap: 12px; }
        .scan-time { font-size: 12px; color: var(--muted); }

        .theme-toggle {
            width: 34px; height: 34px; border-radius: 10px;
            border: 1px solid var(--border); background: var(--bg3);
            color: var(--text); display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; flex-shrink: 0;
        }
        .theme-toggle:hover { border-color: var(--accent); color: var(--accent); }
        .theme-toggle svg { width: 16px; height: 16px; }
        .theme-toggle .icon-sun { display: none; }
        :root[data-theme="light"] .theme-toggle .icon-moon { display: none; }
        :root[data-theme="light"] .theme-toggle .icon-sun { display: block; }

        /* ===================== HERO ===================== */
        .location-hero {
            padding: 1.5rem 1.25rem;
            background: linear-gradient(135deg, var(--bg2) 0%, var(--accent-soft) 100%);
            border-bottom: 1px solid var(--border);
            transition: background 0.25s ease, border-color 0.25s ease;
        }
        .location-label { font-family: var(--font-head); font-size: 1.4rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .location-path { font-size: 13px; color: var(--muted); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .location-path span { color: var(--border); }

        .scan-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
            margin-top: 10px; background: var(--accent-soft); color: var(--accent-strong);
            border: 1px solid var(--accent-soft2);
        }
        .scan-badge::before { content: ''; width: 6px; height: 6px; background: currentColor; border-radius: 50%; animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

        /* ===================== TABS ===================== */
        .tabs { display: flex; background: var(--bg2); border-bottom: 1px solid var(--border); overflow-x: auto; transition: background 0.25s ease, border-color 0.25s ease; }
        .tab {
            flex: 1; padding: 12px 8px; text-align: center;
            font-size: 13px; font-weight: 500; font-family: var(--font-body);
            color: var(--muted); border: none; background: none;
            cursor: pointer; border-bottom: 2px solid transparent;
            transition: all 0.2s; white-space: nowrap;
        }
        .tab.active { color: var(--accent); border-bottom-color: var(--accent); background: var(--accent-soft); }

        .pane { display: none; padding: 1.25rem; }
        .pane.active { display: block; }

        /* ===================== PLAN 2D (SVG) ===================== */
        .map-container {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            transition: background 0.25s ease, border-color 0.25s ease;
        }
        .map-container svg { width: 100%; height: auto; display: block; }
        .room-rect { fill: var(--room-fill); stroke: var(--room-stroke); }
        .room-rect.muted { fill: var(--room-fill-muted); }
        .room-rect.highlight { fill: var(--accent-soft2); stroke: var(--accent); }
        .room-label { fill: var(--room-text); font-family: var(--font-head); font-size: 11px; font-weight: 600; text-anchor: middle; dominant-baseline: middle; pointer-events: none; }
        .room-label.highlight { fill: var(--accent-strong); font-weight: 700; }
        .floor-outline { fill: none; stroke: var(--outline); stroke-width: 2; }
        .floor-grid-line { stroke: var(--grid-line); stroke-width: 1; }

        .path-bg { fill: none; stroke: var(--accent); stroke-width: 7; stroke-linecap: round; stroke-linejoin: round; opacity: 0.18; }
        .path-fg { fill: none; stroke: var(--accent); stroke-width: 3.5; stroke-linecap: round; stroke-linejoin: round; filter: drop-shadow(0 0 4px var(--accent-soft2)); }
        .walker circle:first-child { fill: var(--walker-fill); stroke: var(--accent); stroke-width: 3; }
        .walker circle:last-child { fill: var(--accent); }
        .marker-start circle:first-child { fill: var(--accent2); }
        .marker-end circle:first-child { fill: var(--accent); }
        .marker-start circle:last-child, .marker-end circle:last-child { fill: var(--walker-fill); }
        .qr-marker rect { fill: var(--qr-fill); rx: 4; }
        .qr-marker text { fill: var(--qr-text); font-size: 10px; font-weight: 700; text-anchor: middle; dominant-baseline: middle; font-family: var(--font-head); }
        .conn-marker rect { fill: var(--accent2); }
        .conn-marker text { fill: var(--qr-fill); font-size: 14px; font-weight: 700; text-anchor: middle; dominant-baseline: middle; }
        .empty-plan { fill: var(--muted); font-size: 14px; text-anchor: middle; dominant-baseline: middle; font-family: var(--font-body); }

        /* ===================== ÉTAGES ===================== */
        .floor-pills { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 1rem; }
        .floor-pill {
            white-space: nowrap; padding: 6px 14px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
            border: 1px solid var(--border); background: var(--bg3);
            color: var(--muted); cursor: pointer; transition: all 0.2s;
        }
        .floor-pill.current { background: var(--accent-soft); color: var(--accent); border-color: var(--accent-soft2); }
        .floor-pill:hover { border-color: var(--accent-soft2); color: var(--text); }

        .nav-banner {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px; margin-bottom: 12px;
            background: var(--accent-soft); border: 1px solid var(--accent-soft2);
            color: var(--accent-strong); font-size: 13px; font-weight: 500;
        }
        .nav-banner svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* ===================== 3D ===================== */
        #canvas3d { width: 100%; border-radius: 12px; border: 1px solid var(--border); display: block; }
        .controls-hint { font-size: 11px; color: var(--muted); text-align: center; margin-top: 8px; }

        /* ===================== INFOS ===================== */
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-key { color: var(--muted); font-size: 13px; }
        .info-val { font-weight: 500; text-align: right; }
        .section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 10px; }

        /* ===================== ROOMS LIST (legacy infos) ===================== */
        .room-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: var(--bg3); margin-bottom: 6px; font-size: 13px; }
        .room-dot { width: 8px; height: 8px; background: var(--accent-soft2); border-radius: 50%; flex-shrink: 0; }
        .room-current .room-dot { background: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }

        /* ===================== ITINÉRAIRE ===================== */
        .dest-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            border-radius: 8px; background: var(--bg3); margin-bottom: 6px;
            font-size: 13px; cursor: pointer; border: 1px solid var(--border);
            transition: all 0.15s;
        }
        .dest-item:hover { border-color: var(--accent-soft2); background: var(--accent-soft); }
        .dest-item.selected { border-color: var(--accent); color: var(--accent-strong); background: var(--accent-soft); }
        .dest-icon {
            width: 28px; height: 28px; border-radius: 6px;
            background: var(--accent-soft); color: var(--accent2);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .dest-meta { font-size: 11px; color: var(--muted); }
        .nav-instruction {
            padding: 14px; background: var(--accent-soft);
            border: 1px solid var(--accent-soft2); border-radius: 10px;
            font-size: 13px; line-height: 1.5; margin-bottom: 1rem; color: var(--text);
        }
        .nav-clear-btn {
            width: 100%; padding: 10px; border-radius: 8px;
            border: 1px solid var(--border); background: var(--bg3);
            color: var(--muted); cursor: pointer; font-size: 13px;
        }
        .nav-clear-btn:hover { color: var(--text); border-color: var(--accent-soft2); }
        .nav-empty { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: 13px; }
    </style>
</head>
<body>

<div class="scan-header">
    <div class="logo">QRMap</div>
    <div class="header-right">
        <div class="scan-time" id="scan-time"></div>
        <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer le thème">
            <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
    </div>
</div>

<div class="location-hero">
    <div class="location-label">{{ $qrcode->location_label }}</div>
    <div class="location-path">
        <span style="color: var(--text);">{{ $site->name }}</span>
        @if($building)
        <span>›</span>
        <span style="color: var(--text);">{{ $building->name }}</span>
        @endif
        @if($currentFloor)
        <span>›</span>
        <span style="color: var(--accent);">{{ $currentFloor->label ?? 'Niveau '.$currentFloor->level }}</span>
        @endif
    </div>
    <div class="scan-badge">Localisé</div>
</div>

<div class="tabs">
    <button class="tab active" onclick="switchTab('plan2d', this)">Plan 2D</button>
    <button class="tab" onclick="switchTab('vue3d', this)">Vue 3D</button>
    <button class="tab" onclick="switchTab('itineraire', this)">Itinéraire</button>
    <button class="tab" onclick="switchTab('infos', this)">Infos</button>
</div>

{{-- ONGLET PLAN 2D --}}
<div id="pane-plan2d" class="pane active">
    <div id="nav-banner" class="nav-banner" style="display:none;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
        <span id="nav-banner-text" style="flex:1;"></span>
        <button id="nav-next-floor-btn" style="display:none; background:var(--accent); color:var(--bg2); border:none; border-radius:6px; padding:5px 10px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;" onclick="goToNextSegment()">Étape suivante →</button>
    </div>

    @if($allFloors->isNotEmpty())
    <div class="floor-pills">
        @foreach($allFloors as $floor)
        <button class="floor-pill {{ ($currentFloor && $floor->id === $currentFloor->id) ? 'current' : '' }}"
                onclick="showFloor2D({{ $floor->id }}, this)"
                id="pill-{{ $floor->id }}">
            {{ $floor->label ?? 'N'.$floor->level }}
            @if($currentFloor && $floor->id === $currentFloor->id) ← vous @endif
        </button>
        @endforeach
    </div>
    @endif

    @foreach($allFloors as $floor)
    <div id="floor2d-{{ $floor->id }}" class="map-container" style="display: {{ ($currentFloor && $floor->id === $currentFloor->id) ? 'block' : 'none' }};">
        <svg id="svg2d-{{ $floor->id }}" viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg"></svg>
    </div>
    @endforeach

    @if($allFloors->isEmpty())
    <div style="text-align: center; padding: 3rem; color: var(--muted);">
        <div style="font-size: 2rem; margin-bottom: 1rem;">🗺</div>
        Plan non encore configuré
    </div>
    @endif

    @if($floor = $currentFloor)
    @if($floor->rooms && count($floor->rooms) > 0)
    <div style="margin-top: 1rem;">
        <div class="section-title">Pièces du niveau</div>
        @foreach($floor->rooms as $room)
        <div class="room-item room-current">
            <div class="room-dot"></div>
            <span>{{ $room['name'] }}</span>
        </div>
        @endforeach
    </div>
    @endif
    @endif
</div>

{{-- ONGLET VUE 3D --}}
<div id="pane-vue3d" class="pane">
    <canvas id="canvas3d" height="380"></canvas>
    <p class="controls-hint">🖱 Toucher-déplacer pour pivoter · Pincer pour zoomer</p>
    <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
        <button onclick="resetCam()" style="flex:1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--muted); cursor: pointer; font-size: 13px;">Vue de face</button>
        <button onclick="topCam()" style="flex:1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--muted); cursor: pointer; font-size: 13px;">Vue du dessus</button>
        <button onclick="toggleWireframe()" id="wf-btn" style="flex:1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg3); color: var(--muted); cursor: pointer; font-size: 13px;">Filaire</button>
    </div>
</div>

{{-- ONGLET ITINÉRAIRE --}}
<div id="pane-itineraire" class="pane">
    <div class="section-title">Où voulez-vous aller ?</div>
    <div id="dest-list">
        <div class="nav-empty">Chargement des destinations…</div>
    </div>

    <div id="nav-result" style="display:none; margin-top: 1.25rem;">
        <div class="section-title">Itinéraire</div>
        <div class="nav-instruction" id="nav-instruction"></div>
        <button class="nav-clear-btn" onclick="clearPath()">Effacer le tracé</button>
    </div>
</div>

{{-- ONGLET INFOS --}}
<div id="pane-infos" class="pane">
    <div class="section-title">Localisation</div>
    <div class="info-row"><span class="info-key">Site</span><span class="info-val">{{ $site->name }}</span></div>
    @if($building)
    <div class="info-row"><span class="info-key">Bâtiment</span><span class="info-val">{{ $building->name }}</span></div>
    <div class="info-row"><span class="info-key">Niveaux</span><span class="info-val">{{ $building->floors_count }}</span></div>
    @endif
    @if($currentFloor)
    <div class="info-row"><span class="info-key">Niveau actuel</span><span class="info-val" style="color: var(--accent);">{{ $currentFloor->label ?? 'Niveau '.$currentFloor->level }}</span></div>
    @endif
    <div class="info-row"><span class="info-key">Emplacement</span><span class="info-val">{{ $qrcode->location_label }}</span></div>

    @if($site->address)
    <br>
    <div class="section-title">Adresse</div>
    <div class="info-row"><span class="info-key">Adresse</span><span class="info-val">{{ $site->address }}</span></div>
    @endif
    @if($site->latitude && $site->longitude)
    <div class="info-row">
        <span class="info-key">Coordonnées</span>
        <span class="info-val">
            <a href="https://maps.google.com/?q={{ $site->latitude }},{{ $site->longitude }}" target="_blank" style="color: var(--accent2);">Voir sur Maps</a>
        </span>
    </div>
    @endif

    <br>
    <div class="section-title">Statistiques</div>
    <div class="info-row"><span class="info-key">Scans total</span><span class="info-val" style="color: var(--accent);">{{ $qrcode->scan_count }}</span></div>
    <div class="info-row"><span class="info-key">Ce scan</span><span class="info-val" id="scan-ts">—</span></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ===== HEURE =====
const now = new Date();
document.getElementById('scan-time').textContent = now.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
document.getElementById('scan-ts').textContent = now.toLocaleString('fr-FR');

// ===== IDENTIFIANTS GLOBAUX =====
const QR_UUID = '{{ $qrcode->uuid }}';
const QR_POS  = { x: {{ $qrcode->pos_x ?? 'null' }}, y: {{ $qrcode->pos_y ?? 'null' }} };

// ===== THEME =====
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('qrmap-theme', theme);
    if (window._3dReady) apply3DTheme();
}
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}
(function initTheme() {
    const saved = localStorage.getItem('qrmap-theme');
    if (saved) { document.documentElement.setAttribute('data-theme', saved); return; }
    const prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
    document.documentElement.setAttribute('data-theme', prefersLight ? 'light' : 'dark');
})();

// ===== TABS =====
function switchTab(name, btn) {
    document.querySelectorAll('.pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('pane-'+name).classList.add('active');
    btn.classList.add('active');
    if (name === 'vue3d' && !window._3dReady) { init3D(); window._3dReady = true; }
    if (name === 'plan2d') renderActiveFloor();
}

// ===== DONNÉES ÉTAGES =====
const floors2D = @json($allFloors->keyBy('id'));
const currentFloorId = {{ $currentFloor ? $currentFloor->id : 'null' }};

// État de l'itinéraire actif : tableau de segments [{ floorId, path: [{x,y},...] }, ...]
// 1 segment = trajet sur un même étage, 2 segments = trajet multi-étages
// (segment 0 = vers l'ascenseur/escalier sur l'étage courant,
//  segment 1 = de l'ascenseur/escalier vers la destination sur l'étage cible)
let pathSegments = [];
let pathAnimFrame = null;

function segmentForFloor(floorId) {
    return pathSegments.find(s => String(s.floorId) === String(floorId)) || null;
}

function showFloor2D(floorId, btn) {
    document.querySelectorAll('[id^="floor2d-"]').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.floor-pill').forEach(el => el.classList.remove('current'));
    document.getElementById('floor2d-'+floorId).style.display = 'block';
    btn.classList.add('current');
    renderFloor(floorId);
}

function renderActiveFloor() {
    const id = currentFloorId || (Object.keys(floors2D)[0] || null);
    if (id) renderFloor(id);
}

// ===== CONVERSION % -> COORDONNÉES SVG (viewBox 600x400, cadre 20..580 / 20..380) =====
const VB_X = 20, VB_Y = 20, VB_W = 560, VB_H = 360;
function toSvgX(pct) { return VB_X + (pct/100) * VB_W; }
function toSvgY(pct) { return VB_Y + (pct/100) * VB_H; }

// ===== RENDU SVG D'UN ÉTAGE =====
function renderFloor(floorId) {
    const svg = document.getElementById('svg2d-'+floorId);
    if (!svg) return;
    if (pathAnimFrame) { cancelAnimationFrame(pathAnimFrame); pathAnimFrame = null; }

    const floor = floors2D[floorId];
    const rooms = floor ? (floor.rooms || []) : [];
    const segment = segmentForFloor(floorId);

    let html = '';

    // Grille de fond
    html += `<defs>
        <pattern id="grid-${floorId}" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" class="floor-grid-line"/>
        </pattern>
    </defs>`;
    html += `<rect x="0" y="0" width="600" height="400" fill="url(#grid-${floorId})"/>`;

    // Contour du bâtiment
    html += `<rect x="${VB_X}" y="${VB_Y}" width="${VB_W}" height="${VB_H}" rx="6" class="floor-outline"/>`;

    if (rooms.length === 0) {
        html += `<text x="300" y="200" class="empty-plan">Plan non configuré pour ce niveau</text>`;
    } else {
        rooms.forEach((r, i) => {
            const x = toSvgX(r.x), y = toSvgY(r.y);
            const w = (r.w/100) * VB_W, h = (r.h/100) * VB_H;
            const name = (r.name || '');
            const lname = name.toLowerCase();
            const muted = lname.includes('couloir') || lname.includes('hall') || lname.includes('attente') || lname.includes('circulation');
            const isDest = segment && pointInRoom(segment.path[segment.path.length-1], r);
            const cls = `room-rect ${muted ? 'muted' : ''} ${isDest ? 'highlight' : ''}`;
            const sw = isDest ? 2 : 1;

            // Corps selon la forme : rect (défaut), circle/ellipse, polygone
            let body;
            if (r.shape === 'circle') {
                body = `<ellipse cx="${x + w/2}" cy="${y + h/2}" rx="${w/2}" ry="${h/2}" class="${cls}" stroke-width="${sw}"/>`;
            } else if (r.shape === 'poly' && Array.isArray(r.points) && r.points.length >= 3) {
                const pts = r.points.map(p => `${toSvgX(p[0])},${toSvgY(p[1])}`).join(' ');
                body = `<polygon points="${pts}" class="${cls}" stroke-width="${sw}"/>`;
            } else {
                body = `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="4" class="${cls}" stroke-width="${sw}"/>`;
            }

            const labelTag = name
                ? `<text x="${x + w/2}" y="${y + h/2}" class="room-label ${isDest ? 'highlight' : ''}">${escapeXml(name)}</text>`
                : '';
            html += `<g>
                ${body}
                ${labelTag}
            </g>`;
        });
    }

    // Marqueur QR (position actuelle) — uniquement sur l'étage courant
    if (String(floorId) === String(currentFloorId) && QR_POS.x !== null && QR_POS.y !== null) {
        const qx = toSvgX(QR_POS.x), qy = toSvgY(QR_POS.y);
        html += `<g class="qr-marker" transform="translate(${qx} ${qy})">
            <rect x="-14" y="-14" width="28" height="28" rx="4"/>
            <text x="0" y="1">QR</text>
        </g>`;
    }

    // Chemin (si un segment d'itinéraire concerne cet étage)
    if (segment) {
        const pts = segment.path.map(p => [toSvgX(p.x), toSvgY(p.y)]);
        const d = 'M ' + pts.map(p => `${p[0]} ${p[1]}`).join(' L ');

        html += `<path d="${d}" class="path-bg"/>`;
        html += `<path d="${d}" class="path-fg" id="pathfg-${floorId}"/>`;

        const start = pts[0], end = pts[pts.length-1];

        // Le connecteur (ascenseur/escalier) est matérialisé :
        // - à la FIN du segment "vers_connecteur" (on rejoint l'ascenseur)
        // - au DÉBUT du segment "depuis_connecteur" (on sort de l'ascenseur)
        const connAtEnd   = segment.role === 'vers_connecteur';
        const connAtStart = segment.role === 'depuis_connecteur';
        const connPoint   = connAtEnd ? end : (connAtStart ? start : null);

        html += `<g class="marker-start" transform="translate(${start[0]} ${start[1]})">
            <circle r="9" stroke-width="2"/><circle r="3"/>
        </g>`;

        if (connPoint) {
            // Marqueur connecteur : pastille avec le symbole ↕
            html += `<g class="conn-marker" transform="translate(${connPoint[0]} ${connPoint[1]})">
                <rect x="-12" y="-12" width="24" height="24" rx="5"/>
                <text x="0" y="1">↕</text>
            </g>`;
        }

        // Marqueur d'arrivée "destination" uniquement si ce segment finit sur la destination
        if (!connAtEnd) {
            html += `<g class="marker-end" transform="translate(${end[0]} ${end[1]})">
                <circle r="11" stroke-width="2"/><circle r="4"/>
            </g>`;
        }

        html += `<g class="walker" id="walker-${floorId}" transform="translate(${start[0]} ${start[1]})">
            <circle r="9" stroke-width="3"/><circle r="3"/>
        </g>`;
    }

    svg.innerHTML = html;

    // Animation du tracé + marcheur
    if (segment) {
        animatePath(floorId, segment.path.map(p => [toSvgX(p.x), toSvgY(p.y)]));
    }
}

function pointInRoom(pt, room) {
    return pt.x >= room.x && pt.x <= room.x + room.w && pt.y >= room.y && pt.y <= room.y + room.h;
}

function escapeXml(s) {
    return String(s).replace(/[<>&'"]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;',"'":'&apos;','"':'&quot;'}[c]));
}

// ===== ANIMATION DU TRACÉ (dasharray) + MARCHEUR =====
function animatePath(floorId, pts) {
    const pathEl = document.getElementById('pathfg-'+floorId);
    const walkerEl = document.getElementById('walker-'+floorId);
    if (!pathEl) return;

    let totalLen = 0;
    const segLens = [];
    for (let i = 1; i < pts.length; i++) {
        const l = Math.hypot(pts[i][0]-pts[i-1][0], pts[i][1]-pts[i-1][1]);
        segLens.push(l); totalLen += l;
    }
    pathEl.style.strokeDasharray = totalLen;
    pathEl.style.strokeDashoffset = totalLen;

    const duration = 2200;
    const start = performance.now();

    function frame(t) {
        const progress = Math.min(1, (t - start) / duration);
        pathEl.style.strokeDashoffset = totalLen * (1 - progress);

        if (walkerEl) {
            let remaining = totalLen * progress;
            let pos = pts[pts.length-1];
            for (let i = 0; i < segLens.length; i++) {
                if (remaining <= segLens[i]) {
                    const t2 = segLens[i] === 0 ? 0 : remaining / segLens[i];
                    pos = [
                        pts[i][0] + (pts[i+1][0]-pts[i][0]) * t2,
                        pts[i][1] + (pts[i+1][1]-pts[i][1]) * t2,
                    ];
                    break;
                }
                remaining -= segLens[i];
            }
            walkerEl.setAttribute('transform', `translate(${pos[0]} ${pos[1]})`);
        }

        if (progress < 1) pathAnimFrame = requestAnimationFrame(frame);
    }
    pathAnimFrame = requestAnimationFrame(frame);
}

setTimeout(renderActiveFloor, 50);

// ===== ITINÉRAIRE =====
async function loadDestinations() {
    const container = document.getElementById('dest-list');
    try {
        const res = await fetch(`/api/v1/scan/${QR_UUID}/destinations`);
        const data = await res.json();

        if (!data.destinations || data.destinations.length === 0) {
            container.innerHTML = '<div class="nav-empty">Aucune destination disponible pour ce bâtiment.</div>';
            return;
        }

        container.innerHTML = '';
        data.destinations.forEach(group => {
            const h = document.createElement('div');
            h.className = 'section-title';
            h.style.marginTop = '14px';
            h.textContent = group.floor_label;
            container.appendChild(h);

            group.destinations.forEach(d => {
                const item = document.createElement('div');
                item.className = 'dest-item';
                item.id = 'dest-' + d.id;
                item.innerHTML = `
                    <div class="dest-icon">${d.icon ?? '📍'}</div>
                    <div style="flex:1;">
                        <div>${d.label}</div>
                        ${d.description ? `<div class="dest-meta">${d.description}</div>` : ''}
                    </div>
                    ${d.same_floor ? '' : '<div class="dest-meta">' + group.floor_label + '</div>'}
                `;
                item.onclick = () => navigateTo(d.id, d.label, item);
                container.appendChild(item);
            });
        });
    } catch (e) {
        container.innerHTML = '<div class="nav-empty">Impossible de charger les destinations.</div>';
        console.error(e);
    }
}

// Densifie un tracé en courbe douce (Catmull-Rom) quand le tracé corrigé est "lissé".
// Reçoit/renvoie des points {x,y} en %. Sans lissage, renvoie le tracé tel quel.
function smoothPathPoints(pts, smooth, samplesPerSeg = 14) {
    if (!smooth || !pts || pts.length < 3) return pts;
    const P = pts.map(p => [p.x, p.y]);
    const out = [];
    for (let i = 0; i < P.length - 1; i++) {
        const p0 = P[i - 1] || P[i];
        const p1 = P[i];
        const p2 = P[i + 1];
        const p3 = P[i + 2] || P[i + 1];
        for (let t = 0; t < samplesPerSeg; t++) {
            const s = t / samplesPerSeg, s2 = s * s, s3 = s2 * s;
            const x = 0.5 * ((2 * p1[0]) + (-p0[0] + p2[0]) * s +
                (2 * p0[0] - 5 * p1[0] + 4 * p2[0] - p3[0]) * s2 +
                (-p0[0] + 3 * p1[0] - 3 * p2[0] + p3[0]) * s3);
            const y = 0.5 * ((2 * p1[1]) + (-p0[1] + p2[1]) * s +
                (2 * p0[1] - 5 * p1[1] + 4 * p2[1] - p3[1]) * s2 +
                (-p0[1] + 3 * p1[1] - 3 * p2[1] + p3[1]) * s3);
            out.push({ x, y });
        }
    }
    out.push({ x: P[P.length - 1][0], y: P[P.length - 1][1] });
    return out;
}

async function navigateTo(destinationId, label, itemEl) {
    document.querySelectorAll('.dest-item').forEach(el => el.classList.remove('selected'));
    if (itemEl) itemEl.classList.add('selected');

    try {
        const res = await fetch('/api/v1/navigate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ uuid: QR_UUID, destination_id: destinationId })
        });
        const data = await res.json();

        // Lissage : honore data.smooth (global) ou s.smooth (par segment).
        pathSegments = (data.segments || []).map(s => ({
            floorId: s.floor_id,
            path: smoothPathPoints(s.path, s.smooth ?? data.smooth),
            role: s.role
        }));

        // Étage de référence pour basculer la vue : l'étage où se trouve le QR scanné
        // (segment 0), pour montrer d'abord le trajet vers l'ascenseur/escalier.
        const initialFloorId = pathSegments.length ? pathSegments[0].floorId : data.current_floor.id;

        if (window._3dReady) render3DPath();

        document.getElementById('nav-instruction').textContent = data.instruction;
        document.getElementById('nav-result').style.display = 'block';

        const banner = document.getElementById('nav-banner');
        document.getElementById('nav-banner-text').textContent = 'Direction : ' + label;
        banner.style.display = 'flex';

        const nextBtn = document.getElementById('nav-next-floor-btn');
        if (data.type === 'multi_floor' && pathSegments.length === 2) {
            nextBtn.style.display = 'inline-block';
            nextBtn.dataset.targetFloor = pathSegments[1].floorId;
            nextBtn.textContent = 'Voir la suite — ' + (data.target_floor?.label ?? 'étage suivant') + ' →';
        } else {
            nextBtn.style.display = 'none';
        }

        // Basculer sur le Plan 2D et afficher l'étage concerné
        const plan2dTab = document.querySelectorAll('.tab')[0];
        switchTab('plan2d', plan2dTab);

        const pill = document.getElementById('pill-' + initialFloorId);
        if (pill) {
            showFloor2D(initialFloorId, pill);
        } else {
            renderActiveFloor();
        }
    } catch (e) {
        console.error(e);
        document.getElementById('nav-instruction').textContent = "Erreur lors du calcul de l'itinéraire.";
        document.getElementById('nav-result').style.display = 'block';
    }
}

function goToNextSegment() {
    const nextBtn = document.getElementById('nav-next-floor-btn');
    const floorId = nextBtn.dataset.targetFloor;
    if (!floorId) return;
    const pill = document.getElementById('pill-' + floorId);
    if (pill) showFloor2D(floorId, pill);
    else renderFloor(floorId);
}

function clearPath() {
    pathSegments = [];
    document.getElementById('nav-result').style.display = 'none';
    document.getElementById('nav-banner').style.display = 'none';
    document.getElementById('nav-next-floor-btn').style.display = 'none';
    document.querySelectorAll('.dest-item').forEach(el => el.classList.remove('selected'));
    clear3DPath();
    renderActiveFloor();
}

loadDestinations();

// ===== VUE 3D =====
const bData = {
    floorsCount: {{ $building ? $building->floors_count : 1 }},
    widthM: {{ $building && $building->model_3d ? ($building->model_3d['width_m'] ?? 30) : 30 }},
    depthM: {{ $building && $building->model_3d ? ($building->model_3d['depth_m'] ?? 20) : 20 }},
    floorH: {{ $building && $building->model_3d ? ($building->model_3d['floor_height_m'] ?? 3) : 3 }},
    floors: @json($allFloors->sortBy('level')->values()),
    currentLevel: {{ $currentFloor ? $currentFloor->level : 0 }},
};

let sc, cam, rend, mats = [], wf = false;
let groundMesh, gridHelper, edgeLines = [];
let path3DGroups = [];
let path3DAnimFrames = [];
let drag = { active: false, px: 0, py: 0, rx: 0.4, ry: 0.6, zoom: 1 };

function hexColor(theme, key) {
    const palette = {
        dark:  { bg: 0x0d0f14, ground: 0x13161e, grid: 0x1a1e28, edge: 0x2a3040 },
        light: { bg: 0xf3f5f9, ground: 0xe6eaf0, grid: 0xd5dbe4, edge: 0xb7bfcc },
    };
    return palette[theme][key];
}

function init3D() {
    const canvas = document.getElementById('canvas3d');
    const W = canvas.parentElement.offsetWidth;
    const H = 380;
    canvas.width = W; canvas.height = H;

    const theme = document.documentElement.getAttribute('data-theme') || 'dark';

    sc = new THREE.Scene();
    sc.background = new THREE.Color(hexColor(theme, 'bg'));
    cam = new THREE.PerspectiveCamera(50, W/H, 0.1, 500);

    rend = new THREE.WebGLRenderer({ canvas, antialias: true });
    rend.setSize(W, H);
    rend.shadowMap.enabled = true;

    sc.add(new THREE.AmbientLight(0xffffff, 0.5));
    const dl = new THREE.DirectionalLight(0xffffff, 0.8);
    dl.position.set(40, 60, 40); dl.castShadow = true; sc.add(dl);
    const al = new THREE.PointLight(0x4fffb0, 1.2, 100);
    al.position.set(-20, 25, -10); sc.add(al);

    groundMesh = new THREE.Mesh(new THREE.PlaneGeometry(150,150), new THREE.MeshLambertMaterial({color: hexColor(theme,'ground')}));
    groundMesh.rotation.x = -Math.PI/2; groundMesh.receiveShadow = true; sc.add(groundMesh);
    gridHelper = new THREE.GridHelper(80, 40, hexColor(theme,'grid'), hexColor(theme,'grid'));
    sc.add(gridHelper);

    const W3 = bData.widthM, D3 = bData.depthM, FH = bData.floorH;
    const nF = Math.max(bData.floors.length, bData.floorsCount);

    for (let i = 0; i < nF; i++) {
        const isCurrentLevel = (i === bData.currentLevel);
        const t = i / Math.max(nF-1, 1);
        const col = isCurrentLevel
            ? new THREE.Color(0x4fffb0)
            : new THREE.Color().setHSL(0.43, 0.5, 0.12 + t * 0.15);

        const mat = new THREE.MeshLambertMaterial({ color: col, transparent: true, opacity: isCurrentLevel ? 0.7 : 0.55 });
        mats.push(mat);
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(W3, FH-0.15, D3), mat);
        mesh.position.y = i*FH + FH/2; mesh.castShadow = true; sc.add(mesh);

        const edges = new THREE.LineSegments(new THREE.EdgesGeometry(mesh.geometry), new THREE.LineBasicMaterial({ color: isCurrentLevel ? 0x4fffb0 : hexColor(theme,'edge'), transparent: true, opacity: isCurrentLevel ? 0.8 : 0.3 }));
        edges.position.copy(mesh.position); sc.add(edges);
        edgeLines.push({ mesh: edges, isCurrentLevel });

        // Pièces pour l'étage courant
        if (isCurrentLevel && bData.floors[i] && bData.floors[i].rooms) {
            bData.floors[i].rooms.forEach(r => {
                const rx = (r.x/100 - 0.5) * W3, rz = (r.y/100 - 0.5) * D3;
                const rw = (r.w/100)*W3 - 0.3, rd = (r.h/100)*D3 - 0.3;
                const rm = new THREE.MeshLambertMaterial({color: 0x4f9fff, transparent: true, opacity: 0.2});
                const rmesh = new THREE.Mesh(new THREE.BoxGeometry(rw, FH*0.85, rd), rm);
                rmesh.position.set(rx, i*FH + FH/2, rz); sc.add(rmesh);
            });
        }
    }

    // Marqueur de position QR
    @if($qrcode->pos_x && $qrcode->pos_y && $currentFloor)
    const markerGeo = new THREE.SphereGeometry(0.8, 16, 16);
    const markerMat = new THREE.MeshBasicMaterial({color: 0x4fffb0});
    const marker = new THREE.Mesh(markerGeo, markerMat);
    const mx = ({{ $qrcode->pos_x }}/100 - 0.5) * bData.widthM;
    const mz = ({{ $qrcode->pos_y }}/100 - 0.5) * bData.depthM;
    marker.position.set(mx, bData.currentLevel*bData.floorH + bData.floorH + 0.5, mz);
    sc.add(marker);
    @endif

    if (pathSegments.length) render3DPath();

    updateCam();

    // Drag sur mobile et desktop
    canvas.addEventListener('mousedown', e => { drag.active=true; drag.px=e.clientX; drag.py=e.clientY; });
    window.addEventListener('mouseup', () => drag.active=false);
    window.addEventListener('mousemove', e => {
        if (!drag.active) return;
        drag.ry += (e.clientX-drag.px)*0.012; drag.rx += (e.clientY-drag.py)*0.012;
        drag.rx = Math.max(-1.1, Math.min(1.1, drag.rx));
        drag.px=e.clientX; drag.py=e.clientY; updateCam();
    });
    canvas.addEventListener('wheel', e => { drag.zoom = Math.max(0.4, Math.min(3, drag.zoom+e.deltaY*0.001)); updateCam(); });

    let t0 = null;
    canvas.addEventListener('touchstart', e => { t0=e.touches[0]; });
    canvas.addEventListener('touchmove', e => {
        e.preventDefault();
        const t = e.touches[0];
        drag.ry += (t.clientX-t0.clientX)*0.015; drag.rx += (t.clientY-t0.clientY)*0.015;
        drag.rx = Math.max(-1.1, Math.min(1.1, drag.rx));
        t0=t; updateCam();
    }, {passive:false});

    (function anim() { requestAnimationFrame(anim); rend.render(sc,cam); })();
}

function apply3DTheme() {
    if (!sc) return;
    const theme = document.documentElement.getAttribute('data-theme') || 'dark';
    sc.background = new THREE.Color(hexColor(theme, 'bg'));
    if (groundMesh) groundMesh.material.color.setHex(hexColor(theme, 'ground'));
    if (gridHelper) {
        if (Array.isArray(gridHelper.material)) {
            gridHelper.material.forEach(m => m.color.setHex(hexColor(theme,'grid')));
        } else {
            gridHelper.material.color.setHex(hexColor(theme, 'grid'));
        }
    }
    edgeLines.forEach(({mesh, isCurrentLevel}) => {
        if (!isCurrentLevel) mesh.material.color.setHex(hexColor(theme, 'edge'));
    });
    if (path3DGroups.length) render3DPath();
}

function updateCam() {
    const r = 60 * drag.zoom;
    cam.position.set(r*Math.sin(drag.ry)*Math.cos(drag.rx), r*Math.sin(drag.rx)+10, r*Math.cos(drag.ry)*Math.cos(drag.rx));
    cam.lookAt(0, (bData.floorsCount*bData.floorH)/2, 0);
}
function resetCam() { drag.rx=0.4; drag.ry=0.6; drag.zoom=1; updateCam(); }
function topCam()   { drag.rx=1.0; drag.ry=0; drag.zoom=1; updateCam(); }
function toggleWireframe() { wf=!wf; mats.forEach(m=>m.wireframe=wf); document.getElementById('wf-btn').textContent=wf?'Solide':'Filaire'; }

// ===== TRACÉ 3D DE L'ITINÉRAIRE =====
function theme3DAccent() {
    const theme = document.documentElement.getAttribute('data-theme') || 'dark';
    return theme === 'light'
        ? { accent: 0x16a34a, accent2: 0x2563eb, walker: 0xffffff }
        : { accent: 0x4fffb0, accent2: 0x4f9fff, walker: 0xffffff };
}

function floorLevelFor(floorId) {
    const f = bData.floors.find(fl => String(fl.id) === String(floorId));
    return f ? f.level : bData.currentLevel;
}

function clear3DPath() {
    path3DAnimFrames.forEach(id => { if (id) cancelAnimationFrame(id); });
    path3DAnimFrames = [];
    if (sc) {
        path3DGroups.forEach(g => sc.remove(g));
    }
    path3DGroups = [];
}

function point3D(p, level) {
    const W3 = bData.widthM, D3 = bData.depthM, FH = bData.floorH;
    const y = level * FH + FH + 0.4;
    return new THREE.Vector3((p.x/100 - 0.5) * W3, y, (p.y/100 - 0.5) * D3);
}

function render3DPath() {
    clear3DPath();
    if (!pathSegments.length || !sc) return;

    const colors = theme3DAccent();
    const segEndpoints = []; // { point: Vector3, level } pour le 1er et dernier point de chaque segment

    pathSegments.forEach((segment, idx) => {
        if (!segment.path || segment.path.length < 2) return;

        const level = floorLevelFor(segment.floorId);
        const pts3D = segment.path.map(p => point3D(p, level));
        segEndpoints.push({ first: pts3D[0], last: pts3D[pts3D.length - 1], level });

        const group = new THREE.Group();

        // Tube lumineux représentant le chemin
        const curve = new THREE.CatmullRomCurve3(pts3D);
        const tubeSegments = Math.max(16, pts3D.length * 6);
        const tubeGeo = new THREE.TubeGeometry(curve, tubeSegments, 0.1, 8, false);
        const tubeMat = new THREE.MeshBasicMaterial({ color: colors.accent, transparent: true, opacity: 0.85 });
        group.add(new THREE.Mesh(tubeGeo, tubeMat));

        // Halo au sol sous le tube (effet de lueur)
        const haloGeo = new THREE.TubeGeometry(curve, tubeSegments, 0.35, 8, false);
        const haloMat = new THREE.MeshBasicMaterial({ color: colors.accent, transparent: true, opacity: 0.12 });
        group.add(new THREE.Mesh(haloGeo, haloMat));

        // Marqueur de départ du segment (bleu pour le 1er segment, accent pour le 2e)
        const startColor = idx === 0 ? colors.accent2 : colors.accent;
        const startMarker = new THREE.Mesh(new THREE.SphereGeometry(0.6, 16, 16), new THREE.MeshBasicMaterial({ color: startColor }));
        startMarker.position.copy(pts3D[0]);
        group.add(startMarker);

        // Marqueur d'arrivée du segment (accent pour le dernier segment uniquement = destination finale)
        const isFinalSegment = idx === pathSegments.length - 1;
        const endMarker = new THREE.Mesh(new THREE.SphereGeometry(0.7, 16, 16), new THREE.MeshBasicMaterial({ color: colors.accent }));
        endMarker.position.copy(pts3D[pts3D.length - 1]);
        group.add(endMarker);

        // Anneau pulsant : autour de la destination finale, ou autour du connecteur (ascenseur/escalier)
        const ringGeo = new THREE.RingGeometry(0.9, 1.2, 24);
        const ringMat = new THREE.MeshBasicMaterial({ color: colors.accent, transparent: true, opacity: 0.5, side: THREE.DoubleSide });
        const ring = new THREE.Mesh(ringGeo, ringMat);
        ring.position.copy(pts3D[pts3D.length - 1]);
        ring.rotation.x = -Math.PI / 2;
        group.add(ring);

        // Marcheur animé
        const walker = new THREE.Mesh(new THREE.SphereGeometry(0.45, 16, 16), new THREE.MeshBasicMaterial({ color: colors.walker }));
        walker.position.copy(pts3D[0]);
        group.add(walker);

        sc.add(group);
        path3DGroups.push(group);

        // Longueurs de segments pour l'animation
        let segLens = [], totalLen = 0;
        for (let i = 1; i < pts3D.length; i++) {
            const l = pts3D[i].distanceTo(pts3D[i-1]);
            segLens.push(l); totalLen += l;
        }

        // Le 1er segment démarre immédiatement, le 2e démarre après un court délai
        // (pour donner l'impression d'un trajet : étage courant -> ascenseur -> étage destination)
        const startDelay = idx * 1100;
        const animIndex = path3DGroups.length - 1;

        const duration = 2200;
        const startTime = performance.now() + startDelay;

        function frame(t) {
            const elapsed = t - startTime;
            const progress = Math.min(1, Math.max(0, elapsed) / duration);

            let remaining = totalLen * progress;
            let pos = pts3D[pts3D.length - 1].clone();
            for (let i = 0; i < segLens.length; i++) {
                if (remaining <= segLens[i]) {
                    const t2 = segLens[i] === 0 ? 0 : remaining / segLens[i];
                    pos = pts3D[i].clone().lerp(pts3D[i+1], t2);
                    break;
                }
                remaining -= segLens[i];
            }
            walker.position.copy(pos);

            const pulse = 1 + Math.sin(t * 0.004) * 0.15;
            ring.scale.set(pulse, pulse, 1);

            if (elapsed < duration) {
                path3DAnimFrames[animIndex] = requestAnimationFrame(frame);
            } else {
                path3DAnimFrames[animIndex] = requestAnimationFrame(() => loopWalker(pts3D, segLens, totalLen, walker, ring, animIndex));
            }
        }
        path3DAnimFrames[animIndex] = requestAnimationFrame(frame);
    });

    // ── Puits de liaison entre étages (ascenseur / escalier) ────────────────
    // Relie la fin du 1er segment (arrivée au connecteur sur l'étage courant)
    // au début du 2e segment (départ du connecteur sur l'étage destination).
    if (segEndpoints.length === 2) {
        const a = segEndpoints[0].last;
        const b = segEndpoints[1].first;

        const shaftCurve = new THREE.CatmullRomCurve3([a, b]);
        const shaftGeo = new THREE.TubeGeometry(shaftCurve, 8, 0.06, 8, false);
        const shaftMat = new THREE.MeshBasicMaterial({ color: colors.accent2, transparent: true, opacity: 0.55 });
        const shaft = new THREE.Mesh(shaftGeo, shaftMat);

        const haloGeo = new THREE.TubeGeometry(shaftCurve, 8, 0.22, 8, false);
        const haloMat = new THREE.MeshBasicMaterial({ color: colors.accent2, transparent: true, opacity: 0.10 });
        const halo = new THREE.Mesh(haloGeo, haloMat);

        const shaftGroup = new THREE.Group();
        shaftGroup.add(shaft);
        shaftGroup.add(halo);
        sc.add(shaftGroup);
        path3DGroups.push(shaftGroup);
    }
}

// Petit aller-retour continu du marcheur une fois le trajet initial joué
function loopWalker(pts3D, segLens, totalLen, walker, ring, animIndex) {
    const duration = 2200;
    const start = performance.now();

    function frame(t) {
        const cycle = ((t - start) / duration) % 2;
        const progress = cycle <= 1 ? cycle : 2 - cycle; // aller-retour

        let remaining = totalLen * progress;
        let pos = pts3D[pts3D.length - 1].clone();
        for (let i = 0; i < segLens.length; i++) {
            if (remaining <= segLens[i]) {
                const t2 = segLens[i] === 0 ? 0 : remaining / segLens[i];
                pos = pts3D[i].clone().lerp(pts3D[i+1], t2);
                break;
            }
            remaining -= segLens[i];
        }
        walker.position.copy(pos);

        const pulse = 1 + Math.sin(t * 0.004) * 0.15;
        ring.scale.set(pulse, pulse, 1);

        path3DAnimFrames[animIndex] = requestAnimationFrame(frame);
    }
    path3DAnimFrames[animIndex] = requestAnimationFrame(frame);
}
</script>
</body>
</html>