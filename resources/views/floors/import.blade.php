@extends('layouts.app')
@section('title', 'Importer un plan — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('sites.index') }}">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $building->site) }}">{{ $building->site->name }}</a>
    <span class="sep">/</span>
    <a href="{{ route('buildings.show', $building) }}">{{ $building->name }}</a>
    <span class="sep">/</span>
    <span>Importer un plan</span>
</div>

<div class="page-header">
    <h1 class="page-title">Importer un <span>plan</span></h1>
    <p style="color:var(--muted); font-size:14px; margin-top:4px;">Formats acceptés : SVG, PNG, JPG · Le plan sera utilisé comme fond de carte</p>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">

<div>
    {{-- DROP ZONE --}}
    <div id="drop-zone" style="border:2px dashed var(--border); border-radius:var(--radius); padding:3rem 2rem; text-align:center; cursor:pointer; transition:all 0.2s; margin-bottom:1.5rem;">
        <div style="font-size:2.5rem; margin-bottom:1rem;">📁</div>
        <div style="font-weight:600; margin-bottom:6px;">Glissez votre fichier ici</div>
        <div style="color:var(--muted); font-size:13px; margin-bottom:1rem;">ou cliquez pour parcourir</div>
        <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
            <span class="badge badge-gray">SVG</span>
            <span class="badge badge-gray">PNG</span>
            <span class="badge badge-gray">JPG</span>
        </div>
        <input type="file" id="file-input" accept=".svg,.png,.jpg,.jpeg" style="display:none;">
    </div>

    {{-- OU : DESSINER LES PIÈCES MANUELLEMENT --}}
    <div style="text-align:center; color:var(--muted); font-size:13px; margin-bottom:1rem;">— ou —</div>

    <form method="POST" id="import-form" action="{{ route('buildings.floors.store', $building) }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="level" id="f-level" value="0">
        <input type="hidden" name="label" id="f-label" value="Rez-de-chaussée">
        <input type="hidden" name="plan_svg" id="f-plan-svg">
        <input type="hidden" name="plan_image_b64" id="f-plan-image">
        <input type="hidden" name="rooms" id="f-rooms" value="[]">

        <div class="card">
            <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:1rem;">Informations de l'étage</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Niveau</label>
                    <input type="number" id="level-input" value="0" min="-5" max="99" oninput="document.getElementById('f-level').value=this.value">
                </div>
                <div class="form-group">
                    <label>Étiquette</label>
                    <input type="text" id="label-input" value="Rez-de-chaussée" oninput="document.getElementById('f-label').value=this.value">
                </div>
            </div>

            <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin:1rem 0 8px;">Ajouter des pièces sur le plan</div>
            <div id="rooms-editor"></div>
            <button type="button" onclick="addRoom()" class="btn btn-outline btn-sm" style="margin-top:6px;">+ Ajouter une pièce</button>

            <hr class="divider">
            <div class="btn-actions">
                <button type="button" onclick="submitImport()" class="btn btn-primary">Enregistrer le plan</button>
                <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline">Annuler</a>
            </div>
        </div>
    </form>
</div>

{{-- APERÇU --}}
<div>
    <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:8px;">Aperçu & éditeur</div>
    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;position:relative;" id="canvas-wrap">
        <canvas id="editor-canvas" width="600" height="400" style="width:100%;height:auto;display:block;cursor:crosshair;"></canvas>
        <div id="draw-hint" style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);font-size:11px;color:var(--muted);background:rgba(13,15,20,0.8);padding:4px 12px;border-radius:20px;pointer-events:none;">Cliquer-déplacer pour placer une pièce</div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px;">
        <button onclick="clearRooms()" class="btn btn-danger btn-sm">Tout effacer</button>
        <button onclick="undoRoom()" class="btn btn-outline btn-sm">Annuler dernière</button>
        <button onclick="toggleGrid()" class="btn btn-outline btn-sm" id="grid-btn">Grille: ON</button>
    </div>
    <p style="font-size:11px;color:var(--muted);margin-top:8px;">💡 Cliquez-glissez sur l'aperçu pour dessiner une pièce. Double-clic sur une pièce pour la renommer.</p>
</div>

</div>

@push('scripts')
<script>
const canvas = document.getElementById('editor-canvas');
const ctx = canvas.getContext('2d');
let bgImage = null, bgSvg = null;
let rooms = [], drawing = false, startX, startY, showGrid = true;
let selectedRoom = null;

// ===== DROP ZONE =====
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor='var(--accent)'; });
dropZone.addEventListener('dragleave', () => dropZone.style.borderColor='var(--border)');
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.style.borderColor='var(--border)'; handleFile(e.dataTransfer.files[0]); });
fileInput.addEventListener('change', e => handleFile(e.target.files[0]));

function handleFile(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        if (file.name.endsWith('.svg')) {
            bgSvg = e.target.result;
            document.getElementById('f-plan-svg').value = bgSvg;
            // Render SVG as image
            const blob = new Blob([bgSvg], {type:'image/svg+xml'});
            const url = URL.createObjectURL(blob);
            const img = new Image();
            img.onload = () => { bgImage = img; render(); };
            img.src = url;
        } else {
            const img = new Image();
            img.onload = () => { bgImage = img; render(); document.getElementById('f-plan-image').value = e.target.result; };
            img.src = e.target.result;
        }
        dropZone.innerHTML = `<div style="font-size:1.5rem;margin-bottom:8px;">✓</div><div style="color:var(--accent);font-weight:600;">${file.name}</div><div style="font-size:12px;color:var(--muted);margin-top:6px;">Fichier chargé</div>`;
    };
    reader.readAsDataURL(file);
}

// ===== CANVAS DRAWING =====
function getCanvasPos(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
}

canvas.addEventListener('mousedown', e => {
    drawing = true;
    const p = getCanvasPos(e);
    startX = p.x; startY = p.y;
});
canvas.addEventListener('mousemove', e => {
    if (!drawing) return;
    const p = getCanvasPos(e);
    render();
    const rx = Math.min(startX, p.x), ry = Math.min(startY, p.y);
    const rw = Math.abs(p.x - startX), rh = Math.abs(p.y - startY);
    ctx.strokeStyle = 'rgba(79,255,176,0.8)'; ctx.lineWidth = 1.5;
    ctx.strokeRect(rx, ry, rw, rh);
    ctx.fillStyle = 'rgba(79,255,176,0.07)';
    ctx.fillRect(rx, ry, rw, rh);
});
canvas.addEventListener('mouseup', e => {
    if (!drawing) return;
    drawing = false;
    const p = getCanvasPos(e);
    const W = canvas.width, H = canvas.height;
    const rx = Math.min(startX, p.x)/W*100, ry = Math.min(startY, p.y)/H*100;
    const rw = Math.abs(p.x-startX)/W*100, rh = Math.abs(p.y-startY)/H*100;
    if (rw < 2 || rh < 2) return;
    const name = prompt('Nom de la pièce ?', 'Pièce ' + (rooms.length+1));
    if (!name) return;
    rooms.push({ id:'R'+Date.now(), name, x:+rx.toFixed(1), y:+ry.toFixed(1), w:+rw.toFixed(1), h:+rh.toFixed(1) });
    syncRooms(); render();
});

canvas.addEventListener('dblclick', e => {
    const p = getCanvasPos(e);
    const W = canvas.width, H = canvas.height;
    const px = p.x/W*100, py = p.y/H*100;
    for (const r of rooms) {
        if (px >= r.x && px <= r.x+r.w && py >= r.y && py <= r.y+r.h) {
            const name = prompt('Renommer la pièce :', r.name);
            if (name) { r.name = name; syncRooms(); render(); }
            break;
        }
    }
});

function render() {
    ctx.fillStyle = '#0d0f14'; ctx.fillRect(0, 0, canvas.width, canvas.height);
    if (bgImage) { ctx.globalAlpha=0.4; ctx.drawImage(bgImage, 0, 0, canvas.width, canvas.height); ctx.globalAlpha=1; }

    if (showGrid) {
        ctx.strokeStyle = 'rgba(255,255,255,0.05)'; ctx.lineWidth = 0.5;
        for (let x = 0; x <= canvas.width; x += canvas.width/20) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,canvas.height); ctx.stroke(); }
        for (let y = 0; y <= canvas.height; y += canvas.height/15) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(canvas.width,y); ctx.stroke(); }
    }

    rooms.forEach((r, i) => {
        const x=r.x/100*canvas.width, y=r.y/100*canvas.height;
        const w=r.w/100*canvas.width, h=r.h/100*canvas.height;
        const hue = (i*53+150)%360;
        ctx.fillStyle = `hsla(${hue},50%,35%,0.18)`;
        ctx.strokeStyle = `hsla(${hue},60%,55%,0.6)`;
        ctx.lineWidth = 1.5;
        ctx.beginPath(); ctx.roundRect(x,y,w,h,4); ctx.fill(); ctx.stroke();
        const fs = Math.min(13, Math.max(8, w/6));
        ctx.fillStyle = '#f0f2f5'; ctx.font = `600 ${fs}px Syne, sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(r.name, x+w/2, y+h/2);
    });
}

function syncRooms() {
    document.getElementById('f-rooms').value = JSON.stringify(rooms);
    renderRoomsList();
}

function renderRoomsList() {
    const el = document.getElementById('rooms-editor');
    el.innerHTML = rooms.map((r,i) => `
        <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;">
            <span style="flex:1;color:var(--text);">${r.name}</span>
            <span style="color:var(--muted);font-size:11px;">x:${r.x}% y:${r.y}% l:${r.w}% h:${r.h}%</span>
            <button type="button" onclick="removeRoom(${i})" style="border:none;background:none;color:var(--danger);cursor:pointer;font-size:14px;">✕</button>
        </div>
    `).join('');
}

function addRoom() {
    rooms.push({id:'R'+Date.now(), name:'Pièce '+(rooms.length+1), x:10, y:10, w:30, h:25});
    syncRooms(); render();
}
function removeRoom(i) { rooms.splice(i,1); syncRooms(); render(); }
function clearRooms()   { rooms=[]; syncRooms(); render(); }
function undoRoom()     { rooms.pop(); syncRooms(); render(); }
function toggleGrid()   { showGrid=!showGrid; document.getElementById('grid-btn').textContent='Grille: '+(showGrid?'ON':'OFF'); render(); }

function submitImport() {
    document.getElementById('f-level').value  = document.getElementById('level-input').value;
    document.getElementById('f-label').value  = document.getElementById('label-input').value;
    syncRooms();
    document.getElementById('import-form').submit();
}

render();
</script>
@endpush
@endsection