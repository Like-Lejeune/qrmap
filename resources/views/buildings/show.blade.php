@extends('layouts.app')
@section('title', $building->name . ' — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="#">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $building->site) }}">{{ $building->site->name }}</a>
    <span class="sep">/</span>
    <span>{{ $building->name }}</span>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title"><span>{{ $building->name }}</span></h1>
        <p class="page-sub">{{ $building->floors_count }} niveau(x) · {{ $building->site->name }}</p>
    </div>
    <div class="btn-actions">
        <a href="{{ route('buildings.floors.create', $building) }}" class="btn btn-primary">+ Étage</a>
        <a href="{{ route('qrcodes.create', ['building_id' => $building->id]) }}" class="btn btn-outline">+ QR Code</a>
        <a href="{{ route('sites.buildings.edit', [$building->site, $building]) }}" class="btn btn-outline">Éditer</a>
    </div>
</div>

{{-- ONGLETS 2D / 3D --}}
<div style="display: flex; gap: 4px; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
    <button onclick="showTab('floors')" id="tab-floors" class="tab-btn active">Étages & Plans 2D</button>
    <button onclick="showTab('3d')" id="tab-3d" class="tab-btn">Vue 3D</button>
    <button onclick="showTab('qrcodes')" id="tab-qrcodes" class="tab-btn">QR Codes ({{ $building->qrCodes->count() }})</button>
</div>

{{-- ONGLET ÉTAGES --}}
<div id="pane-floors">
    @if($building->floors->isEmpty())
    <div class="card" style="text-align: center; padding: 3rem;">
        <div style="font-size: 2rem; margin-bottom: 1rem;">🗺</div>
        <div style="color: var(--muted); margin-bottom: 1rem;">Aucun étage configuré</div>
        <a href="{{ route('buildings.floors.create', $building) }}" class="btn btn-primary">Ajouter le premier étage</a>
    </div>
    @else
    {{-- Sélecteur d'étage --}}
    <div style="display: flex; gap: 8px; margin-bottom: 1rem; flex-wrap: wrap;">
        @foreach($building->floors->sortBy('level') as $floor)
        <button onclick="showFloor({{ $floor->id }})" class="btn btn-outline btn-sm floor-btn" id="floor-btn-{{ $floor->id }}">
            {{ $floor->label ?? 'Niveau '.$floor->level }}
        </button>
        @endforeach
    </div>

    @foreach($building->floors->sortBy('level') as $i => $floor)
    <div id="floor-pane-{{ $floor->id }}" class="floor-pane" style="display: {{ $i === 0 ? 'block' : 'none' }};">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <span class="badge badge-blue">{{ $floor->label ?? 'Niveau '.$floor->level }}</span>
            </div>
            <a href="{{ route('buildings.floors.edit', [$building, $floor]) }}" class="btn btn-outline btn-sm">Éditer l'étage</a>
        </div>

        @if($floor->plan_2d)
        <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; position: relative; overflow: hidden; min-height: 300px;">
            {{-- Rendu SVG direct --}}
            @if(is_array($floor->plan_2d) && isset($floor->plan_2d['svg']))
                <div style="width: 100%; overflow: auto;">{!! $floor->plan_2d['svg'] !!}</div>
            @elseif(is_string($floor->plan_2d))
                <div style="width: 100%; overflow: auto;">{!! $floor->plan_2d !!}</div>
            @else
                {{-- Plan auto-généré à partir des rooms --}}
                <canvas id="canvas-floor-{{ $floor->id }}" width="800" height="400" style="width:100%; height: auto; border-radius: 8px;"></canvas>
            @endif

            {{-- Marqueurs des QR codes sur ce plan --}}
            @foreach($floor->qrCodes ?? [] as $qr)
                @if($qr->pos_x && $qr->pos_y)
                <div class="pos-marker" style="left: {{ $qr->pos_x }}%; top: {{ $qr->pos_y }}%;" title="{{ $qr->location_label }}"></div>
                @endif
            @endforeach
        </div>

        @push('scripts')
        <script>
        (function() {
            const canvas = document.getElementById('canvas-floor-{{ $floor->id }}');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const rooms = @json($floor->rooms ?? []);
            const W = canvas.width, H = canvas.height;

            ctx.fillStyle = '#1a1e28';
            ctx.fillRect(0, 0, W, H);

            if (rooms.length === 0) {
                ctx.fillStyle = '#8892a4';
                ctx.font = '16px DM Sans, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Plan non configuré — ajoutez des pièces', W/2, H/2);
                return;
            }

            const scaleX = W / 100, scaleY = H / 100;
            rooms.forEach(room => {
                const x = room.x * scaleX, y = room.y * scaleY;
                const w = room.w * scaleX, h = room.h * scaleY;

                ctx.fillStyle = 'rgba(79,255,176,0.07)';
                ctx.strokeStyle = 'rgba(79,255,176,0.4)';
                ctx.lineWidth = 1.5;
                roundRect(ctx, x, y, w, h, 4);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = '#f0f2f5';
                ctx.font = `bold ${Math.min(12, w/4)}px Syne, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(room.name, x + w/2, y + h/2);
            });
        })();

        function roundRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + w - r, y);
            ctx.quadraticCurveTo(x + w, y, x + w, y + r);
            ctx.lineTo(x + w, y + h - r);
            ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
            ctx.lineTo(x + r, y + h);
            ctx.quadraticCurveTo(x, y + h, x, y + h - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
        }
        </script>
        @endpush

        @else
        <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); padding: 3rem; text-align: center;">
            <div style="color: var(--muted); font-size: 14px;">Aucun plan pour cet étage</div>
            <a href="{{ route('buildings.floors.edit', [$building, $floor]) }}" class="btn btn-outline btn-sm" style="margin-top: 1rem;">Configurer le plan</a>
        </div>
        @endif

        @if($floor->rooms)
        <div style="margin-top: 1rem;">
            <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;">Pièces ({{ count($floor->rooms) }})</div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                @foreach($floor->rooms as $room)
                <span class="badge badge-gray">{{ $room['name'] }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
    @endif
</div>

{{-- ONGLET 3D --}}
<div id="pane-3d" style="display: none;">
    <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; height: 500px; position: relative;">
        <canvas id="canvas3d" style="width: 100%; height: 100%;"></canvas>
        <div style="position: absolute; bottom: 1rem; left: 1rem; font-size: 12px; color: var(--muted);">
            🖱 Cliquer-déplacer pour pivoter · Molette pour zoomer
        </div>
    </div>
    <div style="margin-top: 1rem; display: flex; gap: 8px; flex-wrap: wrap;">
        <button onclick="resetCamera()" class="btn btn-outline btn-sm">Réinitialiser la vue</button>
        <button onclick="toggleWireframe()" class="btn btn-outline btn-sm" id="wireframe-btn">Filaire</button>
    </div>
</div>

{{-- ONGLET QR CODES --}}
<div id="pane-qrcodes" style="display: none;">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('qrcodes.create', ['building_id' => $building->id]) }}" class="btn btn-primary">+ Générer un QR Code</a>
    </div>
    @if($building->qrCodes->isEmpty())
    <div class="card" style="text-align: center; padding: 2rem;">
        <div style="color: var(--muted);">Aucun QR Code pour ce bâtiment</div>
    </div>
    @else
    <div class="card-grid">
        @foreach($building->qrCodes as $qr)
        <div class="qr-card">
            <img src="{{ asset('storage/qrcodes/'.$qr->uuid.'.svg') }}" alt="QR Code" onerror="this.style.display='none'">
            <div style="margin-top: 12px; font-size: 13px; font-weight: 500;">{{ $qr->location_label ?? 'Sans étiquette' }}</div>
            <div style="font-size: 12px; color: var(--muted); margin-bottom: 10px;">{{ $qr->floor->label ?? 'Étage non défini' }} · {{ $qr->scan_count }} scan(s)</div>
            <div class="btn-actions" style="justify-content: center;">
                <a href="{{ route('scan.show', $qr->uuid) }}" class="btn btn-primary btn-sm" target="_blank">Tester</a>
                <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-outline btn-sm">Détails</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('styles')
<style>
.tab-btn {
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    font-family: var(--font-body);
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: -1px;
}
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
.floor-btn.active { border-color: var(--accent); color: var(--accent); }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ===== ONGLETS =====
function showTab(name) {
    ['floors','3d','qrcodes'].forEach(t => {
        document.getElementById('pane-'+t).style.display = 'none';
        document.getElementById('tab-'+t).classList.remove('active');
    });
    document.getElementById('pane-'+name).style.display = 'block';
    document.getElementById('tab-'+name).classList.add('active');
    if (name === '3d' && !window._3dInit) { init3D(); window._3dInit = true; }
}

// ===== SÉLECTEUR ÉTAGES =====
function showFloor(id) {
    document.querySelectorAll('.floor-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.floor-btn').forEach(b => b.classList.remove('active'));
    const pane = document.getElementById('floor-pane-'+id);
    const btn = document.getElementById('floor-btn-'+id);
    if (pane) pane.style.display = 'block';
    if (btn) btn.classList.add('active');
}
// Activer le premier bouton
const firstFloorBtn = document.querySelector('.floor-btn');
if (firstFloorBtn) firstFloorBtn.classList.add('active');

// ===== RENDU 3D THREE.JS =====
const buildingData = {
    floors: @json($building->floors->sortBy('level')->values()),
    floorsCount: {{ $building->floors_count }},
    widthM: {{ $building->model_3d['width_m'] ?? 30 }},
    depthM: {{ $building->model_3d['depth_m'] ?? 20 }},
    floorHeightM: {{ $building->model_3d['floor_height_m'] ?? 3 }},
};

let scene, camera, renderer, controls3d, wireframe = false;
let buildingGroup, materials = [];

function init3D() {
    const canvas = document.getElementById('canvas3d');
    const W = canvas.parentElement.offsetWidth;
    const H = canvas.parentElement.offsetHeight;

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1a1e28);
    scene.fog = new THREE.FogExp2(0x1a1e28, 0.018);

    camera = new THREE.PerspectiveCamera(45, W/H, 0.1, 1000);
    camera.position.set(60, 50, 60);
    camera.lookAt(0, 0, 0);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
    renderer.setSize(W, H);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // Éclairage
    const ambient = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambient);
    const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
    dirLight.position.set(50, 80, 50);
    dirLight.castShadow = true;
    scene.add(dirLight);
    const accentLight = new THREE.PointLight(0x4fffb0, 1, 80);
    accentLight.position.set(-20, 30, -20);
    scene.add(accentLight);

    // Sol
    const groundGeo = new THREE.PlaneGeometry(200, 200);
    const groundMat = new THREE.MeshLambertMaterial({ color: 0x13161e });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    // Grille
    const grid = new THREE.GridHelper(100, 50, 0x2a2e3a, 0x1e2230);
    scene.add(grid);

    buildBuilding();

    // Axes
    const axesHelper = new THREE.AxesHelper(10);
    scene.add(axesHelper);

    // Contrôles orbitaux simples
    controls3d = { isDragging: false, prevX: 0, prevY: 0, rotX: 0.3, rotY: 0.5, zoom: 1 };
    canvas.addEventListener('mousedown', e => { controls3d.isDragging = true; controls3d.prevX = e.clientX; controls3d.prevY = e.clientY; });
    canvas.addEventListener('mouseup', () => controls3d.isDragging = false);
    canvas.addEventListener('mousemove', e => {
        if (!controls3d.isDragging) return;
        controls3d.rotY += (e.clientX - controls3d.prevX) * 0.01;
        controls3d.rotX += (e.clientY - controls3d.prevY) * 0.01;
        controls3d.rotX = Math.max(-1.2, Math.min(1.2, controls3d.rotX));
        controls3d.prevX = e.clientX; controls3d.prevY = e.clientY;
        updateCamera();
    });
    canvas.addEventListener('wheel', e => {
        controls3d.zoom = Math.max(0.3, Math.min(3, controls3d.zoom + e.deltaY * 0.001));
        updateCamera();
    });

    // Touch support
    let lastTouch = null;
    canvas.addEventListener('touchstart', e => { lastTouch = e.touches[0]; });
    canvas.addEventListener('touchmove', e => {
        e.preventDefault();
        const t = e.touches[0];
        controls3d.rotY += (t.clientX - lastTouch.clientX) * 0.01;
        controls3d.rotX += (t.clientY - lastTouch.clientY) * 0.01;
        lastTouch = t;
        updateCamera();
    }, { passive: false });

    animate3D();
}

function buildBuilding() {
    if (buildingGroup) scene.remove(buildingGroup);
    buildingGroup = new THREE.Group();

    const W = buildingData.widthM || 30;
    const D = buildingData.depthM || 20;
    const FH = buildingData.floorHeightM || 3;
    const floors = buildingData.floors;
    const nFloors = Math.max(floors.length, buildingData.floorsCount || 1);

    materials = [];
    for (let i = 0; i < nFloors; i++) {
        const t = i / Math.max(nFloors - 1, 1);
        const col = new THREE.Color().setHSL(0.43, 0.6, 0.15 + t * 0.15);
        const mat = new THREE.MeshLambertMaterial({ color: col, transparent: true, opacity: 0.9 });
        materials.push(mat);

        const geo = new THREE.BoxGeometry(W, FH - 0.2, D);
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(0, i * FH + FH/2, 0);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        buildingGroup.add(mesh);

        // Contour de l'étage
        const edgesGeo = new THREE.EdgesGeometry(geo);
        const edgesMat = new THREE.LineBasicMaterial({ color: 0x4fffb0, transparent: true, opacity: 0.3 });
        const edges = new THREE.LineSegments(edgesGeo, edgesMat);
        edges.position.copy(mesh.position);
        buildingGroup.add(edges);

        // Label d'étage (simple cube vert pour indiquer le niveau)
        const labelGeo = new THREE.BoxGeometry(1, 0.5, 1);
        const labelMat = new THREE.MeshBasicMaterial({ color: 0x4fffb0 });
        const label = new THREE.Mesh(labelGeo, labelMat);
        label.position.set(-W/2 - 1, i * FH + FH/2, 0);
        buildingGroup.add(label);

        // Pièces sur ce niveau
        if (floors[i] && floors[i].rooms) {
            floors[i].rooms.forEach(room => {
                const rx = (room.x / 100 - 0.5) * W;
                const rz = (room.y / 100 - 0.5) * D;
                const rw = (room.w / 100) * W - 0.4;
                const rd = (room.h / 100) * D - 0.4;
                const roomGeo = new THREE.BoxGeometry(rw, FH * 0.8, rd);
                const roomMat = new THREE.MeshLambertMaterial({ color: 0x4f9fff, transparent: true, opacity: 0.15, wireframe: false });
                const roomMesh = new THREE.Mesh(roomGeo, roomMat);
                roomMesh.position.set(rx, i * FH + FH/2, rz);
                buildingGroup.add(roomMesh);
            });
        }
    }

    buildingGroup.position.set(-W/6, 0, -D/6);
    scene.add(buildingGroup);
}

function updateCamera() {
    const r = 80 * controls3d.zoom;
    camera.position.x = r * Math.sin(controls3d.rotY) * Math.cos(controls3d.rotX);
    camera.position.y = r * Math.sin(controls3d.rotX) + 20;
    camera.position.z = r * Math.cos(controls3d.rotY) * Math.cos(controls3d.rotX);
    camera.lookAt(0, (buildingData.floorsCount * buildingData.floorHeightM) / 2, 0);
}

function resetCamera() {
    controls3d.rotX = 0.3; controls3d.rotY = 0.5; controls3d.zoom = 1;
    updateCamera();
}

function toggleWireframe() {
    wireframe = !wireframe;
    materials.forEach(m => m.wireframe = wireframe);
    document.getElementById('wireframe-btn').textContent = wireframe ? 'Solide' : 'Filaire';
}

function animate3D() {
    requestAnimationFrame(animate3D);
    renderer.render(scene, camera);
}
</script>
@endpush
@endsection