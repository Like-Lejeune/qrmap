@extends('layouts.app')
@section('title', 'Statistiques — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Statistiques <span>de scan</span></h1>
        <p class="page-sub">Activité des 30 derniers jours</p>
    </div>
</div>

{{-- KPIs --}}
<div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($todayScans) }}</div>
        <div class="stat-label">Aujourd'hui</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($weekScans) }}</div>
        <div class="stat-label">Cette semaine</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($monthScans) }}</div>
        <div class="stat-label">Ce mois</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($totalScans) }}</div>
        <div class="stat-label">Total historique</div>
    </div>
</div>

{{-- Graphique activité --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
        <div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted);">Scans par jour — 30 jours</div>
    </div>
    <div style="position:relative; height:200px;">
        <canvas id="chart-activity" role="img" aria-label="Graphique des scans quotidiens sur 30 jours">Données d'activité des scans</canvas>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    {{-- Appareils --}}
    <div class="card">
        <div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1.2rem;">Appareils</div>
        <div style="display:flex; gap:8px; margin-bottom:1rem; flex-wrap:wrap;">
            <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);">
                <span style="width:10px;height:10px;border-radius:2px;background:#4fffb0;display:inline-block;"></span>
                Mobile {{ $byDevice['mobile'] ?? 0 }}
            </span>
            <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);">
                <span style="width:10px;height:10px;border-radius:2px;background:#4f9fff;display:inline-block;"></span>
                Desktop {{ $byDevice['desktop'] ?? 0 }}
            </span>
            <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);">
                <span style="width:10px;height:10px;border-radius:2px;background:#ffb84f;display:inline-block;"></span>
                Tablette {{ $byDevice['tablet'] ?? 0 }}
            </span>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="chart-devices" role="img" aria-label="Répartition des scans par type d'appareil">Données appareils</canvas>
        </div>
    </div>

    {{-- Top sites --}}
    <div class="card">
        <div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1.2rem;">Sites les plus scannés</div>
        @forelse($bySite as $site)
        @php $max = $bySite->max('total_scans') ?: 1; @endphp
        <div style="margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">
                <span>{{ $site->name }}</span>
                <span style="color:var(--accent); font-weight:600;">{{ number_format($site->total_scans ?? 0) }}</span>
            </div>
            <div style="height:4px; background:var(--bg3); border-radius:2px; overflow:hidden;">
                <div style="height:100%; background:var(--accent); border-radius:2px; width:{{ $max > 0 ? (($site->total_scans ?? 0) / $max) * 100 : 0 }}%; transition:width 0.6s ease;"></div>
            </div>
        </div>
        @empty
        <div style="color:var(--muted); font-size:13px;">Aucune donnée</div>
        @endforelse
    </div>
</div>

{{-- Top QR codes --}}
<div style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:1rem;">Top 10 QR Codes</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Emplacement</th>
                <th>Site</th>
                <th>Bâtiment</th>
                <th>Scans</th>
                <th>Dernier scan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($topQr as $i => $qr)
            <tr>
                <td style="color:var(--muted); font-size:13px;">{{ $i + 1 }}</td>
                <td style="font-weight:500;">{{ $qr->location_label ?? '—' }}</td>
                <td style="color:var(--muted); font-size:13px;">{{ $qr->site->name ?? '—' }}</td>
                <td style="color:var(--muted); font-size:13px;">{{ $qr->building->name ?? '—' }}</td>
                <td>
                    <span class="badge badge-green">{{ number_format($qr->scan_count) }}</span>
                </td>
                <td style="color:var(--muted); font-size:12px;">{{ $qr->last_scanned_at ? $qr->last_scanned_at : '—' }}</td>
                <td>
                    <a href="{{ route('stats.qrcode', $qr) }}" class="btn btn-outline btn-sm">Détail →</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; color:var(--muted); padding:2rem;">Aucun scan enregistré</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
const days   = @json(array_keys($days));
const counts = @json(array_values($days));

// Graphique activité
new Chart(document.getElementById('chart-activity'), {
    type: 'bar',
    data: {
        labels: days.map(d => { const dt = new Date(d); return dt.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'}); }),
        datasets: [{
            label: 'Scans',
            data: counts,
            backgroundColor: 'rgba(79,255,176,0.25)',
            borderColor: '#4fffb0',
            borderWidth: 1.5,
            borderRadius: 3,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8892a4', font: { size: 11 }, maxRotation: 45, autoSkip: true } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8892a4', font: { size: 11 } }, beginAtZero: true }
        }
    }
});

// Graphique appareils
const dv = @json($byDevice);
new Chart(document.getElementById('chart-devices'), {
    type: 'doughnut',
    data: {
        labels: ['Mobile', 'Desktop', 'Tablette'],
        datasets: [{
            data: [dv.mobile || 0, dv.desktop || 0, dv.tablet || 0],
            backgroundColor: ['rgba(79,255,176,0.7)', 'rgba(79,159,255,0.7)', 'rgba(255,184,79,0.7)'],
            borderColor: ['#4fffb0', '#4f9fff', '#ffb84f'],
            borderWidth: 1,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%',
    }
});
</script>
@endpush
@endsection