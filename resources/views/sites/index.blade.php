@extends('layouts.app')
@section('title', 'Sites — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sites</h1>
        <p class="page-sub">{{ $sites->total() }} site(s) enregistré(s)</p>
    </div>
    <a href="{{ route('sites.create') }}" class="btn btn-primary">+ Nouveau site</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nom du site</th>
                <th>Adresse</th>
                <th>Bâtiments</th>
                <th>QR Codes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sites as $site)
            <tr>
                <td>
                    <a href="{{ route('sites.show', $site) }}" style="color: var(--text); text-decoration: none; font-weight: 500;">
                        {{ $site->name }}
                    </a>
                </td>
                <td style="color: var(--muted); font-size: 13px;">{{ $site->address ?? '—' }}</td>
                <td><span class="badge badge-blue">{{ $site->buildings_count }}</span></td>
                <td><span class="badge badge-green">{{ $site->qr_codes_count }}</span></td>
                <td>
                    <div class="btn-actions">
                        <a href="{{ route('sites.show', $site) }}" class="btn btn-outline btn-sm">Voir</a>
                        <a href="{{ route('sites.edit', $site) }}" class="btn btn-outline btn-sm">Éditer</a>
                        <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Supprimer ce site ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--muted);">
                    Aucun site. <a href="{{ route('sites.create') }}" style="color: var(--accent);">Créer le premier</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($sites->hasPages())
<div style="margin-top: 1rem; display: flex; justify-content: center;">
    {{ $sites->links() }}
</div>
@endif
@endsection