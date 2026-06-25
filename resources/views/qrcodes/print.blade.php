@extends('layouts.app')
@section('title', 'Impression des QR Codes — QRMap')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Impression <span>QR Codes</span></h1>
        <p class="page-sub">Sélectionnez les QR codes à imprimer en lot</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary" id="print-btn">Imprimer la sélection</button>
</div>

{{-- FILTRES --}}
<div class="card" style="margin-bottom:1.5rem;" id="filter-panel">
    <div style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        <div style="flex:1; min-width:180px;">
            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:6px;">Filtrer par site</label>
            <select id="filter-site" onchange="filterQR()" style="width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-size:13px;outline:none;">
                <option value="">Tous les sites</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}">{{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:180px;">
            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:6px;">Format d'impression</label>
            <select id="print-format" onchange="applyFormat(this.value)" style="width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-size:13px;outline:none;">
                <option value="2x3">2 × 3 par page (A4)</option>
                <option value="1x1">1 grand par page</option>
                <option value="4x4">4 × 4 petits</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <button onclick="selectAll()" class="btn btn-outline btn-sm">Tout sélectionner</button>
            <button onclick="selectNone()" class="btn btn-outline btn-sm">Désélectionner</button>
            <span id="selected-count" class="badge badge-green">0 sélectionné(s)</span>
        </div>
    </div>
</div>

{{-- GRILLE QR CODES --}}
<div id="qr-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem;">
    @forelse($qrcodes as $qr)
    <div class="qr-item card" data-site="{{ $qr->site_id }}" data-id="{{ $qr->id }}"
         onclick="toggleSelect(this)"
         style="cursor:pointer; text-align:center; transition:all 0.15s; user-select:none;">

        <div class="select-indicator" style="width:20px;height:20px;border-radius:50%;border:2px solid var(--border);margin:0 auto 10px;transition:all 0.15s;display:flex;align-items:center;justify-content:center;font-size:11px;"></div>

        @if(Storage::disk('public')->exists('qrcodes/'.$qr->uuid.'.svg'))
        <img src="{{ asset('storage/qrcodes/'.$qr->uuid.'.svg') }}"
             alt="QR {{ $qr->location_label }}"
             style="width:130px;height:130px;background:white;border-radius:6px;padding:6px;margin:0 auto 10px;display:block;">
        @else
        <div style="width:130px;height:130px;background:var(--bg3);border-radius:6px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:11px;">Non généré</div>
        @endif

        <div style="font-size:13px;font-weight:600;margin-bottom:4px;line-height:1.3;">{{ $qr->location_label ?? 'Sans étiquette' }}</div>
        <div style="font-size:11px;color:var(--muted);">{{ $qr->site->name ?? '' }}</div>
        @if($qr->building)
        <div style="font-size:11px;color:var(--muted);">{{ $qr->building->name }}</div>
        @endif
        @if($qr->floor)
        <div style="margin-top:6px;"><span class="badge badge-blue" style="font-size:10px;">{{ $qr->floor->label }}</span></div>
        @endif
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted);">
        Aucun QR code. <a href="{{ route('qrcodes.create') }}" style="color:var(--accent);">En créer un</a>
    </div>
    @endforelse
</div>

{{-- PAGE D'IMPRESSION (cachée à l'écran, visible seulement à l'impression) --}}
<div id="print-area" style="display:none;"></div>

@push('styles')
<style>
.qr-item.selected {
    border-color: var(--accent) !important;
    background: rgba(79,255,176,0.05) !important;
}
.qr-item.selected .select-indicator {
    background: var(--accent);
    border-color: var(--accent);
    color: #0d0f14;
}
.qr-item:hover { border-color: rgba(79,255,176,0.3) !important; }

/* ===== IMPRESSION ===== */
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { display: block !important; position: fixed; inset: 0; background: white; padding: 15mm; }

    .print-grid-2x3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 8mm; }
    .print-grid-1x1 { display: grid; grid-template-columns: 1fr; }
    .print-grid-4x4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 4mm; }

    .print-card {
        border: 1px solid #ddd; border-radius: 6px; padding: 8px;
        text-align: center; break-inside: avoid;
        font-family: Arial, sans-serif;
    }
    .print-card img { width: 100%; height: auto; max-width: 120px; display: block; margin: 0 auto 6px; }
    .print-card .plabel { font-size: 11px; font-weight: 700; color: #111; margin-bottom: 2px; }
    .print-card .psub   { font-size: 9px; color: #666; }
    .print-card .purl   { font-size: 8px; color: #999; margin-top: 4px; word-break: break-all; }
    .print-card .pbadge { display:inline-block; padding:1px 6px; border-radius:10px; font-size:8px; background:#e8ffe8; color:#0a6640; margin-top:3px; }
}
</style>
@endpush

@push('scripts')
<script>
let selectedIds = new Set();
const allData = @json($qrcodesJs);
let currentFormat = '2x3';

function toggleSelect(el) {
    const id = el.dataset.id;
    if (selectedIds.has(id)) {
        selectedIds.delete(id);
        el.classList.remove('selected');
        el.querySelector('.select-indicator').textContent = '';
    } else {
        selectedIds.add(id);
        el.classList.add('selected');
        el.querySelector('.select-indicator').textContent = '✓';
    }
    updateCount();
}

function selectAll() {
    document.querySelectorAll('.qr-item:not([style*="display: none"])').forEach(el => {
        selectedIds.add(el.dataset.id);
        el.classList.add('selected');
        el.querySelector('.select-indicator').textContent = '✓';
    });
    updateCount();
}

function selectNone() {
    selectedIds.clear();
    document.querySelectorAll('.qr-item').forEach(el => {
        el.classList.remove('selected');
        el.querySelector('.select-indicator').textContent = '';
    });
    updateCount();
}

function updateCount() {
    document.getElementById('selected-count').textContent = selectedIds.size + ' sélectionné(s)';
}

function filterQR() {
    const siteId = document.getElementById('filter-site').value;
    document.querySelectorAll('.qr-item').forEach(el => {
        if (!siteId || el.dataset.site == siteId) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
            selectedIds.delete(el.dataset.id);
            el.classList.remove('selected');
        }
    });
    updateCount();
}

function applyFormat(val) {
    currentFormat = val;
}

// Prépare la zone d'impression avant window.print()
document.getElementById('print-btn').addEventListener('click', function(e) {
    e.preventDefault();
    if (selectedIds.size === 0) { alert('Sélectionnez au moins un QR code.'); return; }

    const selected = allData.filter(d => selectedIds.has(String(d.id)));
    const gridClass = {
        '2x3': 'print-grid-2x3',
        '1x1': 'print-grid-1x1',
        '4x4': 'print-grid-4x4',
    }[currentFormat];

    const cards = selected.map(d => `
        <div class="print-card">
            ${d.image ? `<img src="${d.image}" alt="QR Code">` : '<div style="height:100px;background:#eee;margin-bottom:6px;border-radius:4px;"></div>'}
            <div class="plabel">${d.label || 'Sans étiquette'}</div>
            <div class="psub">${d.site || ''} ${d.building ? '· ' + d.building : ''}</div>
            ${d.floor ? `<div class="pbadge">${d.floor}</div>` : ''}
            <div class="purl">${d.url}</div>
        </div>
    `).join('');

    document.getElementById('print-area').innerHTML = `<div class="${gridClass}">${cards}</div>`;
    window.print();
});
</script>
@endpush
@endsection