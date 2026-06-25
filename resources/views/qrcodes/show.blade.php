@extends('layouts.app')
@section('title', 'QR Code — ' . $qrcode->location_label)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('qrcodes.index') }}">QR Codes</a>
    <span class="sep">/</span>
    <span>{{ substr($qrcode->uuid, 0, 8) }}...</span>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title"><span>{{ $qrcode->location_label }}</span></h1>
        <p class="page-sub">{{ $qrcode->site->name ?? '' }} {{ $qrcode->building ? '· '.$qrcode->building->name : '' }} {{ $qrcode->floor ? '· '.$qrcode->floor->label : '' }}</p>
    </div>
    <div class="btn-actions">
        <a href="{{ route('scan.show', $qrcode->uuid) }}" class="btn btn-primary" target="_blank">Tester le scan</a>
        <form method="POST" action="{{ route('qrcodes.destroy', $qrcode) }}" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; align-items: start;">

{{-- QR CODE --}}
<div>
    <div class="qr-card">
        @if(Storage::disk('public')->exists('qrcodes/'.$qrcode->uuid.'.svg'))
        <img src="{{ asset('storage/qrcodes/'.$qrcode->uuid.'.svg') }}" alt="QR Code {{ $qrcode->location_label }}">
        @else
        <div style="width:180px; height:180px; background: var(--bg3); border-radius: 8px; display:flex; align-items:center; justify-content:center; color: var(--muted); font-size:13px; margin: 0 auto;">Image non disponible</div>
        @endif
        <div style="margin-top: 1rem; font-size: 13px; color: var(--muted);">Scannez pour voir le plan</div>
        <div style="margin-top: 12px;">
            <a href="{{ asset('storage/qrcodes/'.$qrcode->uuid.'.svg') }}" download class="btn btn-outline btn-sm">Télécharger PNG</a>
        </div>
    </div>

    <div class="card" style="margin-top: 1rem; text-align: center;">
        <div class="stat-value">{{ $qrcode->scan_count }}</div>
        <div class="stat-label">Scans total</div>
        @if($qrcode->last_scanned_at)
        <div style="font-size: 12px; color: var(--muted); margin-top: 8px;">Dernier : {{ $qrcode->last_scanned_at }}</div>
        @endif
    </div>
</div>

{{-- INFOS --}}
<div>
    <div class="card" style="margin-bottom: 1rem;">
        <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 1rem;">Informations</div>
        <table style="font-size: 14px;">
            <tr>
                <td style="color: var(--muted); padding: 6px 0; width: 160px;">UUID</td>
                <td><code style="color: var(--accent); font-size: 12px;">{{ $qrcode->uuid }}</code></td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">URL de scan</td>
                <td>
                    <code style="font-size: 12px; word-break: break-all;">{{ route('scan.show', $qrcode->uuid) }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ route('scan.show', $qrcode->uuid) }}').then(() => this.textContent='✓ Copié!')" class="btn btn-outline btn-sm" style="margin-left: 8px;">Copier</button>
                </td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">Site</td>
                <td>{{ $qrcode->site->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">Bâtiment</td>
                <td>{{ $qrcode->building->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">Étage</td>
                <td>{{ $qrcode->floor->label ?? '—' }}</td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">Position</td>
                <td>X: {{ $qrcode->pos_x ?? '—' }}% · Y: {{ $qrcode->pos_y ?? '—' }}%</td>
            </tr>
            <tr>
                <td style="color: var(--muted); padding: 6px 0;">Créé le</td>
                <td>{{ $qrcode->created_at->format('d/m/Y à H:i') }}</td>
            </tr>
        </table>
    </div>

    {{-- Aperçu du plan avec marqueur --}}
    @if($qrcode->floor && $qrcode->floor->rooms)
    <div class="card">
        <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); margin-bottom: 1rem;">Position sur le plan</div>
        <div style="position: relative; background: var(--bg3); border-radius: 8px; overflow: hidden;">
            <canvas id="mini-map" width="500" height="280" style="width: 100%; height: auto;"></canvas>
            <div class="pos-marker" style="left: {{ $qrcode->pos_x ?? 50 }}%; top: {{ $qrcode->pos_y ?? 50 }}%;"></div>
        </div>
    </div>
    @endif
</div>
</div>

@push('scripts')
<script>
(function() {
    const canvas = document.getElementById('mini-map');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const rooms = @json($qrcode->floor->rooms ?? []);

    ctx.fillStyle = '#0d0f14';
    ctx.fillRect(0, 0, W, H);

    ctx.strokeStyle = 'rgba(255,255,255,0.04)';
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/10) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (let y = 0; y <= H; y += H/10) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    rooms.forEach((r, i) => {
        const x = r.x/100*W, y = r.y/100*H, w = r.w/100*W, h = r.h/100*H;
        ctx.fillStyle = 'rgba(79,255,176,0.07)';
        ctx.strokeStyle = 'rgba(79,255,176,0.35)';
        ctx.lineWidth = 1.5;
        ctx.beginPath(); ctx.roundRect(x,y,w,h,4); ctx.fill(); ctx.stroke();
        ctx.fillStyle = '#f0f2f5'; ctx.font = `bold ${Math.min(12, w/4)}px Syne,sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(r.name, x+w/2, y+h/2);
    });
})();
</script>
@endpush
@endsection