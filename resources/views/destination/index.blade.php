@extends('layouts.app')
@section('title', 'Destinations — ' . $floor->label)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('sites.index') }}">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $building->site) }}">{{ $building->site->name }}</a>
    <span class="sep">/</span>
    <a href="{{ route('buildings.show', $building) }}">{{ $building->name }}</a>
    <span class="sep">/</span>
    <span>Destinations — {{ $floor->label }}</span>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title">Destinations <span>navigables</span></h1>
        <p class="page-sub">{{ $floor->label }} · {{ $building->name }} · {{ $destinations->count() }} destination(s)</p>
    </div>
    <a href="{{ route('buildings.floors.destinations.create', [$building, $floor]) }}" class="btn btn-primary">
        + Ajouter une destination
    </a>
</div>

{{-- Explication --}}
<div class="card" style="margin-bottom:1.5rem;background:rgba(10,171,110,0.05);border-color:rgba(10,171,110,0.2);">
    <div style="display:flex;gap:12px;align-items:flex-start;">
        <div style="font-size:1.5rem;flex-shrink:0;">🧭</div>
        <div>
            <div style="font-weight:600;margin-bottom:4px;color:var(--accent);">Navigation A* activée sur cet étage</div>
            <div style="font-size:13px;color:var(--muted);line-height:1.6;">
                Chaque destination correspond à une pièce de cet étage. Quand un utilisateur scanne un QR code,
                il voit la liste des destinations et le chemin est calculé automatiquement en temps réel
                via l'algorithme A* — le chemin contourne les murs et optimise la trajectoire.
            </div>
        </div>
    </div>
</div>

@if($destinations->isEmpty())
<div class="card" style="text-align:center;padding:3rem;">
    <div style="font-size:2.5rem;margin-bottom:1rem;">🗺</div>
    <div style="font-weight:600;margin-bottom:8px;">Aucune destination configurée</div>
    <div style="color:var(--muted);font-size:14px;margin-bottom:1.5rem;">
        Ajoutez les destinations navigables de cet étage (Pharmacie, Urgences, Accueil…)
    </div>
    <a href="{{ route('buildings.floors.destinations.create', [$building, $floor]) }}" class="btn btn-primary">
        Ajouter la première destination
    </a>
</div>
@else

{{-- Aperçu du plan avec destinations marquées --}}
@if($floor->rooms && count($floor->rooms) > 0)
<div class="card" style="margin-bottom:1.5rem;">
    <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:10px;">
        Aperçu du plan — destinations en vert
    </div>
    <canvas id="dest-preview" width="800" height="300" style="width:100%;height:auto;border-radius:8px;background:var(--bg3);"></canvas>
</div>
@endif

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Ordre</th>
                <th>Destination</th>
                <th>Pièce (room_id)</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($destinations as $dest)
            <tr>
                <td style="color:var(--muted);font-size:13px;">{{ $dest->sort_order }}</td>
                <td>
                    <span style="font-size:16px;margin-right:6px;">{{ $dest->icon }}</span>
                    <strong>{{ $dest->label }}</strong>
                </td>
                <td><code style="color:var(--accent2);font-size:12px;">{{ $dest->room_id }}</code></td>
                <td><span class="badge badge-gray">{{ $dest->category }}</span></td>
                <td style="color:var(--muted);font-size:13px;">{{ $dest->description ?? '—' }}</td>
                <td>
                    @if($dest->is_active)
                        <span class="badge badge-green">Actif</span>
                    @else
                        <span class="badge badge-gray">Inactif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-actions">
                        <a href="{{ route('buildings.floors.destinations.edit', [$building, $floor, $dest]) }}"
                           class="btn btn-outline btn-sm">Éditer</a>
                        <form method="POST"
                              action="{{ route('buildings.floors.destinations.destroy', [$building, $floor, $dest]) }}"
                              onsubmit="return confirm('Supprimer cette destination ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@push('scripts')
<script>
(function() {
    const canvas = document.getElementById('dest-preview');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const rooms = @json($floor->rooms ?? []);
    const dests = @json($destinations->keyBy('room_id'));
    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#0AAB6E';

    ctx.fillStyle = '#0d0f14'; ctx.fillRect(0, 0, W, H);

    // Grille
    ctx.strokeStyle = 'rgba(255,255,255,0.04)'; ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/20) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (let y = 0; y <= H; y += H/10) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    rooms.forEach((r, i) => {
        const isDest = dests[r.id] !== undefined;
        const x = r.x/100*W, y = r.y/100*H, w = r.w/100*W, h = r.h/100*H;
        ctx.fillStyle   = isDest ? 'rgba(10,171,110,0.15)' : 'rgba(79,255,176,0.05)';
        ctx.strokeStyle = isDest ? '#0AAB6E' : 'rgba(79,255,176,0.2)';
        ctx.lineWidth   = isDest ? 2 : 0.75;
        ctx.beginPath(); ctx.roundRect(x,y,w,h,4); ctx.fill(); ctx.stroke();

        const fs = Math.min(13, Math.max(8, w/6));
        ctx.fillStyle = isDest ? '#0AAB6E' : '#8892a4';
        ctx.font = `${isDest ? '600' : '400'} ${fs}px sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(isDest ? (dests[r.id].icon + ' ' + r.name) : r.name, x+w/2, y+h/2);
    });
})();
</script>
@endpush
@endsection