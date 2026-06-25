<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
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
        --bg:#f3f5f9;--bg2:#fff;--bg3:#eef1f6;--bg4:#e7ebf2;
        --border:rgba(15,23,42,.08);--border2:rgba(15,23,42,.16);
        --accent:#16a34a;--accent-strong:#15803d;--accent2:#2563eb;--danger:#dc2626;
        --accent-soft:rgba(22,163,74,.08);--accent-soft2:rgba(22,163,74,.18);
        --text:#0f172a;--muted:#64748b;
        --room:rgba(37,99,235,.08);--room-stroke:rgba(37,99,235,.4);
        --open:rgba(22,163,74,.07);--open-stroke:rgba(22,163,74,.4);
        color-scheme:light;
    }
    body{background:var(--bg);color:var(--text);font-family:var(--body);min-height:100vh;font-size:15px;-webkit-tap-highlight-color:transparent}
    .wrap{max-width:520px;margin:0 auto;min-height:100vh;display:flex;flex-direction:column}

    /* Header */
    .hd{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);background:var(--bg2)}
    .logo{font-family:var(--head);font-weight:800;font-size:15px;color:var(--accent);display:flex;align-items:center;gap:8px}
    .logo::before{content:'';width:8px;height:8px;background:var(--accent);border-radius:2px;transform:rotate(45deg)}
    .tbtn{width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:var(--bg3);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer}
    .tbtn svg{width:17px;height:17px}
    .moon{display:block}.sun{display:none}
    :root[data-theme="light"] .moon{display:none}:root[data-theme="light"] .sun{display:block}

    /* ===== A : ACCUEIL (destination d'abord) ===== */
    .home{flex:1;display:flex;flex-direction:column;padding:20px 18px}
    .here{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--muted);margin-bottom:6px}
    .here .dot{width:9px;height:9px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 4px var(--accent-soft);flex-shrink:0}
    .h1{font-family:var(--head);font-size:1.55rem;font-weight:700;line-height:1.15;margin-bottom:18px}
    .search{display:flex;align-items:center;gap:10px;background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:13px 15px;margin-bottom:18px}
    .search:focus-within{border-color:var(--accent)}
    .search svg{width:19px;height:19px;color:var(--muted);flex-shrink:0}
    .search input{flex:1;background:none;border:none;outline:none;color:var(--text);font-family:var(--body);font-size:15px}
    .search input::placeholder{color:var(--muted)}
    .chips{display:flex;gap:10px;overflow-x:auto;padding-bottom:6px;margin-bottom:22px;scrollbar-width:none}
    .chips::-webkit-scrollbar{display:none}
    .chip{display:flex;flex-direction:column;align-items:center;gap:6px;min-width:64px;cursor:pointer;background:none;border:none}
    .chip .ci{width:50px;height:50px;border-radius:15px;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;transition:all .15s}
    .chip.on .ci{background:var(--accent-soft);border-color:var(--accent)}
    .chip .ci svg{width:22px;height:22px;color:var(--accent)}
    .chip span{font-size:11px;color:var(--muted)}
    .chip.on span{color:var(--accent-strong)}
    .grp{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:8px 0 10px}
    .dest{display:flex;align-items:center;gap:13px;padding:13px 15px;background:var(--bg2);border:1px solid var(--border);border-radius:14px;margin-bottom:9px;cursor:pointer;transition:all .15s}
    .dest:active{transform:scale(.98)}
    .dest:hover{border-color:var(--accent-soft2)}
    .dest .di{width:40px;height:40px;border-radius:11px;background:var(--accent-soft);color:var(--accent2);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0}
    .dest .dt{flex:1;min-width:0}
    .dest .dt .l{font-size:15px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .dest .dt .m{font-size:12px;color:var(--muted)}
    .dest .ch{color:var(--muted);font-size:18px;flex-shrink:0}
    .empty{text-align:center;color:var(--muted);font-size:14px;padding:2.5rem 1rem}

    /* ===== B : NAVIGATION (turn-by-turn) ===== */
    .nav{flex:1;display:none;flex-direction:column}
    .nav.show{display:flex}
    .navtop{padding:14px 18px 0}
    .navtop-row{display:flex;align-items:center;gap:10px;margin-bottom:14px}
    .navtop-row .x{width:34px;height:34px;border-radius:10px;border:1px solid var(--border);background:var(--bg3);color:var(--text);display:flex;align-items:center;justify-content:center;cursor:pointer}
    .navtop-row .x svg{width:17px;height:17px}
    .navtop-row .to{flex:1;text-align:center;font-size:14px;color:var(--muted)}
    .navtop-row .to b{color:var(--text);font-weight:600}
    .prog{display:flex;gap:5px;margin-bottom:16px}
    .prog i{flex:1;height:4px;border-radius:2px;background:var(--bg3)}
    .prog i.done{background:var(--accent)}

    .instr{margin:0 18px;background:var(--accent-soft);border:1px solid var(--accent-soft2);border-radius:18px;padding:20px 18px;display:flex;align-items:center;gap:16px}
    .instr .ic{width:62px;height:62px;border-radius:17px;background:var(--accent);color:var(--bg);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .instr .ic svg{width:34px;height:34px}
    .instr .tx .t{font-size:1.25rem;font-weight:600;line-height:1.15;font-family:var(--head)}
    .instr .tx .d{font-size:13px;color:var(--muted);margin-top:4px;line-height:1.4}
    .instr .tx .dist{font-size:13px;color:var(--accent-strong);margin-top:7px;display:inline-flex;align-items:center;gap:5px;font-weight:600}

    .planbox{margin:16px 18px 0;border-radius:14px;overflow:hidden;border:1px solid var(--border);background:var(--bg3)}
    .planbox svg{width:100%;height:auto;display:block}
    .floorbadge{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:12px;color:var(--muted);border-top:1px solid var(--border)}
    .floorbadge b{color:var(--accent-strong)}

    .navctl{display:flex;gap:10px;padding:16px 18px;margin-top:auto}
    .navctl button{padding:14px;border-radius:13px;font-size:15px;font-weight:600;font-family:var(--body);cursor:pointer;border:none}
    .navctl .prev{flex:1;background:var(--bg3);border:1px solid var(--border);color:var(--text)}
    .navctl .next{flex:2;background:var(--accent);color:var(--bg)}
    .navctl .next:disabled{opacity:.5}

    .steps{padding:0 18px 18px}
    .steps summary{font-size:13px;color:var(--muted);cursor:pointer;padding:8px 0;list-style:none;display:flex;align-items:center;gap:6px}
    .steps summary::-webkit-details-marker{display:none}
    .steprow{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid var(--border);font-size:14px}
    .steprow .n{width:26px;height:26px;border-radius:8px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--muted);flex-shrink:0}
    .steprow.cur .n{background:var(--accent);color:var(--bg)}
    .steprow.cur{color:var(--text);font-weight:600}
    .steprow.done{color:var(--muted)}

    .toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--bg4);border:1px solid var(--border2);color:var(--text);padding:10px 16px;border-radius:9px;font-size:13px;opacity:0;pointer-events:none;transition:all .25s;z-index:60}
    .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
</style>
</head>
<body>
<div class="wrap">
    <div class="hd">
        <div class="logo">QRMap</div>
        <button class="tbtn" onclick="toggleTheme()" aria-label="Thème">
            <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/></svg>
        </button>
    </div>

    {{-- ===== A : ÉCRAN D'ACCUEIL ===== --}}
    <div class="home" id="home">
        <div class="here"><span class="dot"></span> Vous êtes à : <b style="color:var(--text)">{{ $qrcode->location_label }}</b></div>
        <div class="h1">Où voulez-vous aller&nbsp;?</div>

        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
            <input id="q" placeholder="Rechercher un service…" autocomplete="off">
        </div>

        <div class="chips" id="chips"></div>

        <div id="destList"><div class="empty">Chargement des destinations…</div></div>
    </div>

    {{-- ===== B : ÉCRAN DE NAVIGATION ===== --}}
    <div class="nav" id="nav">
        <div class="navtop">
            <div class="navtop-row">
                <button class="x" onclick="exitNav()" aria-label="Quitter"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                <div class="to">Vers <b id="navDest">—</b></div>
                <div style="width:34px"></div>
            </div>
            <div class="prog" id="prog"></div>
        </div>

        <div class="instr">
            <div class="ic" id="instrIc"></div>
            <div class="tx">
                <div class="t" id="instrT">—</div>
                <div class="d" id="instrD"></div>
                <div class="dist" id="instrDist" style="display:none"></div>
            </div>
        </div>

        <div class="planbox">
            <svg id="navSvg" viewBox="0 0 600 380" xmlns="http://www.w3.org/2000/svg"></svg>
            <div class="floorbadge"><span id="floorName">—</span><span id="stepCount"></span></div>
        </div>

        <details class="steps">
            <summary id="stepsSummary">▸ Toutes les étapes</summary>
            <div id="stepsList"></div>
        </details>

        <div class="navctl">
            <button class="prev" id="btnPrev" onclick="prevStep()">Précédent</button>
            <button class="next" id="btnNext" onclick="nextStep()">Étape suivante</button>
        </div>
    </div>
</div>
<div class="toast" id="toast"></div>

<script>
// ===== Contexte serveur =====
const QR_UUID = '{{ $qrcode->uuid }}';
const QR_POS  = { x: {{ $qrcode->pos_x ?? 'null' }}, y: {{ $qrcode->pos_y ?? 'null' }} };
const BW = {{ $building && $building->model_3d ? ($building->model_3d['width_m'] ?? 40) : 40 }};
const BD = {{ $building && $building->model_3d ? ($building->model_3d['depth_m'] ?? 25) : 25 }};
const floorsById = @json($allFloors->keyBy('id'));

// ===== Thème =====
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);localStorage.setItem('qrmap-theme',t);}
function toggleTheme(){const c=document.documentElement.getAttribute('data-theme')||'dark';applyTheme(c==='dark'?'light':'dark');if(curSteps.length)drawStep();}
(function(){const s=localStorage.getItem('qrmap-theme');if(s){applyTheme(s);return;}const l=window.matchMedia&&window.matchMedia('(prefers-color-scheme: light)').matches;applyTheme(l?'light':'dark');})();

function toast(m){const t=document.getElementById('toast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),1800);}

// ===== Catégories (raccourcis) =====
const CATS=[
    {key:'medical',ic:'M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.7 0-3 .9-4.5 2.5C10.5 3.9 9.2 3 7.5 3A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z',label:'Médical'},
    {key:'exit',ic:'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',label:'Sortie'},
    {key:'service',ic:'M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z',label:'Services'},
    {key:'admin',ic:'M3 21h18M5 21V7l8-4v18M19 21V11l-6-4',label:'Admin'},
    {key:'shop',ic:'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4ZM3 6h18M16 10a4 4 0 0 1-8 0',label:'Boutiques'},
];

// ===== État =====
let groups=[];        // destinations groupées (réponse serveur)
let activeCat=null;
let curSteps=[];      // étapes turn-by-turn
let curIdx=0;
let curDestLabel='';

// ===== Chargement destinations (écran A) =====
async function loadDestinations(){
    try{
        const res=await fetch(`/api/v1/scan/${QR_UUID}/destinations`);
        const data=await res.json();
        groups=data.destinations||[];
        renderChips();
        renderDestList();
    }catch(e){
        document.getElementById('destList').innerHTML='<div class="empty">Impossible de charger les destinations.</div>';
    }
}
function renderChips(){
    const present=new Set();
    groups.forEach(g=>g.destinations.forEach(d=>present.add(d.category)));
    const box=document.getElementById('chips');box.innerHTML='';
    CATS.filter(c=>present.has(c.key)).forEach(c=>{
        const b=document.createElement('button');
        b.className='chip'+(activeCat===c.key?' on':'');
        b.innerHTML=`<span class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="${c.ic}"/></svg></span><span>${c.label}</span>`;
        b.onclick=()=>{activeCat=activeCat===c.key?null:c.key;renderChips();renderDestList();};
        box.appendChild(b);
    });
}
function renderDestList(){
    const q=(document.getElementById('q').value||'').toLowerCase().trim();
    const box=document.getElementById('destList');box.innerHTML='';
    let total=0;
    groups.forEach(g=>{
        let items=g.destinations.filter(d=>{
            if(activeCat&&d.category!==activeCat)return false;
            if(q&&!((d.label||'').toLowerCase().includes(q)||(d.description||'').toLowerCase().includes(q)))return false;
            return true;
        });
        if(!items.length)return;
        const h=document.createElement('div');h.className='grp';h.textContent=g.floor_label;box.appendChild(h);
        items.forEach(d=>{
            total++;
            const el=document.createElement('div');el.className='dest';
            el.innerHTML=`<div class="di">${d.icon??'📍'}</div>
                <div class="dt"><div class="l">${d.label}</div><div class="m">${d.same_floor?'Cet étage':g.floor_label}${d.description?' · '+d.description:''}</div></div>
                <div class="ch">›</div>`;
            el.onclick=()=>startNavigation(d.id,d.label);
            box.appendChild(el);
        });
    });
    if(!total)box.innerHTML='<div class="empty">Aucune destination ne correspond.</div>';
}
document.getElementById('q').addEventListener('input',renderDestList);

// ===== Lancement navigation (A -> B) =====
async function startNavigation(destId,label){
    curDestLabel=label;
    try{
        const res=await fetch('/api/v1/navigate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({uuid:QR_UUID,destination_id:destId})});
        const data=await res.json();
        curSteps=buildSteps(data);
        curIdx=0;
        document.getElementById('navDest').textContent=label;
        document.getElementById('home').style.display='none';
        document.getElementById('nav').classList.add('show');
        drawStep();
    }catch(e){toast('Erreur de calcul d\'itinéraire');}
}
function exitNav(){
    document.getElementById('nav').classList.remove('show');
    document.getElementById('home').style.display='flex';
    curSteps=[];curIdx=0;
}

// ===== Génération des étapes turn-by-turn depuis les segments =====
function metersBetween(a,b){const dx=(b.x-a.x)/100*BW,dy=(b.y-a.y)/100*BD;return Math.hypot(dx,dy);}
function turnAngle(a,b,c){
    const v1x=b.x-a.x,v1y=b.y-a.y,v2x=c.x-b.x,v2y=c.y-b.y;
    const cross=v1x*v2y-v1y*v2x,dot=v1x*v2x+v1y*v2y;
    return Math.atan2(cross,dot)*180/Math.PI; // >0 = à droite (repère y vers le bas)
}
function roundDist(m){return Math.max(5,Math.round(m/5)*5);}

function buildSteps(data){
    const segs=data.segments||[];
    const steps=[];
    segs.forEach((seg,si)=>{
        const pts=(seg.path||[]).map(p=>({x:+p.x,y:+p.y}));
        const fid=seg.floor_id;
        if(pts.length>=2){
            let accum=0,startPt=pts[0];
            for(let i=1;i<pts.length;i++){
                accum+=metersBetween(pts[i-1],pts[i]);
                const next=pts[i+1];
                if(next){
                    const ang=turnAngle(pts[i-1],pts[i],next);
                    if(Math.abs(ang)>25){
                        steps.push({type:'straight',dist:accum,floorId:fid,at:pts[i],from:startPt,path:pts});
                        steps.push({type:ang>0?'right':'left',floorId:fid,at:pts[i],from:pts[i],path:pts});
                        accum=0;startPt=pts[i];
                    }
                }
            }
            if(accum>1){
                steps.push({type:'straight',dist:accum,floorId:fid,at:pts[pts.length-1],from:startPt,path:pts,lastOfSeg:true});
            }
        }
        if(seg.role==='vers_connecteur'){
            const cname=(seg.connector&&seg.connector.name)?seg.connector.name:'ascenseur / escalier';
            steps.push({type:'connector',label:cname,floorId:fid,at:pts[pts.length-1]||null,path:pts,target:data.target_floor});
        }
    });
    steps.push({type:'arrive',label:curDestLabel,floorId:(segs[segs.length-1]||{}).floor_id,at:lastPoint(segs),path:lastPath(segs)});
    return steps;
}
function lastPoint(segs){const s=segs[segs.length-1];if(!s||!s.path||!s.path.length)return null;const p=s.path[s.path.length-1];return{x:+p.x,y:+p.y};}
function lastPath(segs){const s=segs[segs.length-1];return s&&s.path?s.path.map(p=>({x:+p.x,y:+p.y})):[];}

// ===== Rendu d'une étape (instruction + mini-plan) =====
const ICONS={
    straight:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>',
    left:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>',
    right:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m15 14 5-5-5-5"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/></svg>',
    connector:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 8l3-3 3 3M9 16l3 3 3-3"/></svg>',
    arrive:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22V4M4 4l13 0-3 5 3 5H4"/></svg>',
};
const LABELS={
    straight:s=>['Continuez tout droit',`Sur environ ${roundDist(s.dist)} mètres`],
    left:()=>['Tournez à gauche','Au prochain croisement'],
    right:()=>['Tournez à droite','Au prochain croisement'],
    connector:s=>[`Prenez l'${s.label.toLowerCase().includes('escalier')?'escalier':'ascenseur'}`, s.target?('Jusqu\'au '+(s.target.label||('niveau '+s.target.level))):'Changez d\'étage'],
    arrive:s=>['Vous êtes arrivé',s.label+' est juste devant vous'],
};

function drawStep(){
    const s=curSteps[curIdx];if(!s)return;
    const [t,d]=LABELS[s.type](s);
    document.getElementById('instrIc').innerHTML=ICONS[s.type];
    document.getElementById('instrT').textContent=t;
    document.getElementById('instrD').textContent=d;
    const distEl=document.getElementById('instrDist');
    if(s.type==='straight'){distEl.style.display='inline-flex';distEl.innerHTML=`<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 4v16M17 8l-4-4-4 4"/></svg> ${roundDist(s.dist)} m`;}
    else distEl.style.display='none';

    // progression
    const prog=document.getElementById('prog');prog.innerHTML='';
    curSteps.forEach((_,i)=>{const e=document.createElement('i');if(i<=curIdx)e.className='done';prog.appendChild(e);});

    // étage courant
    const fl=floorsById[s.floorId];
    document.getElementById('floorName').innerHTML=fl?('Niveau : <b>'+(fl.label||('N'+fl.level))+'</b>'):'';
    document.getElementById('stepCount').textContent=`Étape ${curIdx+1} / ${curSteps.length}`;

    drawMiniPlan(s);
    renderStepsList();

    document.getElementById('btnPrev').disabled=curIdx===0;
    const next=document.getElementById('btnNext');
    next.textContent=curIdx===curSteps.length-1?'Terminer':'Étape suivante';
}

// ===== Mini-plan de l'étage avec le tracé + repère de l'étape =====
const VBX=20,VBY=20,VBW=560,VBH=340;
const sx=p=>VBX+(p/100)*VBW, sy=p=>VBY+(p/100)*VBH;
function css(v){return getComputedStyle(document.documentElement).getPropertyValue(v).trim();}
function drawMiniPlan(step){
    const svg=document.getElementById('navSvg');
    const fl=floorsById[step.floorId];
    const rooms=fl?(fl.rooms||[]):[];
    const AC=css('--accent'),AC2=css('--accent2'),RM=css('--room'),RMS=css('--room-stroke'),OP=css('--open'),OPS=css('--open-stroke'),MUT=css('--muted'),BG=css('--bg');

    // Zones communes : toujours nommées sur le plan
    const COMMON=['couloir','hall','accueil','sortie','attente','circulation','palier','allée','allee','galerie','escalier','ascenseur','lobby','passage'];

    // Point de départ (QR) et d'arrivée (destination) du tracé de cette étape
    const startPt = step.path && step.path.length ? step.path[0] : null;
    const endPt   = step.path && step.path.length ? step.path[step.path.length-1] : null;
    function inRoom(pt,r){ if(!pt)return false; return pt.x>=r.x && pt.x<=r.x+r.w && pt.y>=r.y && pt.y<=r.y+r.h; }

    let h='';
    h+=`<rect x="${VBX}" y="${VBY}" width="${VBW}" height="${VBH}" rx="6" fill="none" stroke="${css('--border')}" stroke-width="2"/>`;
    rooms.forEach(r=>{
        const n=(r.name||'').toLowerCase();
        const isCommon = COMMON.some(k=>n.includes(k));
        const fill=isCommon?OP:RM, st=isCommon?OPS:RMS;
        const x=sx(r.x),y=sy(r.y),w=(r.w/100)*VBW,hh=(r.h/100)*VBH;

        if(r.shape==='circle')h+=`<ellipse cx="${x+w/2}" cy="${y+hh/2}" rx="${w/2}" ry="${hh/2}" fill="${fill}" stroke="${st}"/>`;
        else if(r.shape==='poly'&&Array.isArray(r.points)&&r.points.length>=3){const pts=r.points.map(p=>`${sx(p[0])},${sy(p[1])}`).join(' ');h+=`<polygon points="${pts}" fill="${fill}" stroke="${st}"/>`;}
        else h+=`<rect x="${x}" y="${y}" width="${w}" height="${hh}" rx="4" fill="${fill}" stroke="${st}"/>`;

        // Nom affiché UNIQUEMENT si : zone commune, OU salle de départ (QR), OU salle d'arrivée (destination)
        const isStart = inRoom(startPt,r);
        const isEnd   = inRoom(endPt,r);
        const showName = r.name && (isCommon || isStart || isEnd);
        if(showName){
            const isHighlight = isStart || (isEnd && step.type!=='connector');
            const fillTxt = isHighlight ? css('--accent-strong') : MUT;
            const weight  = isHighlight ? '700' : '600';
            h+=`<text x="${x+w/2}" y="${y+hh/2}" fill="${fillTxt}" font-size="11" font-weight="${weight}" font-family="${css('--head')}" text-anchor="middle" dominant-baseline="middle">${escapeXml(r.name)}</text>`;
        }
    });

    // tracé complet du segment de cette étape
    if(step.path&&step.path.length>=2){
        const dd='M '+step.path.map(p=>`${sx(p.x)} ${sy(p.y)}`).join(' L ');
        h+=`<path d="${dd}" fill="none" stroke="${AC}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.45"/>`;
        if(step.at){
            const idx=nearestIdx(step.path,step.at);
            const done=step.path.slice(0,idx+1);
            if(done.length>=2){const dd2='M '+done.map(p=>`${sx(p.x)} ${sy(p.y)}`).join(' L ');h+=`<path d="${dd2}" fill="none" stroke="${AC}" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>`;}
        }
        const a=step.path[0];
        h+=`<g transform="translate(${sx(a.x)} ${sy(a.y)})"><circle r="8" fill="${BG}" stroke="${AC2}" stroke-width="2.5"/></g>`;
    }

    // repère de l'étape courante (pastille pulsée)
    if(step.at){
        const px=sx(step.at.x),py=sy(step.at.y);
        if(step.type==='connector')h+=`<g transform="translate(${px} ${py})"><rect x="-13" y="-13" width="26" height="26" rx="6" fill="${AC2}"/><text y="2" text-anchor="middle" fill="${BG}" font-size="15" font-weight="700">↕</text></g>`;
        else if(step.type==='arrive')h+=`<g transform="translate(${px} ${py})"><circle r="12" fill="${AC}"/><circle r="4.5" fill="${BG}"/></g>`;
        else h+=`<g transform="translate(${px} ${py})"><circle r="13" fill="${AC}" opacity="0.25"><animate attributeName="r" values="10;16;10" dur="1.6s" repeatCount="indefinite"/></circle><circle r="7" fill="${AC}"/></g>`;
    }
    svg.innerHTML=h;
}
function nearestIdx(path,pt){let bi=0,bd=Infinity;path.forEach((p,i)=>{const d=Math.hypot(p.x-pt.x,p.y-pt.y);if(d<bd){bd=d;bi=i;}});return bi;}
function escapeXml(s){return String(s).replace(/[<>&'"]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;',"'":'&apos;','"':'&quot;'}[c]));}

// ===== Liste repliable des étapes =====
function renderStepsList(){
    const box=document.getElementById('stepsList');box.innerHTML='';
    document.getElementById('stepsSummary').textContent=`▸ Toutes les étapes (${curSteps.length})`;
    curSteps.forEach((s,i)=>{
        const [t]=LABELS[s.type](s);
        const row=document.createElement('div');
        row.className='steprow'+(i===curIdx?' cur':(i<curIdx?' done':''));
        row.innerHTML=`<div class="n">${i+1}</div><div>${t}${s.type==='straight'?` · ${roundDist(s.dist)} m`:''}</div>`;
        row.onclick=()=>{curIdx=i;drawStep();};
        box.appendChild(row);
    });
}

function nextStep(){if(curIdx<curSteps.length-1){curIdx++;drawStep();}else{toast('Arrivé à destination 🎉');exitNav();}}
function prevStep(){if(curIdx>0){curIdx--;drawStep();}}

loadDestinations();
</script>
</body>
</html>