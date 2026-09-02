<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>QRMap — Éditeur de tracé</title>
<script>
    window.__PATHS__ = {
        origins:   @json($origins),
        dests:     @json($dests),
        computeUrl:"{{ route('paths.compute') }}",
        storeUrl:  "{{ route('paths.store') }}",
        destroyUrl:"{{ route('paths.destroy') }}",
        backUrl:   "{{ route('plan-editor.index') }}",
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --bg:#0d0f14;--bg2:#13161e;--bg3:#1a1e28;--bg4:#222735;
        --border:rgba(255,255,255,.08);--border2:rgba(255,255,255,.14);
        --accent:#4fffb0;--accent2:#4f9fff;--warn:#ffb84f;--danger:#ff6f6f;
        --text:#f0f2f5;--muted:#8892a4;--soft:rgba(79,255,176,.12);
        --head:'Syne',sans-serif;--body:'DM Sans',sans-serif;--mono:'DM Mono',monospace;
        --grid:rgba(255,255,255,.05);
        --room:rgba(79,159,255,.10);--room-stroke:rgba(79,159,255,.4);
        --open:rgba(79,255,176,.07);--open-stroke:rgba(79,255,176,.35);
        color-scheme:dark;
    }
    :root[data-theme="light"]{
        --bg:#f3f5f9;--bg2:#ffffff;--bg3:#eef1f6;--bg4:#e2e7ef;
        --border:rgba(20,28,45,.10);--border2:rgba(20,28,45,.18);
        --accent:#0aab6e;--accent2:#2f7fff;--warn:#c77b00;--danger:#d64545;
        --text:#141c2d;--muted:#5b6577;--soft:rgba(10,171,110,.10);
        --grid:rgba(20,28,45,.06);
        --room:rgba(47,127,255,.10);--room-stroke:rgba(47,127,255,.40);
        --open:rgba(10,171,110,.08);--open-stroke:rgba(10,171,110,.35);
        color-scheme:light;
    }
    /* bascule des icônes lune/soleil */
    .moon{display:block}.sun{display:none}
    :root[data-theme="light"] .moon{display:none}
    :root[data-theme="light"] .sun{display:block}
    .tbtn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--bg3);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0}
    .tbtn:hover{border-color:var(--border2)}
    .tbtn svg{width:17px;height:17px}
    body{background:var(--bg);color:var(--text);font-family:var(--body);height:100vh;overflow:hidden;font-size:14px}
    .app{display:grid;grid-template-columns:300px 1fr;grid-template-rows:56px 1fr;height:100vh}
    .topbar{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;padding:0 18px;background:var(--bg2);border-bottom:1px solid var(--border)}
    .brand{font-family:var(--head);font-weight:800;font-size:16px;color:var(--accent);display:flex;align-items:center;gap:9px}
    .brand::before{content:'';width:9px;height:9px;background:var(--accent);border-radius:2px;transform:rotate(45deg)}
    .brand small{font-family:var(--body);font-weight:500;color:var(--muted);font-size:12px}
    .top-actions{display:flex;gap:8px;align-items:center}
    .panel{background:var(--bg2);border-right:1px solid var(--border);padding:16px;overflow-y:auto}
    .stage{background:var(--bg);display:flex;flex-direction:column;overflow:hidden}
    .sec{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin:18px 0 9px}
    .sec:first-child{margin-top:0}
    label.f{display:block;font-size:11px;color:var(--muted);margin:9px 0 4px;font-weight:500}
    select.f{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:7px;color:var(--text);padding:8px 10px;font-size:13px;outline:none}
    select.f:focus{border-color:var(--accent)}
    button{font-family:var(--body);cursor:pointer;border:none;border-radius:7px;font-size:13px;font-weight:600;transition:all .15s}
    .btn{padding:9px 12px;background:var(--bg3);color:var(--text);border:1px solid var(--border);width:100%;margin-top:8px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px}
    .btn:hover{border-color:var(--border2)}
    .btn.accent{background:var(--accent);color:var(--bg);border-color:var(--accent)}
    .btn.accent:hover{filter:brightness(1.08)}
    .btn.ghost{background:transparent}
    .btn.danger{color:var(--danger);border-color:transparent;background:transparent}
    .btn.danger:hover{background:rgba(255,111,111,.1)}
    .btn[disabled]{opacity:.5;cursor:not-allowed}
    .row{display:flex;gap:8px}.row .btn{margin-top:0}
    .chk{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text);cursor:pointer;user-select:none;margin-top:12px}
    .chk input{accent-color:var(--accent);width:16px;height:16px}
    .help{font-size:11.5px;color:var(--muted);line-height:1.6;margin-top:10px}
    .help b{color:var(--text);font-weight:600}
    .help kbd{font-family:var(--mono);font-size:10px;background:var(--bg3);border:1px solid var(--border);border-radius:4px;padding:1px 5px}
    .stage-bar{height:44px;display:flex;align-items:center;gap:12px;padding:0 16px;border-bottom:1px solid var(--border);background:var(--bg2);flex-shrink:0;font-size:12px;color:var(--muted)}
    .pill{font-family:var(--mono);font-size:11px;color:var(--accent);background:var(--soft);border-radius:20px;padding:3px 10px}
    .canvas-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:22px;overflow:hidden}
    svg.editor{background:var(--bg2);border:1px solid var(--border);border-radius:10px;width:100%;max-width:940px;height:auto;touch-action:none;box-shadow:0 18px 50px -24px rgba(0,0,0,.6)}
    .room .body{stroke-width:1.4}
    .room.salle .body{fill:var(--room);stroke:var(--room-stroke)}
    .room.open .body{fill:var(--open);stroke:var(--open-stroke);stroke-dasharray:4 3}
    .room .lbl{font-family:var(--head);font-weight:600;fill:var(--muted);text-anchor:middle;dominant-baseline:middle;pointer-events:none;font-size:11px}
    .path-halo{fill:none;stroke:rgba(79,255,176,.25);stroke-width:7;stroke-linecap:round;stroke-linejoin:round;pointer-events:none}
    .path-line{fill:none;stroke:var(--accent);stroke-width:3.5;stroke-linecap:round;stroke-linejoin:round;pointer-events:none}
    .seg-hit{stroke:transparent;stroke-width:16;fill:none;cursor:copy}
    .anchor{fill:var(--bg);stroke:var(--accent);stroke-width:2.5;cursor:grab}
    .anchor.sel{fill:var(--accent)}
    .endpoint{stroke-width:3}
    .ep-start{fill:var(--accent2)}.ep-end{fill:var(--accent)}
    .ep-conn rect{fill:var(--accent2)}
    .ep-label{font-family:var(--mono);font-size:8px;fill:var(--bg);text-anchor:middle;dominant-baseline:central;font-weight:700;pointer-events:none}
    .add-dot{fill:var(--accent);opacity:0;cursor:copy;transition:opacity .1s}
    .seg-group:hover .add-dot{opacity:.9}
    /* sélecteur de tronçon */
    .legs{display:flex;flex-direction:column;gap:6px}
    .leg{display:flex;align-items:center;gap:9px;padding:9px 11px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);cursor:pointer;font-size:12.5px}
    .leg.on{background:var(--soft);border-color:var(--accent)}
    .leg .ln{width:22px;height:22px;border-radius:6px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;color:var(--muted);flex-shrink:0}
    .leg.on .ln{color:var(--accent)}
    .leg .lt{flex:1;line-height:1.3}
    .leg .lt small{color:var(--muted);font-size:11px}
    .leg .edited{width:7px;height:7px;border-radius:50%;background:var(--warn);flex-shrink:0}
    .toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--bg4);border:1px solid var(--border2);color:var(--text);padding:10px 16px;border-radius:9px;font-size:13px;opacity:0;pointer-events:none;transition:all .25s;z-index:60}
    .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
    .stat{font-family:var(--mono);font-size:11px;color:var(--muted)}
    ::-webkit-scrollbar{width:10px}::-webkit-scrollbar-thumb{background:var(--bg4);border-radius:6px;border:2px solid var(--bg2)}
</style>
</head>
<body>
<div class="app">
    <div class="topbar">
        <div class="brand">QRMap <small>Éditeur de tracé</small></div>
        <div class="top-actions">
            <button class="tbtn" onclick="toggleTheme()" aria-label="Basculer le thème" title="Thème clair / sombre" style="margin-right:4px">
                <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/></svg>
            </button>
            <a class="btn" href="{{ route('plan-editor.index') }}" style="width:auto;margin:0;padding:9px 14px">← Plans</a>
            <button class="btn accent" id="saveBtn" style="width:auto;margin:0;padding:9px 18px" disabled>Enregistrer le tracé</button>
        </div>
    </div>

    <div class="panel">
        <div class="sec">Trajet</div>
        <label class="f">Origine (QR)</label>
        <select class="f" id="originSel"></select>
        <label class="f">Destination</label>
        <select class="f" id="destSel"></select>
        <button class="btn accent" id="computeBtn">↻ Calculer le chemin (A*)</button>

        <div id="legsBlock" style="display:none">
            <div class="sec">Tronçons (multi-étage)</div>
            <div class="legs" id="legsList"></div>
        </div>

        <div class="sec">Édition du tronçon</div>
        <label class="chk"><input type="checkbox" id="smoothChk"> Lisser la courbe</label>
        <div class="row" style="margin-top:10px">
            <button class="btn ghost" id="resetBtn">Réinitialiser (A*)</button>
            <button class="btn ghost" id="clearSelBtn">Désélectionner</button>
        </div>
        <button class="btn danger" id="deleteBtn" style="margin-top:8px">Supprimer le tracé enregistré</button>

        <div class="help">
            <b>Déplacer</b> : glisse un point (libre). La <b>destination ★</b> est déplaçable, le <b>QR</b> et l'<b>ascenseur ↕</b> sont fixes.<br>
            <b>Ajouter</b> : survole un segment, ou double-clic sur la ligne.<br>
            <b>Supprimer</b> : sélectionne + <kbd>Suppr</kbd>, ou clic droit.
        </div>

        <div class="sec">Infos</div>
        <div class="stat" id="stat">— points</div>
    </div>

    <div class="stage">
        <div class="stage-bar">
            <span class="pill" id="statusPill">Prêt</span>
            <span id="hint">Choisis un trajet puis « Calculer le chemin ».</span>
        </div>
        <div class="canvas-wrap">
            <svg class="editor" id="svg" viewBox="0 0 900 600"></svg>
        </div>
    </div>
</div>
<div class="toast" id="toast"></div>

<script>
// ===== Thème clair / sombre =====
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);try{localStorage.setItem('qrmap-theme',t);}catch(e){}}
function toggleTheme(){const c=document.documentElement.getAttribute('data-theme')||'dark';applyTheme(c==='dark'?'light':'dark');if(typeof render==='function'){try{render();}catch(e){}}}
(function(){let t='dark';try{t=localStorage.getItem('qrmap-theme')||'dark';}catch(e){}applyTheme(t);})();

const VW=900,VH=600;
const pxX=p=>(p/100)*VW, pxY=p=>(p/100)*VH;
const pcX=x=>(x/VW)*100, pcY=y=>(y/VH)*100;
const $=s=>document.querySelector(s);
const NS='http://www.w3.org/2000/svg';
const CFG=window.__PATHS__;
const CSRF=document.querySelector('meta[name="csrf-token"]').content;
function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),1900);}

const OPEN=['couloir','hall','accueil','sortie','entrée','entree','escalier','ascenseur','attente','circulation','palier','galerie','lobby','allée','allee'];
const isOpen=r=>OPEN.some(k=>(r.name||'').toLowerCase().includes(k));

// ===== État =====
let allSegments=[];   // [{floor_id,floor_label,role,rooms,from,to,waypoints,smooth,editable_end}]
let segIdx=0;         // tronçon en cours d'édition
let rooms=[];         // pièces du tronçon courant
let anchors=[];       // [[x,y],...] en %
let sel=-1, existsInDb=false, isMulti=false;
let origin=null, dest=null;
const svg=$('#svg');

function curSeg(){return allSegments[segIdx]||null;}
function smoothCur(){const s=curSeg();return s?!!s.smooth:false;}

// ---- Sélecteurs trajet ----
function fillSelects(){
    $('#originSel').innerHTML = CFG.origins.map(q=>`<option value="${q.id}">${q.label} (N${q.floor_level})</option>`).join('');
    $('#destSel').innerHTML   = CFG.dests.map(d=>`<option value="${d.id}">${d.label} (N${d.floor_level})</option>`).join('');
    origin = CFG.origins[0]; dest = CFG.dests[0];
}
$('#originSel').onchange=e=>{origin=CFG.origins.find(q=>String(q.id)===e.target.value);};
$('#destSel').onchange=e=>{dest=CFG.dests.find(d=>String(d.id)===e.target.value);};

const norm = wp => (wp||[]).map(p => Array.isArray(p) ? [p[0],p[1]] : [p.x,p.y]);

// ---- Compute ----
$('#computeBtn').onclick=async()=>{
    if(!origin||!dest){toast('Choisis une origine et une destination');return;}
    $('#statusPill').textContent='Calcul…';
    try{
        const url=`${CFG.computeUrl}?qr_code_id=${origin.id}&destination_id=${dest.id}`;
        const res=await fetch(url,{headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
        if(!res.ok)throw new Error('HTTP '+res.status);
        const data=await res.json();
        isMulti=!!data.multi;
        existsInDb=!!data.exists;
        // normaliser chaque tronçon
        allSegments=(data.segments||[]).map(s=>{
            const wp=norm(s.waypoints);
            const from=Array.isArray(s.from)?s.from:[s.from.x,s.from.y];
            const to=Array.isArray(s.to)?s.to:[s.to.x,s.to.y];
            if(wp.length>=2){wp[0]=from; wp[wp.length-1]=to;}
            return {...s, waypoints:wp.map(p=>[+p[0].toFixed(1),+p[1].toFixed(1)]), _edited:false, _raw:wp.map(p=>[...p])};
        });
        segIdx=0;
        buildLegs();
        loadSegment(0);
        $('#statusPill').textContent = existsInDb ? 'Tracé enregistré chargé' : (isMulti?'Calculé — 2 tronçons':'Calculé (A*)');
        $('#hint').textContent = isMulti ? 'Édite chaque tronçon, puis enregistre.' : 'Déplace/ajoute/supprime des points, puis enregistre.';
        $('#saveBtn').disabled=false;
    }catch(e){$('#statusPill').textContent='Erreur';toast('Échec du calcul : '+e.message);}
};

// ---- Sélecteur de tronçons ----
function buildLegs(){
    const block=$('#legsBlock'),list=$('#legsList');
    if(allSegments.length<=1){block.style.display='none';return;}
    block.style.display='block';
    list.innerHTML='';
    allSegments.forEach((s,i)=>{
        const roleTxt = s.role==='vers_connecteur' ? '→ ascenseur/escalier'
                      : s.role==='depuis_connecteur' ? 'ascenseur → destination'
                      : '→ destination';
        const el=document.createElement('div');
        el.className='leg'+(i===segIdx?' on':'');
        el.innerHTML=`<div class="ln">${i+1}</div><div class="lt">${s.floor_label||('Niveau '+i)}<br><small>${roleTxt}</small></div>${s._edited?'<div class="edited" title="modifié"></div>':''}`;
        el.onclick=()=>{saveAnchorsToSeg();segIdx=i;buildLegs();loadSegment(i);};
        list.appendChild(el);
    });
}

// ---- Charger un tronçon dans l'éditeur ----
function loadSegment(i){
    const s=allSegments[i];if(!s)return;
    rooms=s.rooms||[];
    anchors=s.waypoints.map(p=>[...p]);
    $('#smoothChk').checked=!!s.smooth;
    sel=-1; render();
}
function saveAnchorsToSeg(){
    const s=curSeg();if(!s)return;
    s.waypoints=anchors.map(p=>[...p]);
}

// ---- Rendu ----
function add(tag,attrs,parent){const e=document.createElementNS(NS,tag);for(const k in attrs)e.setAttribute(k,attrs[k]);(parent||svg).appendChild(e);return e;}
function roomBody(r,parent){
    const x=pxX(r.x),y=pxY(r.y),w=pxX(r.w),h=pxY(r.h);
    if(r.shape==='circle')return add('ellipse',{class:'body',cx:x+w/2,cy:y+h/2,rx:w/2,ry:h/2},parent);
    if(r.shape==='poly'&&Array.isArray(r.points)&&r.points.length>=3){
        const pts=r.points.map(([px,py])=>`${pxX(px)},${pxY(py)}`).join(' ');
        return add('polygon',{class:'body',points:pts},parent);
    }
    return add('rect',{class:'body',x,y,width:w,height:h,rx:5},parent);
}
function smoothPathD(pts){
    if(pts.length<3) return 'M '+pts.map(p=>`${pxX(p[0])} ${pxY(p[1])}`).join(' L ');
    const P=pts.map(p=>[pxX(p[0]),pxY(p[1])]);
    let d=`M ${P[0][0]} ${P[0][1]}`;
    for(let i=0;i<P.length-1;i++){
        const p0=P[i-1]||P[i],p1=P[i],p2=P[i+1],p3=P[i+2]||P[i+1];
        const c1x=p1[0]+(p2[0]-p0[0])/6,c1y=p1[1]+(p2[1]-p0[1])/6;
        const c2x=p2[0]-(p3[0]-p1[0])/6,c2y=p2[1]-(p3[1]-p1[1])/6;
        d+=` C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2[0]} ${p2[1]}`;
    }
    return d;
}
const straightD=pts=>'M '+pts.map(p=>`${pxX(p[0])} ${pxY(p[1])}`).join(' L ');

function render(){
    while(svg.firstChild)svg.removeChild(svg.firstChild);
    const defs=add('defs',{});
    defs.innerHTML=`<pattern id="g" width="${VW/20}" height="${VH/20}" patternUnits="userSpaceOnUse"><path d="M ${VW/20} 0 L 0 0 0 ${VH/20}" fill="none" stroke="var(--grid)" stroke-width="1"/></pattern>`;
    add('rect',{x:0,y:0,width:VW,height:VH,fill:'url(#g)'});
    add('rect',{x:1,y:1,width:VW-2,height:VH-2,rx:8,fill:'none',stroke:'var(--border)','stroke-width':1});

    rooms.forEach(r=>{
        const g=add('g',{class:`room ${isOpen(r)?'open':'salle'}`});
        roomBody(r,g);
        if(r.name)add('text',{class:'lbl',x:pxX(r.x+r.w/2),y:pxY(r.y+r.h/2)},g).textContent=r.name;
    });

    if(anchors.length){
        const smooth=smoothCur();
        const dStr=smooth?smoothPathD(anchors):straightD(anchors);
        add('path',{class:'path-halo',d:dStr});
        add('path',{class:'path-line',d:dStr});
        for(let i=0;i<anchors.length-1;i++){
            const a=anchors[i],b=anchors[i+1];
            const mx=(a[0]+b[0])/2,my=(a[1]+b[1])/2;
            const g=add('g',{class:'seg-group'});
            add('line',{class:'seg-hit',x1:pxX(a[0]),y1:pxY(a[1]),x2:pxX(b[0]),y2:pxY(b[1])},g);
            const dot=add('circle',{class:'add-dot',cx:pxX(mx),cy:pxY(my),r:5},g);
            dot.addEventListener('pointerdown',e=>{e.stopPropagation();insertPoint(i+1,[mx,my]);});
        }
        anchors.forEach((p,i)=>{
            if(i===0||i===anchors.length-1)return;
            const c=add('circle',{class:`anchor ${i===sel?'sel':''}`,cx:pxX(p[0]),cy:pxY(p[1]),r:7});
            c.addEventListener('pointerdown',e=>onAnchorDown(e,i));
            c.addEventListener('contextmenu',e=>{e.preventDefault();deletePoint(i);});
        });
        drawEndpoints();
    }
    const s=curSeg();
    $('#stat').textContent = anchors.length ? `${anchors.length} points · ${smoothCur()?'lissé':'droit'}${existsInDb?' · enregistré':''}${s&&s.editable_end?' · ★ déplaçable':''}` : '— points';
}

function drawEndpoints(){
    const s=curSeg();if(!s)return;
    const a=anchors[0], b=anchors[anchors.length-1];
    const lastIdx=anchors.length-1;
    // DÉPART : QR (1er tronçon) ou ascenseur (tronçons suivants) — toujours fixe
    let g=add('g',{});
    if(segIdx===0){
        add('circle',{class:'endpoint ep-start',cx:pxX(a[0]),cy:pxY(a[1]),r:11,stroke:'var(--bg2)'},g);
        add('text',{class:'ep-label',x:pxX(a[0]),y:pxY(a[1])},g).textContent='QR';
    }else{
        const gg=add('g',{class:'ep-conn'},g);
        add('rect',{x:pxX(a[0])-11,y:pxY(a[1])-11,width:22,height:22,rx:5,stroke:'var(--bg2)','stroke-width':2},gg);
        add('text',{class:'ep-label',x:pxX(a[0]),y:pxY(a[1])},gg).textContent='↕';
    }
    // ARRIVÉE : destination ★ (déplaçable) ou ascenseur ↕ (fixe)
    g=add('g',{});
    if(s.editable_end){
        const cEnd=add('circle',{class:'endpoint ep-end',cx:pxX(b[0]),cy:pxY(b[1]),r:11,stroke:'var(--bg2)',style:'cursor:grab'},g);
        add('text',{class:'ep-label',x:pxX(b[0]),y:pxY(b[1])},g).textContent='★';
        cEnd.addEventListener('pointerdown',ev=>onAnchorDown(ev,lastIdx));
    }else{
        const gg=add('g',{class:'ep-conn'},g);
        add('rect',{x:pxX(b[0])-11,y:pxY(b[1])-11,width:22,height:22,rx:5,stroke:'var(--bg2)','stroke-width':2},gg);
        add('text',{class:'ep-label',x:pxX(b[0]),y:pxY(b[1])},gg).textContent='↕';
    }
}

// ---- Interactions ----
let drag=null;
function svgPt(e){const r=svg.getBoundingClientRect();return [pcX((e.clientX-r.left)/r.width*VW), pcY((e.clientY-r.top)/r.height*VH)];}
function markEdited(){const s=curSeg();if(s)s._edited=true;}
function onAnchorDown(e,i){e.stopPropagation();sel=i;drag={i};svg.setPointerCapture(e.pointerId);render();}
svg.addEventListener('pointermove',e=>{
    if(!drag)return;
    const [x,y]=svgPt(e);
    anchors[drag.i]=[Math.max(0,Math.min(100,+x.toFixed(1))),Math.max(0,Math.min(100,+y.toFixed(1)))];
    markEdited();saveAnchorsToSeg();render();
});
svg.addEventListener('pointerup',()=>{drag=null;buildLegs();});
svg.addEventListener('dblclick',e=>{
    if(!anchors.length)return;
    const [x,y]=svgPt(e); let best=-1,bd=Infinity;
    for(let i=0;i<anchors.length-1;i++){const d=distToSeg([x,y],anchors[i],anchors[i+1]);if(d<bd){bd=d;best=i;}}
    if(best>=0&&bd<4) insertPoint(best+1,[x,y]);
});
function distToSeg(p,a,b){const dx=b[0]-a[0],dy=b[1]-a[1],l=dx*dx+dy*dy;if(!l)return Math.hypot(p[0]-a[0],p[1]-a[1]);let t=((p[0]-a[0])*dx+(p[1]-a[1])*dy)/l;t=Math.max(0,Math.min(1,t));return Math.hypot(p[0]-(a[0]+t*dx),p[1]-(a[1]+t*dy));}
function insertPoint(idx,pt){anchors.splice(idx,0,[+pt[0].toFixed(1),+pt[1].toFixed(1)]);sel=idx;markEdited();saveAnchorsToSeg();render();buildLegs();}
function deletePoint(i){if(i<=0||i>=anchors.length-1)return;anchors.splice(i,1);sel=-1;markEdited();saveAnchorsToSeg();render();buildLegs();}
document.addEventListener('keydown',e=>{
    if(/input|textarea|select/i.test(e.target.tagName))return;
    if((e.key==='Delete'||e.key==='Backspace')&&sel>0&&sel<anchors.length-1)deletePoint(sel);
});

$('#resetBtn').onclick=()=>{const s=curSeg();if(s&&s._raw){anchors=s._raw.map(p=>[...p]);s.waypoints=anchors.map(p=>[...p]);s._edited=false;sel=-1;render();buildLegs();toast('Tronçon réinitialisé sur l\'A*');}};
$('#clearSelBtn').onclick=()=>{sel=-1;render();};
$('#smoothChk').onchange=e=>{const s=curSeg();if(s){s.smooth=e.target.checked;markEdited();render();buildLegs();}};

// ---- Save (tous les tronçons) ----
$('#saveBtn').onclick=async()=>{
    saveAnchorsToSeg();
    if(!allSegments.length||allSegments.some(s=>s.waypoints.length<2)){toast('Calcule d\'abord un chemin');return;}
    const btn=$('#saveBtn');btn.disabled=true;const prev=btn.textContent;btn.textContent='Enregistrement…';
    try{
        const res=await fetch(CFG.storeUrl,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({
                qr_code_id:origin.id, destination_id:dest.id,
                segments: allSegments.map(s=>({
                    floor_id:s.floor_id, role:s.role, smooth:!!s.smooth,
                    waypoints:s.waypoints.map(([x,y])=>({x,y})),
                })),
            }),
        });
        if(!res.ok)throw new Error('HTTP '+res.status);
        existsInDb=true; allSegments.forEach(s=>s._edited=false); buildLegs(); render();
        toast('Tracé enregistré ✓'); $('#statusPill').textContent='Tracé enregistré';
    }catch(e){toast('Échec : '+e.message);}
    finally{btn.disabled=false;btn.textContent=prev;}
};

// ---- Delete override ----
$('#deleteBtn').onclick=async()=>{
    if(!origin||!dest)return;
    if(!confirm('Supprimer le tracé enregistré ? La navigation reprendra l\'A* automatique.'))return;
    try{
        const res=await fetch(CFG.destroyUrl,{
            method:'DELETE',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({qr_code_id:origin.id,destination_id:dest.id}),
        });
        if(!res.ok)throw new Error('HTTP '+res.status);
        existsInDb=false; toast('Tracé supprimé — A* automatique rétabli');
        $('#computeBtn').click();
    }catch(e){toast('Échec : '+e.message);}
};

fillSelects(); render();
</script>
</body>
</html>