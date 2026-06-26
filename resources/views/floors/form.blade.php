@extends('layouts.app')
@section('title', 'Configurer l\'étage — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="#">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $building->site) }}">{{ $building->site->name }}</a>
    <span class="sep">/</span>
    <a href="{{ route('buildings.show', $building) }}">{{ $building->name }}</a>
    <span class="sep">/</span>
    <span>{{ isset($floor) ? 'Éditer étage' : 'Nouvel étage' }}</span>
</div>

<div class="page-header">
    <h1 class="page-title">{{ isset($floor) ? 'Éditer l\'étage' : 'Nouvel <span>étage</span>' }}</h1>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">

{{-- FORMULAIRE --}}
<div class="card">
    <form method="POST" action="{{ isset($floor) ? route('buildings.floors.update', [$building, $floor]) : route('buildings.floors.store', $building) }}">
        @csrf
        @if(isset($floor)) @method('PUT') @endif

        <div class="form-row">
            <div class="form-group">
                <label>Niveau *</label>
                <input type="number" name="level" min="-5" max="100" value="{{ old('level', $floor->level ?? 0) }}" required>
                <small style="color: var(--muted); font-size: 11px;">0 = Rez-de-chaussée, -1 = Sous-sol</small>
            </div>
            <div class="form-group">
                <label>Étiquette</label>
                <input type="text" name="label" value="{{ old('label', $floor->label ?? '') }}" placeholder="Ex: Rez-de-chaussée">
            </div>
        </div>

        <div class="form-group">
            <label>Plan SVG (optionnel)</label>
            <textarea name="plan_svg" id="plan_svg" placeholder="Collez votre SVG ici: <svg viewBox='0 0 800 400'>...</svg>" style="min-height: 100px; font-family: monospace; font-size: 12px;">{{ old('plan_svg', isset($floor->plan_2d) && is_array($floor->plan_2d) ? ($floor->plan_2d['svg'] ?? '') : '') }}</textarea>
        </div>

        <hr class="divider">
        <div style="font-family: var(--font-head); font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px;">Pièces (éditeur visuel)</div>

        <div id="rooms-list"></div>
        <input type="hidden" name="rooms" id="rooms-input" value="{{ old('rooms', isset($floor->rooms) ? json_encode($floor->rooms) : '[]') }}">

        <button type="button" onclick="addRoom()" class="btn btn-outline btn-sm" style="margin-bottom: 1rem;">+ Ajouter une pièce</button>

        <hr class="divider">
        <div class="btn-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

{{-- APERÇU CANVAS --}}
<div>
    <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;">Aperçu en temps réel</div>
    <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem;">
        <canvas id="preview-canvas" width="600" height="350" style="width: 100%; height: auto; border-radius: 8px; background: #0d0f14;"></canvas>
    </div>
    <p style="font-size: 12px; color: var(--muted); margin-top: 8px;">Les pièces sont positionnées en % (0–100) de la surface du plan.</p>
</div>

</div>

@push('scripts')
<script>
let rooms = JSON.parse(document.getElementById('rooms-input').value || '[]');

function addRoom() {
    rooms.push({ id: 'R' + Date.now(), name: 'Pièce ' + (rooms.length + 1), x: 10, y: 10, w: 30, h: 25 });
    renderRoomsList();
    renderPreview();
}

function removeRoom(idx) {
    rooms.splice(idx, 1);
    renderRoomsList();
    renderPreview();
}

function updateRoom(idx, field, value) {
    rooms[idx][field] = field === 'name' ? value : parseFloat(value);
    document.getElementById('rooms-input').value = JSON.stringify(rooms);
    renderPreview();
}

function renderRoomsList() {
    const list = document.getElementById('rooms-list');
    document.getElementById('rooms-input').value = JSON.stringify(rooms);
    list.innerHTML = rooms.map((r, i) => `
        <div style="background: var(--bg3); border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;">
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                <input type="text" value="${r.name}" onchange="updateRoom(${i},'name',this.value)" style="flex:1; padding: 6px 10px; font-size: 13px;" placeholder="Nom de la pièce">
                <button type="button" onclick="removeRoom(${i})" class="btn btn-danger btn-sm">✕</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 6px;">
                <div><label style="font-size: 10px;">X%</label><input type="number" value="${r.x}" min="0" max="95" onchange="updateRoom(${i},'x',this.value)" style="padding: 4px 8px; font-size: 12px;"></div>
                <div><label style="font-size: 10px;">Y%</label><input type="number" value="${r.y}" min="0" max="95" onchange="updateRoom(${i},'y',this.value)" style="padding: 4px 8px; font-size: 12px;"></div>
                <div><label style="font-size: 10px;">L%</label><input type="number" value="${r.w}" min="5" max="100" onchange="updateRoom(${i},'w',this.value)" style="padding: 4px 8px; font-size: 12px;"></div>
                <div><label style="font-size: 10px;">H%</label><input type="number" value="${r.h}" min="5" max="100" onchange="updateRoom(${i},'h',this.value)" style="padding: 4px 8px; font-size: 12px;"></div>
            </div>
        </div>
    `).join('');
}

function renderPreview() {
    const canvas = document.getElementById('preview-canvas');
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;

    ctx.fillStyle = '#0d0f14';
    ctx.fillRect(0, 0, W, H);

    // Grille
    ctx.strokeStyle = 'rgba(255,255,255,0.04)';
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/10) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, H); ctx.stroke(); }
    for (let y = 0; y <= H; y += H/10) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke(); }

    rooms.forEach((r, i) => {
        const x = r.x/100*W, y = r.y/100*H, w = r.w/100*W, h = r.h/100*H;
        const hue = (i * 47) % 360;
        ctx.fillStyle = `hsla(${hue}, 60%, 40%, 0.15)`;
        ctx.strokeStyle = `hsla(${hue}, 60%, 60%, 0.6)`;
        ctx.lineWidth = 1.5;

        ctx.beginPath(); ctx.roundRect(x, y, w, h, 4); ctx.fill(); ctx.stroke();

        ctx.fillStyle = '#f0f2f5';
        ctx.font = `bold ${Math.min(13, w/4)}px Syne, sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(r.name, x + w/2, y + h/2);
    });

    if (rooms.length === 0) {
        ctx.fillStyle = '#8892a4'; ctx.font = '14px DM Sans, sans-serif';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('Ajoutez des pièces pour voir l\'aperçu', W/2, H/2);
    }
}

// Aperçu SVG en temps réel
document.getElementById('plan_svg').addEventListener('input', function() {
    if (this.value.trim().startsWith('<svg')) {
        const canvas = document.getElementById('preview-canvas');
        canvas.style.display = 'none';
        let prev = document.getElementById('svg-preview');
        if (!prev) { prev = document.createElement('div'); prev.id = 'svg-preview'; canvas.parentNode.appendChild(prev); }
        prev.innerHTML = this.value;
        prev.querySelector('svg').style.width = '100%';
    }
});

renderRoomsList();
renderPreview();
</script>
@endpush
@endsection