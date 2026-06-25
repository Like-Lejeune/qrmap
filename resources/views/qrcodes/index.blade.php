@extends('layouts.app')
@section('title', 'QR Codes — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">QR <span>Codes</span></h1>
        <p class="page-sub">Générez et gérez vos QR codes de localisation</p>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>QR Code</th>
                <th>Emplacement</th>
                <th>Site</th>
                <th>Bâtiment</th>
                <th>Étage</th>
                <th>Scans</th>
                <th>Dernier scan</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($qrcodes as $qr)
            <tr>
                <td>
                    <code style="color: var(--accent); font-size: 11px;">{{ substr($qr->uuid, 0, 8) }}...</code>
                </td>
                <td style="font-size: 13px;">{{ $qr->location_label ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->site->name ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->building->name ?? '—' }}</td>
                <td style="color: var(--muted); font-size: 13px;">{{ $qr->floor->label ?? '—' }}</td>
                <td><span class="badge badge-green">{{ $qr->scan_count }}</span></td>
                <td style="color: var(--muted); font-size: 12px;">{{ $qr->last_scanned_at ? $qr->last_scanned_at : '—' }}</td>
                <td>
                    <div class="btn-actions">
                        <a href="{{ route('scan.show', $qr->uuid) }}" class="btn btn-primary btn-sm" target="_blank">Tester</a>
                        <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-outline btn-sm">Voir</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--muted);">
                    Aucun QR code. <a href="{{ route('qrcodes.create') }}" style="color: var(--accent);">Générer le premier</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $qrcodes->links() }}
@endsection