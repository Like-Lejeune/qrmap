<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>{{ $qrcode->location_label }} — QRMap</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
        --bg:#0d0f14;--bg2:#13161e;--bg3:#1a1e28;--bg4:#222735;
        --border:rgba(255,255,255,.08);--border2:rgba(255,255,255,.14);
        --accent:#4fffb0;--accent-strong:#1fcf8a;--accent2:#4f9fff;--danger:#ff6f6f;
        --accent-soft:rgba(79,255,176,.10);--accent-soft2:rgba(79,255,176,.22);
        --text:#f0f2f5;--muted:#8892a4;
        --room:rgba(79,159,255,.10);--room-stroke:rgba(79,159,255,.40);
        --open:rgba(79,255,176,.07);--open-stroke:rgba(79,255,176,.30);
        --head:'Syne',sans-serif;--body:'DM Sans',sans-serif;color-scheme:dark;
    }
    :root[data-theme="light"]{
        --bg:#e9edf3;--bg2:#fff;--bg3:#eef1f6;--bg4:#e1e6ee;
        --border:rgba(15,23,42,.08);--border2:rgba(15,23,42,.16);
        --accent:#16a34a;--accent-strong:#15803d;--accent2:#2563eb;--danger:#dc2626;
        --accent-soft:rgba(22,163,74,.08);--accent-soft2:rgba(22,163,74,.18);
        --text:#0f172a;--muted:#64748b;
        --room:rgba(37,99,235,.08);--room-stroke:rgba(37,99,235,.4);
        --open:rgba(22,163,74,.07);--open-stroke:rgba(22,163,74,.4);
        color-scheme:light;
    }
    html,body{height:100%;overflow:hidden}
    body{background:var(--bg);color:var(--text);font-family:var(--body);font-size:15px;-webkit-tap-highlight-color:transparent}

    .map{position:fixed;inset:0;background:var(--bg2);touch-action:none;cursor:grab}
    .map:active{cursor:grabbing}
    .map svg{width:100%;height:100%;display:block}

    /* Barre de recherche flottante */
    .topbar{position:fixed;top:0;left:0;right:0;padding:14px 14px 0;z-index:20;pointer-events:none}
    .topbar>*{pointer-events:auto}
    .searchbar{display:flex;align-items:center;gap:10px;background:var(--bg2);border:1px solid var(--border);border-radius:15px;padding:12px 15px;box-shadow:0 6px 20px rgba(0,0,0,.28)}
    .searchbar svg{width:19px;height:19px;color:var(--muted);flex-shrink:0}
    .searchbar input{flex:1;background:none;border:none;outline:none;color:var(--text);font-family:var(--body);font-size:15px}
    .searchbar input::placeholder{color:var(--muted)}
    .searchbar .logo{font-family:var(--head);font-weight:800;font-size:13px;color:var(--accent)}

    /* Boutons flottants à droite */
    .fabs{position:fixed;right:14px;z-index:20;display:flex;flex-direction:column;gap:10px;top:78px}
    .fab{width:44px;height:44px;border-radius:13px;background:var(--bg2);border:1px solid var(--border);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.25)}
    .fab svg{width:20px;height:20px}
    .fab.act{color:var(--accent);border-color:var(--accent)}

    /* Sélecteur d'étage flottant */
    .floors{position:fixed;left:14px;top:78px;z-index:20;display:flex;flex-direction:column;gap:6px;background:var(--bg2);border:1px solid var(--border);border-radius:13px;padding:5px;box-shadow:0 4px 14px rgba(0,0,0,.25)}
    .floors button{min-width:40px;padding:8px 6px;border:none;background:none;color:var(--muted);font-family:var(--body);font-size:13px;font-weight:600;border-radius:9px;cursor:pointer}
    .floors button.on{background:var(--accent-soft);color:var(--accent)}

    /* Bandeau d'instruction (mode guidé) */
    .banner{position:fixed;top:74px;left:14px;right:14px;z-index:19;background:var(--accent);color:var(--bg);border-radius:14px;padding:13px 16px;display:none;align-items:center;gap:13px;box-shadow:0 6px 20px rgba(0,0,0,.3)}
    .banner.show{display:flex}
    .banner .bi{width:40px;height:40px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
    .banner .bi svg{width:30px;height:30px}
    .banner .bt{flex:1}
    .banner .bt .t{font-size:16px;font-weight:700;font-family:var(--head);line-height:1.1}
    .banner .bt .d{font-size:12px;opacity:.85;margin-top:2px}
    .banner .bx{cursor:pointer;opacity:.8}
    .banner .bx svg{width:20px;height:20px}

    /* Bottom sheet */
    .sheet{position:fixed;left:0;right:0;bottom:0;z-index:25;background:var(--bg2);border-top:1px solid var(--border);border-radius:22px 22px 0 0;box-shadow:0 -8px 30px rgba(0,0,0,.32);transform:translateY(0);transition:transform .28s cubic-bezier(.4,0,.2,1);max-height:82vh;display:flex;flex-direction:column}
    .grab{padding:10px 0 4px;display:flex;justify-content:center;cursor:pointer;flex-shrink:0}
    .grab span{width:42px;height:5px;background:var(--bg4);border-radius:3px}
    .sheet-body{padding:6px 18px 22px;overflow-y:auto}

    .sheet-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:6px 0 12px}
    .chips{display:flex;gap:9px;overflow-x:auto;padding-bottom:6px;margin-bottom:14px;scrollbar-width:none}
    .chips::-webkit-scrollbar{display:none}
    .chip{display:flex;align-items:center;gap:7px;padding:9px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:22px;white-space:nowrap;font-size:13px;cursor:pointer;color:var(--text)}
    .chip.on{background:var(--accent-soft);border-color:var(--accent);color:var(--accent-strong)}
    .chip svg{width:16px;height:16px}

    .dest{display:flex;align-items:center;gap:13px;padding:13px 4px;border-bottom:1px solid var(--border);cursor:pointer}
    .dest:last-child{border-bottom:none}
    .dest .di{width:40px;height:40px;border-radius:11px;background:var(--accent-soft);color:var(--accent2);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0}
    .dest .dt{flex:1;min-width:0}
    .dest .dt .l{font-size:15px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .dest .dt .m{font-size:12px;color:var(--muted)}
    .dest .ch{color:var(--muted);font-size:18px}
    .empty{text-align:center;color:var(--muted);font-size:14px;padding:2rem 1rem}

    /* Fiche itinéraire */
    .route-head{display:flex;align-items:center;gap:13px;margin-bottom:16px}
    .route-head .di{width:46px;height:46px;border-radius:13px;background:var(--accent-soft);color:var(--accent2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
    .route-head .rt{flex:1;min-width:0}
    .route-head .rt .l{font-size:17px;font-weight:700}
    .route-head .rt .m{font-size:12px;color:var(--muted)}
    .route-head .eta{text-align:right;flex-shrink:0}
    .route-head .eta .big{font-size:19px;font-weight:700;color:var(--accent-strong)}
    .route-head .eta .sub{font-size:11px;color:var(--muted)}
    .route-meta{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
    .route-meta .pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);background:var(--bg3);border:1px solid var(--border);border-radius:20px;padding:6px 12px}
    .route-meta .pill svg{width:14px;height:14px}
    .route-actions{display:flex;gap:10px}
    .route-actions button{padding:14px;border-radius:13px;font-size:15px;font-weight:600;font-family:var(--body);cursor:pointer;border:none}
    .route-actions .sec{flex:1;background:var(--bg3);border:1px solid var(--border);color:var(--text)}
    .route-actions .pri{flex:2;background:var(--accent);color:var(--bg);display:inline-flex;align-items:center;justify-content:center;gap:7px}
    .route-actions .pri svg{width:18px;height:18px}
    .floor-jump{margin-top:12px;display:none;align-items:center;gap:10px;background:var(--accent-soft);border:1px solid var(--accent-soft2);border-radius:12px;padding:11px 14px;font-size:13px;cursor:pointer}
    .floor-jump.show{display:flex}
    .floor-jump svg{width:18px;height:18px;color:var(--accent-strong)}

    .tbtn{width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:var(--bg3);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0}
    .tbtn svg{width:17px;height:17px}
    .moon{display:block}.sun{display:none}
    :root[data-theme="light"] .moon{display:none}:root[data-theme="light"] .sun{display:block}

    .toast{position:fixed;bottom:30vh;left:50%;transform:translateX(-50%) translateY(20px);background:var(--bg4);border:1px solid var(--border2);color:var(--text);padding:10px 16px;border-radius:9px;font-size:13px;opacity:0;pointer-events:none;transition:all .25s;z-index:60}
    .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
</style>
</head>
<body>

<div class="map" id="map">
    <svg id="svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 700" preserveAspectRatio="xMidYMid slice">
        <g id="viewport"></g>
    </svg>
</div>

<div class="topbar">
    <div class="searchbar">
        <span class="logo">QRMap</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
        <input id="q" placeholder="Rechercher un service…" autocomplete="off" onfocus="openSheet('list')">
        <button class="tbtn" onclick="toggleTheme()" aria-label="Thème">
            <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/></svg>
        </button>
    </div>
</div>

<div class="banner" id="banner">
    <div class="bi" id="bannerIc"></div>
    <div class="bt"><div class="t" id="bannerT">—</div><div class="d" id="bannerD"></div></div>
    <div class="bx" onclick="stopGuide()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
</div>

<div class="floors" id="floors"></div>

<div class="fabs">
    <button class="fab" onclick="zoomBy(1.3)" aria-label="Zoom +"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></button>
    <button class="fab" onclick="zoomBy(0.77)" aria-label="Zoom -"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg></button>
    <button class="fab" onclick="recenter()" aria-label="Recentrer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg></button>
</div>

<div class="sheet" id="sheet">
    <div class="grab" onclick="toggleSheet()"><span></span></div>
    <div class="sheet-body" id="sheetBody"></div>
</div>

<div class="toast" id="toast"></div>

<script>
const QR_UUID='{{ $qrcode->uuid }}';
const QR_POS={x:{{ $qrcode->pos_x ?? 'null' }},y:{{ $qrcode->pos_y ?? 'null' }}};
const CUR_FLOOR_ID={{ $currentFloor ? $currentFloor->id : 'null' }};
const BW={{ $building && $building->model_3d ? ($building->model_3d['width_m'] ?? 40) : 40 }};
const BD={{ $building && $building->model_3d ? ($building->model_3d['depth_m'] ?? 25) : 25 }};
const floorsById=@json($allFloors->keyBy('id'));
const floorOrder=@json($allFloors->sortBy('level')->pluck('id')->values());

// ===== Thème =====
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);localStorage.setItem('qrmap-theme',t);drawMap();}
function toggleTheme(){const c=document.documentElement.getAttribute('data-theme')||'dark';applyTheme(c==='dark'?'light':'dark');}
(function(){const s=localStorage.getItem('qrmap-theme');document.documentElement.setAttribute('data-theme',s|| ((window.matchMedia&&window.matchMedia('(prefers-color-scheme: light)').matches)?'light':'dark'));})();
function toast(m){const t=document.getElementById('toast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),1700);}
function css(v){return getComputedStyle(document.documentElement).getPropertyValue(v).trim();}

// ===== Coordonnées carte (viewBox 1000x700) =====
const MX=60,MY=60,MW=880,MH=580;
const sx=p=>MX+(p/100)*MW, sy=p=>MY+(p/100)*MH;

// ===== Pan / zoom =====
let view={tx:0,ty:0,s:1};
const vp=document.getElementById('viewport');
function applyView(){vp.setAttribute('transform',`translate(${view.tx} ${view.ty}) scale(${view.s})`);}
function zoomBy(f){zoomAt(500,350,f);}
function zoomAt(cx,cy,f){
    const ns=Math.max(0.6,Math.min(4,view.s*f));
    const k=ns/view.s;
    view.tx=cx-(cx-view.tx)*k;
    view.ty=cy-(cy-view.ty)*k;
    view.s=ns;applyView();
}
function recenter(){view={tx:0,ty:0,s:1};applyView();}

const map=document.getElementById('map');
let drag=null,pinch=null;
function ptInSvg(clientX,clientY){
    const svg=document.getElementById('svg');const r=svg.getBoundingClientRect();
    // viewBox slice : on approxime l'échelle px->unités
    const scale=Math.max(1000/r.width,700/r.height);
    return {x:(clientX-r.left)*scale,y:(clientY-r.top)*scale};
}
map.addEventListener('pointerdown',e=>{drag={x:e.clientX,y:e.clientY,tx:view.tx,ty:view.ty};map.setPointerCapture(e.pointerId);});
map.addEventListener('pointermove',e=>{
    if(!drag||pinch)return;
    const svg=document.getElementById('svg');const r=svg.getBoundingClientRect();
    const scale=Math.max(1000/r.width,700/r.height);
    view.tx=drag.tx+(e.clientX-drag.x)*scale;
    view.ty=drag.ty+(e.clientY-drag.y)*scale;
    applyView();
});
map.addEventListener('pointerup',()=>{drag=null;});
map.addEventListener('wheel',e=>{e.preventDefault();const p=ptInSvg(e.clientX,e.clientY);zoomAt(p.x,p.y,e.deltaY<0?1.12:0.89);},{passive:false});
// pinch
map.addEventListener('touchstart',e=>{if(e.touches.length===2){const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);pinch={d,s:view.s};}},{passive:false});
map.addEventListener('touchmove',e=>{if(pinch&&e.touches.length===2){e.preventDefault();const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);const f=d/pinch.d;view.s=Math.max(0.6,Math.min(4,pinch.s*f));applyView();}},{passive:false});
map.addEventListener('touchend',e=>{if(e.touches.length<2)pinch=null;});

// ===== État =====
let groups=[],activeCat=null;
let shownFloorId=CUR_FLOOR_ID||(floorOrder[0]||null);
let segments=[],destLabel='',targetFloorId=null;
let guide=null;

// ===== Sélecteur d'étage =====
function renderFloorSwitcher(){
    const box=document.getElementById('floors');box.innerHTML='';
    floorOrder.slice().reverse().forEach(fid=>{
        const f=floorsById[fid];if(!f)return;
        const b=document.createElement('button');
        b.className=(String(fid)===String(shownFloorId)?'on':'');
        b.textContent=f.label?shortLabel(f.label):('N'+f.level);
        b.onclick=()=>{shownFloorId=fid;renderFloorSwitcher();drawMap();};
        box.appendChild(b);
    });
}
function shortLabel(l){return l.length>6?('N'+(floorsById[shownFloorId]?floorsById[shownFloorId].level:'')):l;}

// ===== Rendu carte =====
function drawMap(){
    const f=floorsById[shownFloorId];
    const rooms=f?(f.rooms||[]):[];
    const AC=css('--accent'),AC2=css('--accent2'),RM=css('--room'),RMS=css('--room-stroke'),OP=css('--open'),OPS=css('--open-stroke'),MUT=css('--muted'),BG=css('--bg'),BG2=css('--bg2'),BD=css('--border');
    let h='';
    // grille
    h+=`<defs><pattern id="gr" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M50 0 L0 0 0 50" fill="none" stroke="${BD}" stroke-width="1"/></pattern></defs>`;
    h+=`<rect x="-500" y="-500" width="2000" height="1700" fill="url(#gr)"/>`;
    h+=`<rect x="${MX}" y="${MY}" width="${MW}" height="${MH}" rx="10" fill="${BG2}" stroke="${BD}" stroke-width="2"/>`;
    rooms.forEach(r=>{
        const n=(r.name||'').toLowerCase();
        const open=['couloir','hall','accueil','sortie','attente','circulation','palier','allée','allee','galerie','escalier','ascenseur','lobby'].some(k=>n.includes(k));
        const fill=open?OP:RM,st=open?OPS:RMS;
        const x=sx(r.x),y=sy(r.y),w=(r.w/100)*MW,hh=(r.h/100)*MH;
        if(r.shape==='circle')h+=`<ellipse cx="${x+w/2}" cy="${y+hh/2}" rx="${w/2}" ry="${hh/2}" fill="${fill}" stroke="${st}" stroke-width="1.5"/>`;
        else if(r.shape==='poly'&&Array.isArray(r.points)&&r.points.length>=3){const pts=r.points.map(p=>`${sx(p[0])},${sy(p[1])}`).join(' ');h+=`<polygon points="${pts}" fill="${fill}" stroke="${st}" stroke-width="1.5"/>`;}
        else h+=`<rect x="${x}" y="${y}" width="${w}" height="${hh}" rx="6" fill="${fill}" stroke="${st}" stroke-width="1.5"/>`;
        if(r.name)h+=`<text x="${x+w/2}" y="${y+hh/2}" fill="${MUT}" font-size="15" font-family="${css('--head')}" font-weight="600" text-anchor="middle" dominant-baseline="middle">${escapeXml(r.name)}</text>`;
    });
    // tracé de l'étage affiché
    const seg=segments.find(s=>String(s.floor_id)===String(shownFloorId));
    if(seg&&seg.path&&seg.path.length>=2){
        const pts=seg.path.map(p=>({x:+p.x,y:+p.y}));
        const dd='M '+pts.map(p=>`${sx(p.x)} ${sy(p.y)}`).join(' L ');
        h+=`<path d="${dd}" fill="none" stroke="${AC}" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" opacity="0.2"/>`;
        h+=`<path id="routeLine" d="${dd}" fill="none" stroke="${AC}" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>`;
        const a=pts[0],b=pts[pts.length-1];
        h+=`<g transform="translate(${sx(a.x)} ${sy(a.y)})"><circle r="13" fill="${BG}" stroke="${AC2}" stroke-width="3.5"/><text y="4" text-anchor="middle" fill="${AC2}" font-size="11" font-weight="700" font-family="${css('--head')}">QR</text></g>`;
        if(seg.role==='vers_connecteur')h+=`<g transform="translate(${sx(b.x)} ${sy(b.y)})"><rect x="-15" y="-15" width="30" height="30" rx="7" fill="${AC2}"/><text y="2" text-anchor="middle" fill="${BG}" font-size="17" font-weight="700">↕</text></g>`;
        else h+=`<g transform="translate(${sx(b.x)} ${sy(b.y)})"><circle r="15" fill="${AC}"/><circle r="5.5" fill="${BG}"/></g>`;
        h+=`<g id="walker" transform="translate(${sx(a.x)} ${sy(a.y)})"><circle r="11" fill="${BG}" stroke="${AC}" stroke-width="4"/><circle r="4" fill="${AC}"/></g>`;
    } else if(String(shownFloorId)===String(CUR_FLOOR_ID)&&QR_POS.x!==null){
        h+=`<g transform="translate(${sx(QR_POS.x)} ${sy(QR_POS.y)})"><circle r="16" fill="${AC}" opacity="0.18"><animate attributeName="r" values="13;22;13" dur="2s" repeatCount="indefinite"/></circle><circle r="13" fill="${BG}" stroke="${AC2}" stroke-width="3.5"/><text y="4" text-anchor="middle" fill="${AC2}" font-size="11" font-weight="700" font-family="${css('--head')}">QR</text></g>`;
    }
    vp.innerHTML=h;
}
function escapeXml(s){return String(s).replace(/[<>&'"]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;',"'":'&apos;','"':'&quot;'}[c]));}

// ===== Bottom sheet =====
let sheetMode='list',sheetExpanded=false;
function openSheet(mode){sheetMode=mode;renderSheet();const sh=document.getElementById('sheet');sh.style.transform='translateY(0)';}
function toggleSheet(){sheetExpanded=!sheetExpanded;const sh=document.getElementById('sheet');const body=document.getElementById('sheetBody');sh.style.maxHeight=sheetExpanded?'82vh':'46vh';}
function collapseSheet(){const sh=document.getElementById('sheet');sh.style.transform='translateY(calc(100% - 92px))';}

function renderSheet(){
    const body=document.getElementById('sheetBody');
    if(sheetMode==='route'){renderRouteCard(body);return;}
    // mode liste : recherche + catégories + destinations
    const present=new Set();groups.forEach(g=>g.destinations.forEach(d=>present.add(d.category)));
    const CATS=[['medical','Médical'],['exit','Sortie'],['service','Services'],['admin','Admin'],['shop','Boutiques']];
    let chips='';CATS.filter(c=>present.has(c[0])).forEach(c=>{chips+=`<div class="chip${activeCat===c[0]?' on':''}" onclick="toggleCat('${c[0]}')">${c[1]}</div>`;});
    let html=`<div class="sheet-title">Où voulez-vous aller&nbsp;?</div><div class="chips">${chips}</div><div id="destItems"></div>`;
    body.innerHTML=html;
    renderDestItems();
}
function toggleCat(c){activeCat=activeCat===c?null:c;renderSheet();}
function renderDestItems(){
    const q=(document.getElementById('q').value||'').toLowerCase().trim();
    const box=document.getElementById('destItems');if(!box)return;box.innerHTML='';let total=0;
    groups.forEach(g=>{
        let items=g.destinations.filter(d=>(!activeCat||d.category===activeCat)&&(!q||(d.label||'').toLowerCase().includes(q)||(d.description||'').toLowerCase().includes(q)));
        if(!items.length)return;
        const h=document.createElement('div');h.className='sheet-title';h.style.marginTop='14px';h.textContent=g.floor_label;box.appendChild(h);
        items.forEach(d=>{total++;const el=document.createElement('div');el.className='dest';
            el.innerHTML=`<div class="di">${d.icon??'📍'}</div><div class="dt"><div class="l">${d.label}</div><div class="m">${d.same_floor?'Cet étage':g.floor_label}${d.description?' · '+d.description:''}</div></div><div class="ch">›</div>`;
            el.onclick=()=>selectDestination(d.id,d.label,g.floor_label);box.appendChild(el);});
    });
    if(!total)box.innerHTML='<div class="empty">Aucune destination ne correspond.</div>';
}
document.getElementById('q').addEventListener('input',()=>{if(sheetMode==='list')renderDestItems();});

// ===== Sélection destination -> tracé + fiche =====
async function selectDestination(destId,label,floorLabel){
    destLabel=label;
    try{
        const res=await fetch('/api/v1/navigate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({uuid:QR_UUID,destination_id:destId})});
        const data=await res.json();
        segments=data.segments||[];
        targetFloorId=data.target_floor?data.target_floor.id:(data.current_floor?data.current_floor.id:shownFloorId);
        routeData=data;
        // afficher l'étage de départ (où est le QR)
        shownFloorId=segments.length?segments[0].floor_id:shownFloorId;
        renderFloorSwitcher();drawMap();fitToRoute();
        sheetMode='route';sheetExpanded=false;
        document.getElementById('sheet').style.maxHeight='auto';
        renderSheet();
        document.getElementById('sheet').style.transform='translateY(0)';
    }catch(e){toast('Erreur de calcul');}
}
let routeData=null;

function totalMeters(){
    let m=0;segments.forEach(s=>{const p=(s.path||[]).map(q=>({x:+q.x,y:+q.y}));for(let i=1;i<p.length;i++){const dx=(p[i].x-p[i-1].x)/100*BW,dy=(p[i].y-p[i-1].y)/100*BD;m+=Math.hypot(dx,dy);}});return m;}

function renderRouteCard(body){
    const m=Math.round(totalMeters());
    const mins=Math.max(1,Math.round(m/70)); // ~70 m/min à pied
    const multi=routeData&&routeData.type==='multi_floor';
    const cat=findCategory(destLabel);
    body.innerHTML=`
        <div class="route-head">
            <div class="di">${iconForCat(cat)}</div>
            <div class="rt"><div class="l">${destLabel}</div><div class="m">${routeData&&routeData.target_floor?(routeData.target_floor.label||('Niveau '+routeData.target_floor.level)):''}</div></div>
            <div class="eta"><div class="big">${mins} min</div><div class="sub">~${m} m</div></div>
        </div>
        <div class="route-meta">
            <span class="pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 4v16M17 8l-4-4-4 4"/></svg> ~${m} m</span>
            <span class="pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg> ~${mins} min</span>
            ${multi?'<span class="pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 8l3-3 3 3"/></svg> Changement d\'étage</span>':''}
        </div>
        <div class="route-actions">
            <button class="sec" onclick="cancelRoute()">Annuler</button>
            <button class="pri" onclick="startGuide()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Démarrer</button>
        </div>
        ${multi?`<div class="floor-jump show" onclick="jumpToTargetFloor()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg> Voir l'arrivée — ${routeData.target_floor?(routeData.target_floor.label||('niveau '+routeData.target_floor.level)):'étage cible'}</div>`:''}
    `;
}
function findCategory(label){let c='service';groups.forEach(g=>g.destinations.forEach(d=>{if(d.label===label)c=d.category;}));return c;}
function iconForCat(c){const ic={medical:'M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.7 0-3 .9-4.5 2.5C10.5 3.9 9.2 3 7.5 3A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z',exit:'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',shop:'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z',admin:'M3 21h18M5 21V7l8-4v18'}[c]||'M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z';return `<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="${ic}"/></svg>`;}

function cancelRoute(){segments=[];routeData=null;sheetMode='list';renderSheet();drawMap();recenter();}
function jumpToTargetFloor(){if(targetFloorId){shownFloorId=targetFloorId;renderFloorSwitcher();drawMap();fitToRoute();}}

// ===== Cadrage sur le tracé =====
function fitToRoute(){
    const seg=segments.find(s=>String(s.floor_id)===String(shownFloorId));
    if(!seg||!seg.path||!seg.path.length){recenter();return;}
    const xs=seg.path.map(p=>sx(+p.x)),ys=seg.path.map(p=>sy(+p.y));
    const minX=Math.min(...xs),maxX=Math.max(...xs),minY=Math.min(...ys),maxY=Math.max(...ys);
    const cx=(minX+maxX)/2,cy=(minY+maxY)/2;
    const w=Math.max(maxX-minX,120),hh=Math.max(maxY-minY,120);
    const s=Math.max(0.8,Math.min(2.2,Math.min(900/w,600/hh)));
    view.s=s;view.tx=500-cx*s;view.ty=350-cy*s;applyView();
}

// ===== Mode guidé : animation + bandeau =====
function startGuide(){
    collapseSheet();
    const seg=segments.find(s=>String(s.floor_id)===String(shownFloorId))||segments[0];
    if(!seg){return;}
    document.getElementById('banner').classList.add('show');
    document.getElementById('bannerIc').innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    document.getElementById('bannerT').textContent='Suivez le tracé';
    document.getElementById('bannerD').textContent=routeData&&routeData.instruction?routeData.instruction:('Vers '+destLabel);
    animateWalker(seg);
}
function stopGuide(){document.getElementById('banner').classList.remove('show');if(guide){cancelAnimationFrame(guide);guide=null;}drawMap();document.getElementById('sheet').style.transform='translateY(0)';}
function animateWalker(seg){
    drawMap();
    const line=document.getElementById('routeLine');const walker=document.getElementById('walker');
    if(!line||!walker)return;
    const pts=seg.path.map(p=>[sx(+p.x),sy(+p.y)]);
    let segLens=[],total=0;for(let i=1;i<pts.length;i++){const l=Math.hypot(pts[i][0]-pts[i-1][0],pts[i][1]-pts[i-1][1]);segLens.push(l);total+=l;}
    line.style.strokeDasharray=total;line.style.strokeDashoffset=total;
    const dur=2600,t0=performance.now();
    function frame(t){
        const pr=Math.min(1,(t-t0)/dur);
        line.style.strokeDashoffset=total*(1-pr);
        let rem=total*pr,pos=pts[pts.length-1];
        for(let i=0;i<segLens.length;i++){if(rem<=segLens[i]){const k=segLens[i]?rem/segLens[i]:0;pos=[pts[i][0]+(pts[i+1][0]-pts[i][0])*k,pts[i][1]+(pts[i+1][1]-pts[i][1])*k];break;}rem-=segLens[i];}
        walker.setAttribute('transform',`translate(${pos[0]} ${pos[1]})`);
        if(pr<1)guide=requestAnimationFrame(frame);
        else if(routeData&&routeData.type==='multi_floor'&&String(shownFloorId)!==String(targetFloorId)){
            document.getElementById('bannerIc').innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 8l3-3 3 3M9 16l3 3 3-3"/></svg>';
            document.getElementById('bannerT').textContent='Prenez l\'ascenseur';
            document.getElementById('bannerD').textContent='Touchez pour voir l\'étage d\'arrivée';
            document.getElementById('banner').onclick=function(ev){if(ev.target.closest('.bx'))return;jumpToTargetFloor();setTimeout(()=>{const s2=segments.find(s=>String(s.floor_id)===String(shownFloorId));if(s2){document.getElementById('banner').onclick=null;animateWalker(s2);document.getElementById('bannerIc').innerHTML='<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.4\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M12 19V5M5 12l7-7 7 7\"/></svg>';document.getElementById('bannerT').textContent='Vous y êtes presque';document.getElementById('bannerD').textContent=destLabel+' est proche';}},350);};
        }else{
            document.getElementById('bannerIc').innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22V4M4 4l13 0-3 5 3 5H4"/></svg>';
            document.getElementById('bannerT').textContent='Vous êtes arrivé';
            document.getElementById('bannerD').textContent=destLabel;
        }
    }
    guide=requestAnimationFrame(frame);
}

// ===== Chargement destinations =====
async function loadDestinations(){
    try{
        const res=await fetch(`/api/v1/scan/${QR_UUID}/destinations`);
        const data=await res.json();groups=data.destinations||[];
    }catch(e){groups=[];}
    renderSheet();
}

// ===== Init =====
renderFloorSwitcher();drawMap();applyView();
collapseSheet();
loadDestinations();
</script>
</body>
</html>