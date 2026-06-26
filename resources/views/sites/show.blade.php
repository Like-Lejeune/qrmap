@extends('layouts.app')
@section('title', $site->name . ' — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="#">Sites</a>
    <span class="sep">/</span>
    <span>{{ $site->name }}</span>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title"><span>{{ $site->name }}</span></h1>
        <p class="page-sub">{{ $site->address ?? 'Adresse non renseignée' }}</p>
    </div>
    <div class="btn-actions">
        <a href="{{ route('sites.buildings.create', $site) }}" class="btn btn-primary">+ Bâtiment</a>
        <a href="{{ route('sites.edit', $site) }}" class="btn btn-outline">Éditer</a>
    </div>
</div>

@if($site->description)
<div class="card" style="margin-bottom: 1.5rem;">
    <p style="color: var(--muted); font-size: 14px;">{{ $site->description }}</p>
    @if($site->latitude && $site->longitude)
    <div style="margin-top: 12px; display: flex; gap: 16px; font-size: 13px; color: var(--muted);">
        <span>📍 {{ $site->latitude }}, {{ $site->longitude }}</span>
    </div>
    @endif
</div>
@endif

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value">{{ $site->buildings->count() }}</div>
        <div class="stat-label">Bâtiments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $site->buildings->sum(fn($b) => $b->floors->count()) }}</div>
        <div class="stat-label">Étages total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $site->qrCodes->count() }}</div>
        <div class="stat-label">QR Codes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $site->qrCodes->sum('scan_count') }}</div>
        <div class="stat-label">Scans total</div>
    </div>
</div>

<h2 style="font-family: var(--font-head); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;">Bâtiments</h2>

@if($site->buildings->isEmpty())
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="font-size: 2rem; margin-bottom: 1rem;">🏢</div>
    <div style="color: var(--muted); margin-bottom: 1rem;">Aucun bâtiment pour ce site</div>
    <a href="{{ route('sites.buildings.create', $site) }}" class="btn btn-primary">Ajouter un bâtiment</a>
</div>
@else
<div class="card-grid">
    @foreach($site->buildings as $building)
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div class="card-title">{{ $building->name }}</div>
            <span class="badge badge-gray">{{ $building->floors_count }} niv.</span>
        </div>
        <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">
            {{ $building->floors->count() }} étage(s) configuré(s)
            @if($building->model_3d) · <span class="badge badge-green" style="font-size: 10px;">3D</span> @endif
            @if($building->floor_plan_2d) · <span class="badge badge-blue" style="font-size: 10px;">2D</span> @endif
        </div>
        <div class="btn-actions">
            <a href="{{ route('buildings.show', $building) }}" class="btn btn-outline btn-sm">Voir →</a>
            <a href="{{ route('sites.buildings.edit', [$site, $building]) }}" class="btn btn-outline btn-sm">Éditer</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($site->qrCodes->isNotEmpty())
<hr class="divider">
<h2 style="font-family: var(--font-head); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;">QR Codes du site</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Emplacement</th>
                <th>Bâtiment</th>
                <th>Étage</th>
                <th>Scans</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($site->qrCodes as $qr)
            <tr>
                <td>{{ $qr->location_label ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->building->name ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->floor->label ?? '—' }}</td>
                <td><span class="badge badge-green">{{ $qr->scan_count }}</span></td>
                <td>
                    <div class="btn-actions">
                        <a href="{{ route('scan.show', $qr->uuid) }}" class="btn btn-primary btn-sm" target="_blank">Scanner</a>
                        <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-outline btn-sm">Détails</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection