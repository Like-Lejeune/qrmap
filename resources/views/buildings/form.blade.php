@extends('layouts.app')
@section('title', isset($building) ? 'Éditer bâtiment' : 'Nouveau bâtiment — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('sites.index') }}">Sites</a>
    <span class="sep">/</span>
    <a href="{{ route('sites.show', $site) }}">{{ $site->name }}</a>
    <span class="sep">/</span>
    <span>{{ isset($building) ? 'Éditer' : 'Nouveau bâtiment' }}</span>
</div>

<div class="page-header">
    <h1 class="page-title">{{ isset($building) ? 'Éditer le bâtiment' : 'Nouveau <span>bâtiment</span>' }}</h1>
</div>

<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ isset($building) ? route('sites.buildings.update', [$site, $building]) : route('sites.buildings.store', $site) }}">
        @csrf
        @if(isset($building)) @method('PUT') @endif

        <div class="form-row">
            <div class="form-group">
                <label>Nom du bâtiment *</label>
                <input type="text" name="name" value="{{ old('name', $building->name ?? '') }}" placeholder="Ex: Bâtiment A, Tour Nord..." required>
                @error('name')<span style="color: var(--danger); font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Nombre de niveaux</label>
                <input type="number" name="floors_count" min="1" max="100" value="{{ old('floors_count', $building->floors_count ?? 1) }}">
            </div>
        </div>

        <hr class="divider">
        <div style="margin-bottom: 1rem;">
            <div style="font-family: var(--font-head); font-size: 0.9rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">Plan 2D</div>
            <p style="font-size: 13px; color: var(--muted); margin-bottom: 1rem;">Collez un SVG ou du GeoJSON représentant la vue de dessus du bâtiment.</p>
        </div>

        <div class="form-group">
            <label>Données plan 2D (SVG inline ou GeoJSON)</label>
            <textarea name="floor_plan_2d" placeholder='{"type":"FeatureCollection","features":[...]} ou <svg>...</svg>' style="min-height: 120px; font-family: monospace; font-size: 12px;">{{ old('floor_plan_2d', isset($building->floor_plan_2d) ? json_encode($building->floor_plan_2d) : '') }}</textarea>
        </div>

        <hr class="divider">
        <div style="margin-bottom: 1rem;">
            <div style="font-family: var(--font-head); font-size: 0.9rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">Modèle 3D</div>
            <p style="font-size: 13px; color: var(--muted); margin-bottom: 1rem;">Définissez les dimensions pour générer automatiquement un rendu 3D simple, ou fournissez une URL vers un fichier .glb/.gltf.</p>
        </div>

        <div class="form-row three">
            <div class="form-group">
                <label>Largeur (m)</label>
                <input type="number" name="width_m" step="0.1" value="{{ old('width_m', $building->model_3d['width_m'] ?? '') }}" placeholder="Ex: 40">
            </div>
            <div class="form-group">
                <label>Profondeur (m)</label>
                <input type="number" name="depth_m" step="0.1" value="{{ old('depth_m', $building->model_3d['depth_m'] ?? '') }}" placeholder="Ex: 20">
            </div>
            <div class="form-group">
                <label>Hauteur/étage (m)</label>
                <input type="number" name="floor_height_m" step="0.1" value="{{ old('floor_height_m', $building->model_3d['floor_height_m'] ?? 3) }}" placeholder="3">
            </div>
        </div>

        <div class="form-group">
            <label>URL fichier 3D (.glb / .gltf) — optionnel</label>
            <input type="url" name="model_3d_url" value="{{ old('model_3d_url', $building->model_3d['url'] ?? '') }}" placeholder="https://...model.glb">
        </div>

        <hr class="divider">
        <div class="btn-actions">
            <button type="submit" class="btn btn-primary">{{ isset($building) ? 'Enregistrer' : 'Créer le bâtiment' }}</button>
            <a href="{{ route('sites.show', $site) }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
@endsection