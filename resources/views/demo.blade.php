<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QRMap — Simulation 2D &amp; 3D</title>
    <meta name="description" content="Essayez la navigation intérieure QRMap : scan QR, plan 2D animé et bâtiment 3D interactif.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    primary: { DEFAULT: '#0AAB6E', dark: '#078a58', foreground: '#ffffff' },
                    ink: { DEFAULT: '#1A202C', 2: '#4A5568' },
                    mist: '#F4F8F6',
                    background: '#ffffff',
                    border: 'rgba(26,32,44,0.10)',
                },
                fontFamily: { display: ['Syne', 'sans-serif'], body: ['DM Sans', 'sans-serif'] },
            }},
        };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }
        .glow-green { box-shadow: 0 0 0 1px rgba(10,171,110,0.3), 0 10px 40px -10px rgba(10,171,110,0.5); }
        .lucide { width: 1em; height: 1em; display:inline-block; vertical-align:-0.125em; }

        .animate-pulse-ring { animation: ring 2s ease-out infinite; }
        @keyframes ring { 0% { transform: scale(.8); opacity:.7 } 100% { transform: scale(2.2); opacity:0 } }
        .animate-pulse-dot { animation: dot 2s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        @keyframes dot { 0%,100% { transform: scale(1) } 50% { transform: scale(1.15) } }
        input[type=range] { accent-color: #0AAB6E; }
    </style>
</head>
<body class="bg-mist text-ink">

<!-- HEADER -->
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-border">
    <div class="mx-auto max-w-7xl px-5 sm:px-8 h-16 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2 font-display font-bold text-lg">
            <i data-lucide="arrow-left" class="w-4 h-4"></i><span class="text-primary">◆</span> QRMap
        </a>
        <div class="hidden md:flex items-center gap-2 text-sm text-ink-2">
            <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i> Hôpital Central de Yaoundé — Démo interactive
        </div>
        <a href="/" class="text-sm font-medium text-ink-2 hover:text-primary transition">Retour</a>
    </div>
</header>

<main class="mx-auto max-w-7xl px-5 sm:px-8 py-10">
    <!-- Intro -->
    <div class="max-w-3xl mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 text-primary px-3 py-1 text-xs font-semibold uppercase tracking-wider">
            <i data-lucide="play" class="w-3 h-3"></i> Simulation
        </span>
        <h1 class="mt-4 text-3xl sm:text-5xl font-display font-bold">Scannez. Choisissez. Suivez le chemin.</h1>
        <p class="mt-3 text-ink-2">Vous arrivez à l'accueil de l'hôpital. Un QR code est affiché sur le mur. Scannez-le, sélectionnez votre destination, et laissez QRMap vous guider en 2D ou en 3D.</p>
    </div>

    <div class="grid lg:grid-cols-[320px_1fr] gap-6">
        <!-- SIDEBAR -->
        <aside class="space-y-5">
            <!-- Step 1 -->
            <div class="rounded-2xl bg-white border border-border p-5">
                <div class="text-xs font-semibold text-primary uppercase tracking-wider">Étape 1</div>
                <div class="mt-1 font-display font-bold text-lg">Scanner le QR</div>
                <button id="scanBtn" class="mt-4 w-full aspect-square rounded-xl border-2 border-dashed border-border hover:border-primary flex flex-col items-center justify-center gap-2 transition">
                    <i data-lucide="qr-code" id="scanIcon" class="w-16 h-16 text-ink-2"></i>
                    <span id="scanText" class="text-sm font-medium">Cliquez pour simuler le scan</span>
                </button>
            </div>

            <!-- Step 2 -->
            <div id="step2" class="rounded-2xl bg-white border border-border p-5 transition opacity-50 pointer-events-none">
                <div class="text-xs font-semibold text-primary uppercase tracking-wider">Étape 2</div>
                <div class="mt-1 font-display font-bold text-lg">Choisir une destination</div>
                <div id="destList" class="mt-4 space-y-2"></div>
            </div>

            <!-- Step 3 -->
            <div id="step3" class="rounded-2xl bg-white border border-border p-5 transition opacity-50 pointer-events-none">
                <div class="text-xs font-semibold text-primary uppercase tracking-wider">Étape 3</div>
                <div class="mt-1 font-display font-bold text-lg">Mode d'affichage</div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button id="mode2d" class="flex items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold border bg-ink text-white border-ink transition"><i data-lucide="layers" class="w-4 h-4"></i> 2D</button>
                    <button id="mode3d-" class="flex items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold border bg-white text-ink border-border hover:border-ink transition"><i data-lucide="box" class="w-4 h-4"></i> 3D</button>
                </div>
                <button id="replayBtn" class="mt-3 w-full inline-flex items-center justify-center gap-2 text-xs text-ink-2 hover:text-primary"><i data-lucide="rotate-cw" class="w-3 h-3"></i> Rejouer l'animation</button>
            </div>
        </aside>

        <!-- STAGE -->
        <section class="rounded-2xl bg-white border border-border overflow-hidden">
            <!-- Empty -->
            <div id="emptyStage" class="aspect-[4/3] flex flex-col items-center justify-center text-ink-2 p-10 text-center">
                <i data-lucide="qr-code" class="w-20 h-20 text-primary/40"></i>
                <p class="mt-4 font-display text-xl">En attente d'un scan</p>
                <p class="text-sm">Cliquez sur le QR à gauche pour démarrer la simulation.</p>
            </div>

            <!-- 2D -->
            <div id="stage2d" class="hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-border bg-mist/50">
                    <div class="text-sm font-semibold text-ink-2">Plan — <span id="floorName">Rez-de-chaussée</span></div>
                    <div class="flex gap-1" id="floorTabs"></div>
                </div>
                <div class="p-4 sm:p-6 bg-mist">
                    <svg id="svg2d" viewBox="0 0 600 400" class="w-full h-auto rounded-xl bg-white shadow-sm"></svg>
                    <div class="mt-4 flex items-center gap-3 text-sm text-ink-2">
                        <i data-lucide="navigation" class="w-4 h-4 text-primary"></i>
                        <span id="navHint"></span>
                    </div>
                </div>
            </div>

            <!-- 3D -->
            <div id="stage3d" class="hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-border bg-mist/50">
                    <div class="text-sm font-semibold text-ink-2">Vue 3D du bâtiment</div>
                    <div class="flex items-center gap-3 text-xs text-ink-2">
                        <label class="flex items-center gap-2">Pivot <input type="range" id="rotY" min="-60" max="60" value="-28"></label>
                        <label class="flex items-center gap-2">Inclinaison <input type="range" id="rotX" min="30" max="75" value="58"></label>
                    </div>
                </div>
                <div class="relative bg-gradient-to-b from-mist to-white p-6 sm:p-10" style="perspective:1400px">
                    <div id="scene3d" class="relative mx-auto" style="width:360px;height:360px;transform-style:preserve-3d;transition:transform .15s ease-out"></div>
                    <div class="mt-6 flex items-center gap-3 text-sm text-ink-2 justify-center">
                        <i data-lucide="box" class="w-4 h-4 text-primary"></i>
                        L'étage en vert contient votre destination : <strong id="dest3dLabel" class="text-ink"></strong>.
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Info -->
    <div class="mt-10 grid sm:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white border border-border p-5"><div class="font-display font-bold">Sans GPS</div><p class="mt-1 text-sm text-ink-2">Position déterminée par le QR scanné — précision intérieure parfaite.</p></div>
        <div class="rounded-xl bg-white border border-border p-5"><div class="font-display font-bold">Sans installation</div><p class="mt-1 text-sm text-ink-2">Tout se passe dans le navigateur. Aucune application à télécharger.</p></div>
        <div class="rounded-xl bg-white border border-border p-5"><div class="font-display font-bold">Multi-étages</div><p class="mt-1 text-sm text-ink-2">Plans 2D par niveau ou vue 3D du bâtiment entier.</p></div>
    </div>
</main>

<script>
// ===== Données =====
const ENTRY = [60, 360];
const STAIRS = [520, 80];
const FLOOR_NAMES = ["Rez-de-chaussée", "Étage 1", "Étage 2"];
const GREEN = "#0AAB6E", INKC = "#1A202C";

const DESTINATIONS = [
    { id:"pharma",   label:"Pharmacie",            floor:0, x:370, y:230, path2D:[ENTRY,[60,230],[300,230],[370,230]] },
    { id:"urgences", label:"Urgences",             floor:0, x:300, y:80,  path2D:[ENTRY,[230,360],[230,80],[300,80]] },
    { id:"cardio",   label:"Cardiologie — Ét. 1",  floor:1, x:120, y:130, path2D:[STAIRS,[520,230],[120,230],[120,130]] },
    { id:"labo",     label:"Laboratoire — Ét. 2",  floor:2, x:350, y:320, path2D:[STAIRS,[350,80],[350,320]] },
];

const ROOMS = {
    0: [
        {x:40,y:40,w:180,h:120,label:"Accueil"},
        {x:240,y:40,w:140,h:120,label:"Urgences",hl:"urgences"},
        {x:400,y:40,w:160,h:120,label:"Bureau"},
        {x:40,y:180,w:260,h:80,label:"Couloir",muted:true},
        {x:320,y:180,w:240,h:80,label:"Pharmacie",hl:"pharma"},
        {x:40,y:280,w:520,h:80,label:"Salle d'attente",muted:true},
    ],
    1: [
        {x:40,y:40,w:200,h:120,label:"Cardiologie",hl:"cardio"},
        {x:260,y:40,w:140,h:120,label:"Consult. 1"},
        {x:420,y:40,w:140,h:120,label:"Consult. 2"},
        {x:40,y:180,w:520,h:60,label:"Couloir",muted:true},
        {x:40,y:260,w:250,h:100,label:"Radiologie"},
        {x:310,y:260,w:250,h:100,label:"Pédiatrie"},
    ],
    2: [
        {x:40,y:40,w:260,h:120,label:"Bloc opératoire"},
        {x:320,y:40,w:240,h:120,label:"Réanimation"},
        {x:40,y:180,w:520,h:60,label:"Couloir",muted:true},
        {x:40,y:260,w:260,h:100,label:"Bureaux"},
        {x:320,y:260,w:240,h:100,label:"Laboratoire",hl:"labo"},
    ],
};

// ===== État =====
let state = { mode:"2d", destId:"cardio", activeFloor:0, scanned:false, progress:0, raf:0 };
const $ = s => document.querySelector(s);
const NS = "http://www.w3.org/2000/svg";
const dest = () => DESTINATIONS.find(d => d.id === state.destId);

// ===== Chemin visible selon l'étage =====
function visiblePath() {
    const d = dest();
    if (d.floor === 0) return state.activeFloor === 0 ? d.path2D : [];
    if (state.activeFloor === 0) return [ENTRY, [60,80], STAIRS];
    if (state.activeFloor === d.floor) return d.path2D;
    return [];
}
function pathLen(p){ let l=0; for(let i=1;i<p.length;i++){l+=Math.hypot(p[i][0]-p[i-1][0],p[i][1]-p[i-1][1]);} return l; }
function walkerAt(p, prog){
    if(!p.length) return null;
    const total=pathLen(p); let rem=total*prog;
    for(let i=1;i<p.length;i++){
        const seg=Math.hypot(p[i][0]-p[i-1][0],p[i][1]-p[i-1][1]);
        if(rem<=seg){const t=seg===0?0:rem/seg;return [p[i-1][0]+(p[i][0]-p[i-1][0])*t,p[i-1][1]+(p[i][1]-p[i-1][1])*t];}
        rem-=seg;
    }
    return p[p.length-1];
}

// ===== Liste destinations =====
function renderDestList(){
    const wrap=$("#destList"); wrap.innerHTML="";
    DESTINATIONS.forEach(d=>{
        const active=d.id===state.destId;
        const b=document.createElement("button");
        b.className="w-full text-left rounded-lg px-3 py-2.5 text-sm font-medium border transition flex items-center justify-between "+
            (active?"bg-primary text-primary-foreground border-primary glow-green":"bg-white text-ink border-border hover:border-primary");
        b.innerHTML=`<span class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4"></i> ${d.label}</span><span class="text-xs opacity-70">${FLOOR_NAMES[d.floor]}</span>`;
        b.onclick=()=>{ state.destId=d.id; state.activeFloor=d.floor; renderDestList(); renderFloorTabs(); restart(); };
        wrap.appendChild(b);
    });
    lucide.createIcons();
}

// ===== Onglets d'étage =====
function renderFloorTabs(){
    const wrap=$("#floorTabs"); wrap.innerHTML="";
    [2,1,0].forEach(f=>{
        const active=state.activeFloor===f;
        const b=document.createElement("button");
        b.className="w-9 h-9 rounded-md text-sm font-bold border transition "+
            (active?"bg-primary text-primary-foreground border-primary":"bg-white text-ink-2 border-border hover:border-primary");
        b.textContent=f===0?"RDC":f;
        b.title=FLOOR_NAMES[f];
        b.onclick=()=>{ state.activeFloor=f; renderFloorTabs(); restart(); };
        wrap.appendChild(b);
    });
    $("#floorName").textContent=FLOOR_NAMES[state.activeFloor];
}

// ===== Rendu SVG 2D =====
function el(tag,attrs,parent){ const e=document.createElementNS(NS,tag); for(const k in attrs)e.setAttribute(k,attrs[k]); parent.appendChild(e); return e; }
function drawRoom(svg,r){
    const d=dest();
    const hl=r.hl && d.id===r.hl;
    const fill=hl?"rgba(10,171,110,0.15)":(r.muted?"#F1F5F4":"white");
    const stroke=hl?GREEN:"#D7DEE6";
    const g=el("g",{},svg);
    el("rect",{x:r.x,y:r.y,width:r.w,height:r.h,fill,stroke,"stroke-width":hl?2:1,rx:4},g);
    const t=el("text",{x:r.x+r.w/2,y:r.y+r.h/2+4,"text-anchor":"middle","font-size":11,"font-weight":hl?700:500,fill:hl?"#2f7d54":"#566275","font-family":"DM Sans"},g);
    t.textContent=r.label;
}
function render2D(){
    const svg=$("#svg2d"); svg.innerHTML="";
    const defs=el("defs",{},svg);
    const pat=el("pattern",{id:"g",width:40,height:40,patternUnits:"userSpaceOnUse"},defs);
    el("path",{d:"M 40 0 L 0 0 0 40",fill:"none",stroke:"#E7ECEB","stroke-width":0.5},pat);
    el("rect",{width:600,height:400,fill:"url(#g)"},svg);
    el("rect",{x:20,y:20,width:560,height:360,fill:"none",stroke:INKC,"stroke-width":3,rx:6},svg);

    ROOMS[state.activeFloor].forEach(r=>drawRoom(svg,r));

    // escalier
    const sg=el("g",{transform:`translate(${STAIRS[0]} ${STAIRS[1]})`},svg);
    el("rect",{x:-18,y:-18,width:36,height:36,rx:4,fill:"#E7ECEB",stroke:"#8A93A0"},sg);
    const st=el("text",{x:0,y:4,"text-anchor":"middle","font-size":10,"font-weight":700,fill:"#3a4350"},sg); st.textContent="↕";

    // chemin
    const p=visiblePath();
    const total=pathLen(p);
    if(p.length){
        const d="M "+p.map(([x,y])=>`${x} ${y}`).join(" L ");
        el("path",{d,fill:"none",stroke:"rgba(10,171,110,0.25)","stroke-width":6,"stroke-linecap":"round","stroke-linejoin":"round"},svg);
        el("path",{d,fill:"none",stroke:GREEN,"stroke-width":4,"stroke-linecap":"round","stroke-linejoin":"round","stroke-dasharray":total,"stroke-dashoffset":total*(1-state.progress)},svg);
    }

    // QR entrée (RDC)
    if(state.activeFloor===0){
        const qg=el("g",{transform:`translate(${ENTRY[0]} ${ENTRY[1]})`},svg);
        el("circle",{r:14,fill:INKC},qg);
        const qt=el("text",{y:4,"text-anchor":"middle","font-size":10,"font-weight":700,fill:"white"},qg); qt.textContent="QR";
    }

    // destination
    const d=dest();
    if(state.activeFloor===d.floor){
        const dg=el("g",{transform:`translate(${d.x} ${d.y})`},svg);
        el("circle",{r:18,fill:"rgba(10,171,110,0.2)",class:"animate-pulse-dot"},dg);
        el("circle",{r:10,fill:GREEN},dg);
        el("circle",{r:4,fill:"white"},dg);
    }

    // marcheur
    const w=walkerAt(p,state.progress);
    if(w){
        const wg=el("g",{transform:`translate(${w[0]} ${w[1]})`},svg);
        el("circle",{r:9,fill:"white",stroke:GREEN,"stroke-width":3},wg);
        el("circle",{r:3,fill:GREEN},wg);
    }

    // hint
    $("#navHint").textContent = (d.floor!==state.activeFloor && d.floor!==0)
        ? `Empruntez les escaliers, puis passez à l'${FLOOR_NAMES[d.floor]}.`
        : `Direction : ${d.label}. Distance estimée ~ ${Math.round(total/8)} m.`;
}

// ===== Rendu 3D =====
function render3D(){
    const scene=$("#scene3d"); scene.innerHTML="";
    const d=dest();
    $("#dest3dLabel").textContent=d.label;

    // sol
    const ground=document.createElement("div");
    ground.className="absolute inset-0 rounded-lg";
    ground.style.cssText="transform:translateZ(-10px);background:repeating-linear-gradient(45deg,#EAEEED 0 12px,#F4F8F6 12px 24px);box-shadow:0 40px 80px -20px rgba(26,32,44,0.3)";
    scene.appendChild(ground);

    [0,1,2].forEach(f=>{
        const isDest=d.floor===f;
        const fl=document.createElement("div");
        fl.className="absolute inset-0 rounded-md border";
        fl.style.transform=`translateZ(${f*90+10}px)`;
        fl.style.background=isDest?"linear-gradient(135deg,rgba(10,171,110,0.85),rgba(7,138,88,0.9))":"linear-gradient(135deg,rgba(255,255,255,0.9),rgba(238,242,241,0.9))";
        fl.style.borderColor=isDest?"#078a58":"#D7DEE6";
        fl.style.boxShadow=isDest?"0 0 0 2px rgba(10,171,110,0.4),0 20px 40px -10px rgba(10,171,110,0.5)":"0 8px 20px -8px rgba(26,32,44,0.25)";

        const lbl=document.createElement("div");
        lbl.className="absolute left-2 top-2 text-[10px] font-bold uppercase tracking-wider "+(isDest?"text-white":"text-ink-2");
        lbl.style.cssText="transform:rotateX(-90deg) translateY(-10px);transform-origin:top left";
        lbl.textContent=f===0?"RDC":`Ét. ${f}`;
        fl.appendChild(lbl);

        const win=document.createElement("div");
        win.className="absolute inset-3 rounded-sm opacity-60";
        win.style.background=isDest?"repeating-linear-gradient(90deg,transparent 0 14px,rgba(255,255,255,0.25) 14px 18px)":"repeating-linear-gradient(90deg,transparent 0 14px,rgba(10,171,110,0.2) 14px 18px)";
        fl.appendChild(win);

        if(isDest){
            const beacon=document.createElement("div");
            beacon.className="absolute";
            beacon.style.cssText=`left:${(d.x/600)*100}%;top:${(d.y/400)*100}%;transform:translate(-50%,-50%) translateZ(60px)`;
            beacon.innerHTML=`<div class="relative">
                <div class="absolute -inset-3 rounded-full bg-white/70 animate-pulse-ring"></div>
                <div class="relative w-5 h-5 rounded-full bg-white border-2 border-primary-dark shadow-lg"></div>
                <div class="absolute left-1/2 bottom-full mb-1 -translate-x-1/2 whitespace-nowrap rounded-md bg-ink text-white text-[10px] font-semibold px-2 py-1">${d.label}</div>
            </div>`;
            fl.appendChild(beacon);
        }

        if(f===0){
            const qr=document.createElement("div");
            qr.className="absolute";
            qr.style.cssText=`left:${(ENTRY[0]/600)*100}%;top:${(ENTRY[1]/400)*100}%;transform:translate(-50%,-50%) translateZ(20px)`;
            qr.innerHTML=`<div class="w-6 h-6 rounded bg-ink text-white text-[9px] font-bold flex items-center justify-center shadow-md">QR</div>`;
            fl.appendChild(qr);
        }
        scene.appendChild(fl);
    });

    // cage d'escalier verticale
    const shaft=document.createElement("div");
    shaft.className="absolute";
    shaft.style.cssText=`left:${(STAIRS[0]/600)*100}%;top:${(STAIRS[1]/400)*100}%;width:24px;height:24px;transform:translate(-50%,-50%);transform-style:preserve-3d`;
    shaft.innerHTML=`<div style="width:8px;height:280px;background:linear-gradient(to top,rgba(10,171,110,0.1),rgba(10,171,110,0.7));transform:translateZ(10px) rotateX(90deg);transform-origin:bottom;border-radius:4px"></div>`;
    scene.appendChild(shaft);

    applyRot();
}
function applyRot(){
    const rx=$("#rotX").value, ry=$("#rotY").value;
    $("#scene3d").style.transform=`rotateX(${rx}deg) rotateZ(${ry}deg)`;
}

// ===== Animation =====
function restart(){
    cancelAnimationFrame(state.raf);
    state.progress=0;
    if(!state.scanned) return;
    if(state.mode==="2d"){
        const start=performance.now(), dur=2800;
        const step=t=>{
            state.progress=Math.min(1,(t-start)/dur);
            render2D();
            if(state.progress<1) state.raf=requestAnimationFrame(step);
        };
        state.raf=requestAnimationFrame(step);
    } else {
        render3D();
    }
}

// ===== Bascule de vue =====
function showStage(){
    $("#emptyStage").classList.toggle("hidden", state.scanned);
    $("#stage2d").classList.toggle("hidden", !(state.scanned && state.mode==="2d"));
    $("#stage3d").classList.toggle("hidden", !(state.scanned && state.mode==="3d"));
}
function setMode(m){
    state.mode=m;
    $("#mode2d").className="flex items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold border transition "+(m==="2d"?"bg-ink text-white border-ink":"bg-white text-ink border-border hover:border-ink");
    $("#mode3d").className="flex items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold border transition "+(m==="3d"?"bg-ink text-white border-ink":"bg-white text-ink border-border hover:border-ink");
    lucide.createIcons();
    showStage(); restart();
}

// ===== Événements =====
$("#scanBtn").onclick=()=>{
    state.scanned=true;
    $("#scanBtn").className="mt-4 w-full aspect-square rounded-xl border-2 border-dashed bg-primary/5 border-primary flex flex-col items-center justify-center gap-2 transition";
    $("#scanIcon").classList.remove("text-ink-2"); $("#scanIcon").classList.add("text-primary");
    $("#scanText").textContent="QR scanné ✓ Accueil — RDC";
    $("#step2").classList.remove("opacity-50","pointer-events-none");
    $("#step3").classList.remove("opacity-50","pointer-events-none");
    state.activeFloor=dest().floor;
    renderFloorTabs(); renderDestList(); showStage(); restart();
};
$("#mode2d").onclick=()=>setMode("2d");
$("#mode3d").onclick=()=>setMode("3d");
$("#replayBtn").onclick=()=>restart();
$("#rotX").oninput=applyRot; $("#rotY").oninput=applyRot;

// ===== Init =====
renderDestList(); renderFloorTabs(); lucide.createIcons();
</script>
</body>
</html>