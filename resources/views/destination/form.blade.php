@extends('layouts.app')
@section('title', isset($destination) ? 'Éditer destination' : 'Nouvelle destination')

@section('content')
<div class="breadcrumb">
    <a href="#">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $building->site) }}">{{ $building->site->name }}</a>
    <span class="sep">/</span>
    <a href="{{ route('buildings.show', $building) }}">{{ $building->name }}</a>
    <span class="sep">/</span>
    <a href="{{ route('buildings.floors.destinations.index', [$building, $floor]) }}">Destinations</a>
    <span class="sep">/</span>
    <span>{{ isset($destination) ? 'Éditer' : 'Nouvelle' }}</span>
</div>

<div class="page-header">
    <h1 class="page-title">{{ isset($destination) ? 'Éditer la destination' : 'Nouvelle <span>destination</span>' }}</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start;">

{{-- Formulaire --}}
<div class="card">
    <form method="POST" action="{{ isset($destination)
        ? route('buildings.floors.destinations.update', [$building, $floor, $destination])
        : route('buildings.floors.destinations.store', [$building, $floor]) }}">
        @csrf
        @if(isset($destination)) @method('PUT') @endif

        <div class="form-group">
            <label>Pièce associée *</label>
            <p style="font-size:12px;color:var(--muted);margin-bottom:8px;">
                Cliquez sur une pièce dans l'aperçu à droite, ou choisissez dans la liste.
            </p>
            <select name="room_id" id="room_select" required onchange="highlightRoom(this.value)">
                <option value="">— Sélectionner une pièce —</option>
                @foreach($floor->rooms ?? [] as $room)
                <option value="{{ $room['id'] }}"
                    {{ old('room_id', $destination->room_id ?? '') === $room['id'] ? 'selected' : '' }}>
                    {{ $room['name'] }} ({{ $room['id'] }})
                </option>
                @endforeach
            </select>
            @error('room_id')<span style="color:var(--danger);font-size:12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Label affiché * <small style="font-size:11px;">(ce que voit l'utilisateur)</small></label>
                <input type="text" name="label"
                       value="{{ old('label', $destination->label ?? '') }}"
                       placeholder="Ex: Pharmacie, Urgences, Accueil…" required>
                @error('label')<span style="color:var(--danger);font-size:12px;">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Catégorie *</label>
                <select name="category">
                    @foreach(['medical' => '🏥 Médical', 'admin' => '🏛 Administration', 'service' => '☕ Service', 'exit' => '🚪 Sortie / Escaliers', 'other' => '📍 Autre'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('category', $destination->category ?? 'other') === $val ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Description courte <small style="font-size:11px;">(optionnel)</small></label>
            <input type="text" name="description"
                   value="{{ old('description', $destination->description ?? '') }}"
                   placeholder="Ex: Ouvert 24h/24, Niveau -1, Bâtiment B…">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Ordre d'affichage</label>
                <input type="number" name="sort_order" min="0" max="999"
                       value="{{ old('sort_order', $destination->sort_order ?? 0) }}">
                <small style="color:var(--muted);font-size:11px;">0 = affiché en premier</small>
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:1.25rem;">
                <label style="display:flex;align-items:center;gap:10px;text-transform:none;letter-spacing:0;font-size:14px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $destination->is_active ?? true) ? 'checked' : '' }}
                           style="width:auto;accent-color:var(--accent);">
                    Destination active (visible lors du scan)
                </label>
            </div>
        </div>

        <hr class="divider">
        <div class="btn-actions">
            <button type="submit" class="btn btn-primary">
                {{ isset($destination) ? 'Enregistrer' : 'Créer la destination' }}
            </button>
            <a href="{{ route('buildings.floors.destinations.index', [$building, $floor]) }}"
               class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

{{-- Aperçu plan avec sélection de pièce --}}
<div>
    <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:8px;">
        Cliquez sur une pièce pour la sélectionner
    </div>
    <div style="position:relative;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
        <canvas id="room-picker" width="380" height="280" style="width:100%;height:auto;cursor:pointer;"></canvas>
    </div>
    <div style="margin-top:8px;font-size:12px;color:var(--muted);" id="selected-info">
        Aucune pièce sélectionnée
    </div>
</div>

</div>

@push('scripts')
<script>
const rooms = @json($floor->rooms ?? []);
const W = 380, H = 280;
const canvas = document.getElementById('room-picker');
const ctx = canvas.getContext('2d');
let selectedRoomId = '{{ old('room_id', $destination->room_id ?? '') }}';

function drawPicker() {
    ctx.fillStyle = '#0d0f14'; ctx.fillRect(0, 0, W, H);
    ctx.strokeStyle = 'rgba(255,255,255,0.04)'; ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/16) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (let y = 0; y <= H; y += H/10) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    rooms.forEach(r => {
        const isSel = r.id === selectedRoomId;
        const x = r.x/100*W, y = r.y/100*H, w = r.w/100*W, h = r.h/100*H;
        ctx.fillStyle   = isSel ? 'rgba(10,171,110,0.2)' : 'rgba(79,255,176,0.05)';
        ctx.strokeStyle = isSel ? '#0AAB6E'  : 'rgba(79,255,176,0.25)';
        ctx.lineWidth   = isSel ? 2 : 0.75;
        ctx.beginPath(); ctx.roundRect(x,y,w,h,4); ctx.fill(); ctx.stroke();
        const fs = Math.min(12, Math.max(8, w/5));
        ctx.fillStyle = isSel ? '#0AAB6E' : '#8892a4';
        ctx.font = `${isSel ? '600' : '400'} ${fs}px sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(r.name, x+w/2, y+h/2);
    });
}

canvas.addEventListener('click', e => {
    const rect = canvas.getBoundingClientRect();
    const mx = (e.clientX - rect.left) / rect.width * W;
    const my = (e.clientY - rect.top)  / rect.height * H;
    const px = mx/W*100, py = my/H*100;

    for (const r of rooms) {
        if (px >= r.x && px <= r.x + r.w && py >= r.y && py <= r.y + r.h) {
            selectedRoomId = r.id;
            document.getElementById('room_select').value = r.id;
            document.getElementById('selected-info').textContent = '✓ Sélectionné : ' + r.name + ' (' + r.id + ')';
            document.getElementById('selected-info').style.color = '#0AAB6E';
            // Auto-remplir le label si vide
            const labelField = document.querySelector('[name="label"]');
            if (!labelField.value) labelField.value = r.name;
            drawPicker();
            break;
        }
    }
});

function highlightRoom(id) {
    selectedRoomId = id;
    const r = rooms.find(r => r.id === id);
    if (r) {
        document.getElementById('selected-info').textContent = '✓ Sélectionné : ' + r.name + ' (' + r.id + ')';
        document.getElementById('selected-info').style.color = '#0AAB6E';
    }
    drawPicker();
}

drawPicker();
if (selectedRoomId) highlightRoom(selectedRoomId);
</script>
@endpush
@endsection