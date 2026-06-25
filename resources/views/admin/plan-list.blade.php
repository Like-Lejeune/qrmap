@extends('layouts.app')
@section('title', 'Éditeur de plans — QRMap')

@push('styles')
<style>
    .plan-stats{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 14px}
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:200;padding:20px}
    .overlay.show{display:flex}
    .modal{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);width:100%;max-width:560px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px var(--shadow)}
    .modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--border)}
    .modal-head h3{font-family:var(--font-head);font-size:1.05rem;font-weight:700}
    .modal-x{background:none;border:none;color:var(--muted);cursor:pointer;font-size:20px;line-height:1;padding:2px 6px}
    .modal-x:hover{color:var(--danger)}
    .modal-body{padding:16px 18px}
    .modal-foot{padding:13px 18px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:flex-end}
    .mhint{font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px}
    .dropzone{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:26px;border:1.5px dashed var(--border);border-radius:var(--radius);cursor:pointer;color:var(--muted);text-align:center;transition:all .15s}
    .dropzone:hover,.dropzone.drag{border-color:var(--accent);color:var(--accent);background:rgba(79,255,176,.05)}
    .dropzone svg{width:26px;height:26px}
    .or{text-align:center;color:var(--muted);font-size:12px;margin:14px 0}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Éditeur de <span>plans</span></h1>
        <p class="page-sub">Dessinez les plans, placez les QR et marquez les destinations</p>
    </div>
    <div class="btn-actions">
        <button class="btn btn-outline" id="importPlanBtn">↑ Importer un plan</button>
        <a href="{{ $createUrl }}" class="btn btn-primary">+ Nouveau plan</a>
    </div>
</div>

@if($sites->isEmpty())
<div class="card" style="text-align:center;padding:3rem;">
    <div style="font-size:2rem;margin-bottom:1rem;">🗺</div>
    <div style="color:var(--muted);margin-bottom:1rem;">Aucun plan pour l'instant. Dessinez votre premier plan visuellement.</div>
    <a href="{{ $createUrl }}" class="btn btn-primary">Créer un plan</a>
</div>
@else
<div class="card-grid">
    @foreach($sites as $site)
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
            <span class="badge badge-green">{{ $site['qrcodes'] }} QR</span>
        </div>

        <div class="btn-actions">
            <a href="{{ $site['edit_url'] }}" class="btn btn-primary btn-sm">Modifier le plan</a>
            <a href="{{ route('paths.editor', $site['id']) }}" class="btn btn-outline btn-sm">Tracés</a>
            <button class="btn btn-outline btn-sm" data-duplicate="{{ $site['duplicate_url'] }}">Dupliquer</button>
            <button class="btn btn-danger btn-sm" data-destroy="{{ $site['destroy_url'] }}" data-name="{{ $site['name'] }}">Supprimer</button>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Modale d'import --}}
<div class="overlay" id="importOverlay">
    <div class="modal">
        <div class="modal-head">
            <h3>Importer un plan</h3>
            <button class="modal-x" id="importClose">✕</button>
        </div>
        <div class="modal-body">
            <p class="mhint">Déposez le fichier <strong>.json</strong> exporté depuis l'éditeur, ou collez son contenu.</p>

            <div class="form-group">
                <label>Destination de l'import</label>
                <select id="targetSelect">
                    <option value="">➕ Créer un nouveau site</option>
                    @foreach($siteOptions as $opt)
                    <option value="{{ $opt['id'] }}">↻ Mettre à jour : {{ $opt['name'] }}</option>
                    @endforeach
                </select>
                <p class="mhint" id="targetHint" style="margin:8px 0 0;"></p>
            </div>

            <label class="dropzone" id="dropzone">
                <input type="file" id="fileInput" accept=".json,application/json" hidden>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                <span id="dropLabel">Glissez un fichier .json ici, ou cliquez pour parcourir</span>
            </label>

            <div class="or">— ou —</div>
            <div class="form-group" style="margin-bottom:0;">
                <textarea id="pasteInput" placeholder="Collez ici le JSON exporté…" style="min-height:110px;font-family:monospace;font-size:12px;"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-outline" id="importCancel">Annuler</button>
            <button class="btn btn-primary" id="importSubmit">Importer</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrf=document.querySelector('meta[name="csrf-token"]').content;
    const importUrl="{{ $importUrl }}";
    const overlay=document.getElementById('importOverlay');
    const fileInput=document.getElementById('fileInput');
    const dropzone=document.getElementById('dropzone');
    const dropLabel=document.getElementById('dropLabel');
    const pasteInput=document.getElementById('pasteInput');
    const targetSelect=document.getElementById('targetSelect');
    const targetHint=document.getElementById('targetHint');
    let pickedFile=null;

    function updateTargetHint(){
        targetHint.innerHTML=targetSelect.value
            ? '⚠️ Le plan importé <strong>remplacera</strong> les étages et destinations de ce site. Les QR déjà imprimés (mêmes UUID) sont conservés, les nouveaux générés.'
            : 'Un nouveau site sera créé à partir du plan.';
    }
    targetSelect.addEventListener('change',updateTargetHint);updateTargetHint();

    document.getElementById('importPlanBtn').onclick=()=>overlay.classList.add('show');
    function closeImport(){overlay.classList.remove('show');pickedFile=null;fileInput.value='';pasteInput.value='';targetSelect.value='';updateTargetHint();dropLabel.textContent='Glissez un fichier .json ici, ou cliquez pour parcourir';}
    document.getElementById('importClose').onclick=document.getElementById('importCancel').onclick=closeImport;

    fileInput.onchange=()=>{if(fileInput.files[0]){pickedFile=fileInput.files[0];dropLabel.textContent=pickedFile.name;}};
    ['dragover','dragenter'].forEach(ev=>dropzone.addEventListener(ev,e=>{e.preventDefault();dropzone.classList.add('drag');}));
    ['dragleave','drop'].forEach(ev=>dropzone.addEventListener(ev,e=>{e.preventDefault();dropzone.classList.remove('drag');}));
    dropzone.addEventListener('drop',e=>{const f=e.dataTransfer.files[0];if(f){pickedFile=f;dropLabel.textContent=f.name;}});

    document.getElementById('importSubmit').onclick=async()=>{
        const btn=document.getElementById('importSubmit');
        const fd=new FormData();
        if(pickedFile)fd.append('file',pickedFile);
        else if(pasteInput.value.trim())fd.append('json',pasteInput.value.trim());
        else{alert('Choisissez un fichier ou collez du JSON');return;}
        if(targetSelect.value)fd.append('target_site_id',targetSelect.value);
        btn.disabled=true;const prev=btn.textContent;btn.textContent='Import…';
        try{
            const res=await fetch(importUrl,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});
            const data=await res.json().catch(()=>({}));
            if(!res.ok)throw new Error(data.message||('Erreur '+res.status));
            window.location=data.edit_url;
        }catch(e){alert(e.message);btn.disabled=false;btn.textContent=prev;}
    };

    // Dupliquer
    document.querySelectorAll('[data-duplicate]').forEach(btn=>{
        btn.onclick=async()=>{
            btn.disabled=true;
            try{
                const res=await fetch(btn.dataset.duplicate,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
                if(!res.ok)throw new Error('Erreur '+res.status);
                const data=await res.json();
                window.location=data.edit_url;
            }catch(e){alert('Échec de la duplication');btn.disabled=false;}
        };
    });

    // Supprimer
    document.querySelectorAll('[data-destroy]').forEach(btn=>{
        btn.onclick=async()=>{
            const name=btn.dataset.name||'ce site';
            if(!confirm(`Supprimer « ${name} » et tout son plan ? Cette action est irréversible.`))return;
            btn.disabled=true;
            try{
                const res=await fetch(btn.dataset.destroy,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
                if(!res.ok)throw new Error('Erreur '+res.status);
                const card=document.getElementById('site-card-'+btn.dataset.destroy.split('/').filter(Boolean).pop());
                const wrap=btn.closest('.card');
                (wrap||card).style.transition='opacity .25s,transform .25s';
                (wrap||card).style.opacity='0';(wrap||card).style.transform='scale(.96)';
                setTimeout(()=>(wrap||card).remove(),250);
            }catch(e){alert('Échec de la suppression');btn.disabled=false;}
        };
    });
</script>
@endpush