@extends('layouts.app')
@section('title', 'Recherche — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Recherche <span>globale</span></h1>
        @if(request('q'))
        <p class="page-sub">{{ $total }} résultat(s) pour « {{ request('q') }} »</p>
        @endif
    </div>
</div>

<form method="GET" action="{{ route('search') }}" style="margin-bottom:1.5rem;">
    <div style="display:flex; gap:10px; max-width:600px;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Chercher un site, bâtiment, étage, QR code..."
               autofocus
               style="flex:1;background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:12px 16px;color:var(--text);font-size:15px;outline:none;transition:border-color 0.2s;">
        <button type="submit" class="btn btn-primary" style="padding:12px 20px;">Rechercher</button>
    </div>
</form>

@if(request('q'))

    {{-- SITES --}}
    @if($sites->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            Sites <span class="badge badge-blue">{{ $sites->count() }}</span>
        </div>
        <div class="card-grid">
            @foreach($sites as $site)
            <a href="{{ route('sites.show', $site) }}" style="text-decoration:none;">
                <div class="card">
                    <div class="card-title">@highlight($site->name, request('q'))</div>
                    <div class="card-meta">{{ $site->address ?? 'Adresse non renseignée' }}</div>
                    <div style="display:flex;gap:6px;">
                        <span class="badge badge-gray">{{ $site->buildings_count ?? 0 }} bât.</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- BÂTIMENTS --}}
    @if($buildings->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            Bâtiments <span class="badge badge-blue">{{ $buildings->count() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Bâtiment</th><th>Site</th><th>Niveaux</th><th></th></tr></thead>
                <tbody>
                    @foreach($buildings as $b)
                    <tr>
                        <td style="font-weight:500;">{{ $b->name }}</td>
                        <td style="color:var(--muted);font-size:13px;">{{ $b->site->name ?? '—' }}</td>
                        <td><span class="badge badge-gray">{{ $b->floors_count }}</span></td>
                        <td><a href="{{ route('buildings.show', $b) }}" class="btn btn-outline btn-sm">Voir →</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- QR CODES --}}
    @if($qrcodes->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            QR Codes <span class="badge badge-green">{{ $qrcodes->count() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Emplacement</th><th>Site</th><th>Bâtiment</th><th>Scans</th><th></th></tr></thead>
                <tbody>
                    @foreach($qrcodes as $qr)
                    <tr>
                        <td style="font-weight:500;">{{ $qr->location_label }}</td>
                        <td style="color:var(--muted);font-size:13px;">{{ $qr->site->name ?? '—' }}</td>
                        <td style="color:var(--muted);font-size:13px;">{{ $qr->building->name ?? '—' }}</td>
                        <td><span class="badge badge-green">{{ $qr->scan_count }}</span></td>
                        <td>
                            <div class="btn-actions">
                                <a href="{{ route('scan.show', $qr->uuid) }}" class="btn btn-primary btn-sm" target="_blank">Scanner</a>
                                <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-outline btn-sm">Détail</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($total === 0)
    <div class="card" style="text-align:center;padding:3rem;">
        <div style="font-size:2.5rem;margin-bottom:1rem;">🔍</div>
        <div style="font-weight:600;margin-bottom:8px;">Aucun résultat</div>
        <div style="color:var(--muted);font-size:14px;">Aucun site, bâtiment ou QR code ne correspond à « {{ request('q') }} »</div>
    </div>
    @endif

@else
<div class="card" style="text-align:center;padding:4rem;max-width:500px;margin:0 auto;">
    <div style="font-size:3rem;margin-bottom:1rem;">🗺</div>
    <div style="font-weight:600;font-size:1.1rem;margin-bottom:8px;">Recherchez dans tout QRMap</div>
    <div style="color:var(--muted);font-size:14px;line-height:1.7;">Trouvez rapidement un site, bâtiment, étage ou emplacement QR Code par son nom.</div>
</div>
@endif
@endsection