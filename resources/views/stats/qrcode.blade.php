@extends('layouts.app')
@section('title', 'Stats — ' . $qrcode->location_label)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('stats.index') }}">Statistiques</a>
    <span class="sep">/</span>
    <span>{{ $qrcode->location_label }}</span>
</div>

<div class="page-header">
    <div>
        <h1 class="page-title">Stats <span>{{ $qrcode->location_label }}</span></h1>
        <p class="page-sub">{{ $qrcode->site->name ?? '' }} {{ $qrcode->building ? '· '.$qrcode->building->name : '' }}</p>
    </div>
    <div class="btn-actions">
        <a href="{{ route('scan.show', $qrcode->uuid) }}" class="btn btn-primary" target="_blank">Tester le scan</a>
        <a href="{{ route('qrcodes.show', $qrcode) }}" class="btn btn-outline">Voir QR Code</a>
    </div>
</div>

<div class="stats-row" style="grid-template-columns: repeat(3,1fr); margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($qrcode->scan_count) }}</div>
        <div class="stat-label">Scans total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ array_sum(array_values($days)) }}</div>
        <div class="stat-label">30 derniers jours</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $qrcode->last_scanned_at ? $qrcode->last_scanned_at : '—' }}</div>
        <div class="stat-label">Dernier scan</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
    <div class="card">
        <div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1rem;">Activité — 30 jours</div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-qr" role="img" aria-label="Scans quotidiens de ce QR code">Données de scan</canvas>
        </div>
    </div>
    <div class="card">
        <div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1rem;">Appareils</div>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
            <span style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:#4fffb0;border-radius:2px;"></span>Mobile {{ $byDevice['mobile'] ?? 0 }}</span>
            <span style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:#4f9fff;border-radius:2px;"></span>Desktop {{ $byDevice['desktop'] ?? 0 }}</span>
            <span style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;"><span style="width:8px;height:8px;background:#ffb84f;border-radius:2px;"></span>Tablette {{ $byDevice['tablet'] ?? 0 }}</span>
        </div>
        <div style="position:relative; height:140px;">
            <canvas id="chart-devices" role="img" aria-label="Répartition par appareil">Appareils</canvas>
        </div>
    </div>
</div>

{{-- Historique des scans --}}
<div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1rem;">Historique détaillé</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Date & heure</th>
                <th>Appareil</th>
                <th>IP</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="font-size:13px;">{{ $log->scanned_at->format('d/m/Y H:i:s') }}</td>
                <td>
                    <span class="badge {{ $log->device_type === 'mobile' ? 'badge-green' : ($log->device_type === 'tablet' ? 'badge-blue' : 'badge-gray') }}">
                        {{ $log->device_type }}
                    </span>
                </td>
                <td style="color:var(--muted); font-size:12px; font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                <td style="color:var(--muted); font-size:11px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:2rem;">Aucun scan enregistré</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($logs->hasPages())
<div style="margin-top:1rem;">{{ $logs->links() }}</div>
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
new Chart(document.getElementById('chart-qr'), {
    type: 'line',
    data: {
        labels: @json(array_keys($days)).map(d => new Date(d).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'})),
        datasets: [{
            label: 'Scans',
            data: @json(array_values($days)),
            borderColor: '#4fffb0',
            backgroundColor: 'rgba(79,255,176,0.08)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 2,
            pointBackgroundColor: '#4fffb0',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8892a4', font:{ size:10 }, maxRotation:45 } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8892a4', font:{ size:10 } }, beginAtZero:true }
        }
    }
});

const dv = @json($byDevice);
new Chart(document.getElementById('chart-devices'), {
    type: 'doughnut',
    data: {
        labels: ['Mobile','Desktop','Tablette'],
        datasets: [{ data:[dv.mobile||0,dv.desktop||0,dv.tablet||0], backgroundColor:['rgba(79,255,176,0.7)','rgba(79,159,255,0.7)','rgba(255,184,79,0.7)'], borderColor:['#4fffb0','#4f9fff','#ffb84f'], borderWidth:1 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, cutout:'60%' }
});
</script>
@endpush
@endsection