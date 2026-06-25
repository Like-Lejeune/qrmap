@extends('layouts.app')
@section('title', 'Tableau de bord — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tableau de <span>bord</span></h1>
        <p class="page-sub">Vue d'ensemble de vos sites cartographiés</p>
    </div>
    <div class="btn-actions">
        <button class="btn btn-outline" id="importPlanBtn">↑ Importer un plan</button>
        <a href="{{ $createUrl }}" class="btn btn-primary">+ Nouveau plan</a>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['sites'] }}</div>
        <div class="stat-label">Sites</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['buildings'] }}</div>
        <div class="stat-label">Bâtiments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['floors'] }}</div>
        <div class="stat-label">Étages</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['qrcodes'] }}</div>
        <div class="stat-label">QR Codes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['scans'] }}</div>
        <div class="stat-label">Scans total</div>
    </div>
</div>

@if($recentScans->count())
<h2 style="font-family: var(--font-head); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;">Scans récents</h2>
<div class="table-wrap" style="margin-bottom: 2rem;">
    <table>
        <thead>
            <tr>
                <th>QR Code</th>
                <th>Lieu</th>
                <th>Site</th>
                <th>Dernier scan</th>
                <th>Total scans</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentScans as $qr)
            <tr>
                <td><code style="color: var(--accent); font-size: 12px;">{{ substr($qr->uuid, 0, 8) }}...</code></td>
                <td>{{ $qr->location_label ?? '—' }}</td>
                <td>{{ $qr->site->name ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->last_scanned_at ? $qr->last_scanned_at : 'Jamais' }}</td>
                <td><span class="badge badge-green">{{ $qr->scan_count }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<h2 style="font-family: var(--font-head); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;">Sites récents</h2>
<div class="card-grid">
    @forelse($sites as $site)
    <div class="card" id="site-card-{{ $site['id'] }}">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
            <div class="card-title">{{ $site['name'] }}</div>
        </div>
         @if($site['address'])
        <div class="card-meta">📍 {{ $site['address'] }}</div>
        @endif
        @if($site['building'])
        <div style="font-size:13px;color:var(--muted);margin-bottom:4px;">Bâtiment : <strong style="color:var(--text);">{{ $site['building'] }}</strong></div>
        @endif
        <div class="plan-stats">
            <span class="badge badge-gray">{{ $site['floors'] }} étage(s)</span>
            <span class="badge badge-blue">{{ $site['destinations'] }} dest.</span>
            <span class="badge badge-green">{{ $site['count_qrcodes'] }} QR</span>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1/-1; text-align: center; padding: 3rem;">
        <div style="font-size: 2rem; margin-bottom: 1rem;">🏗</div>
        <div style="color: var(--muted); margin-bottom: 1rem;">Aucun site encore créé</div>
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Créer votre premier site</a>
    </div>
    @endforelse
</div>
@endsection