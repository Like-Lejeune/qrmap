<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>QRMap — Éditeur de plan</title>
<script>
    window.__INITIAL__ = @json($initial ?? null);
    window.__SITE_ID__ = @json($siteId ?? null);
    window.__URLS__ = {
        store:  "{{ url('/plan-editor') }}",
        update: "{{ url('/plan-editor') }}",
        vision: "{{ url('/plan-editor/vision') }}",
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
        --text:#f0f2f5;--muted:#8892a4;
        --soft:rgba(79,255,176,.10);--soft2:rgba(79,255,176,.22);
        --head:'Syne',sans-serif;--body:'DM Sans',sans-serif;--mono:'DM Mono',monospace;
        --grid:rgba(255,255,255,.05);
        --room:rgba(79,159,255,.10);--room-stroke:rgba(79,159,255,.45);
        --open:rgba(79,255,176,.08);--open-stroke:rgba(79,255,176,.40);
        --conn:rgba(255,184,79,.14);--conn-stroke:rgba(255,184,79,.6);
        color-scheme:dark;
    }
    html[data-theme="light"]{
        --bg:#eef1f6;--bg2:#fff;--bg3:#f4f6fa;--bg4:#e7ebf2;
        --border:rgba(15,23,42,.10);--border2:rgba(15,23,42,.18);
        --accent:#16a34a;--accent2:#2563eb;--warn:#d97706;--danger:#dc2626;
        --text:#0f172a;--muted:#64748b;
        --soft:rgba(22,163,74,.08);--soft2:rgba(22,163,74,.18);
        --grid:rgba(15,23,42,.06);
        --room:rgba(37,99,235,.08);--room-stroke:rgba(37,99,235,.4);
        --open:rgba(22,163,74,.07);--open-stroke:rgba(22,163,74,.4);
        --conn:rgba(217,119,6,.10);--conn-stroke:rgba(217,119,6,.55);
        color-scheme:light;
    }
    .moon{display:block}.sun{display:none}
    html[data-theme="light"] .moon{display:none}
    html[data-theme="light"] .sun{display:block}
    #themeBtn svg{width:16px;height:16px}
    body{background:var(--bg);color:var(--text);font-family:var(--body);height:100vh;overflow:hidden;font-size:14px}

    .app{display:grid;grid-template-columns:300px 1fr 320px;grid-template-rows:56px 1fr;height:100vh}
    .topbar{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;padding:0 18px;background:var(--bg2);border-bottom:1px solid var(--border)}
    .brand{font-family:var(--head);font-weight:800;font-size:16px;color:var(--accent);display:flex;align-items:center;gap:9px}
    .brand::before{content:'';width:9px;height:9px;background:var(--accent);border-radius:2px;transform:rotate(45deg)}
    .brand small{font-family:var(--body);font-weight:500;color:var(--muted);font-size:12px}
    .top-actions{display:flex;gap:8px;align-items:center}

    .panel{background:var(--bg2);overflow-y:auto;padding:16px}
    .panel.left{border-right:1px solid var(--border)}
    .panel.right{border-left:1px solid var(--border)}
    .stage{background:var(--bg);position:relative;overflow:hidden;display:flex;flex-direction:column}

    .sec-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin:18px 0 9px}
    .sec-title:first-child{margin-top:0}

    label.f{display:block;font-size:11px;color:var(--muted);margin:9px 0 4px;font-weight:500}
    input.f,select.f,textarea.f{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:7px;color:var(--text);padding:8px 10px;font-family:var(--body);font-size:13px;outline:none;transition:border-color .15s}
    input.f:focus,select.f:focus,textarea.f:focus{border-color:var(--accent)}
    textarea.f{resize:vertical;min-height:48px}
    .row2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px}

    button{font-family:var(--body);cursor:pointer;border:none;border-radius:7px;font-size:13px;font-weight:600;transition:all .15s}
    .btn{padding:8px 12px;background:var(--bg3);color:var(--text);border:1px solid var(--border)}
    .btn:hover{border-color:var(--border2)}
    .btn.accent{background:var(--accent);color:var(--bg);border-color:var(--accent)}
    .btn.accent:hover{filter:brightness(1.08)}
    .btn.ghost{background:transparent}
    .btn.danger{color:var(--danger);border-color:transparent;background:transparent}
    .btn.danger:hover{background:rgba(255,111,111,.1)}
    .btn.sm{padding:5px 9px;font-size:12px}
    .btn.icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;padding:0}
    .btn svg{width:15px;height:15px;display:block}

    .tools{display:flex;flex-direction:column;gap:6px}
    .tool{display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);text-align:left;width:100%;color:var(--text)}
    .tool:hover{border-color:var(--border2)}
    .tool.active{background:var(--soft);border-color:var(--accent);color:var(--accent)}
    .tool svg{width:16px;height:16px;flex-shrink:0}
    .tool .k{margin-left:auto;font-family:var(--mono);font-size:11px;color:var(--muted);background:var(--bg);padding:1px 6px;border-radius:4px}

    .zorder{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}

    .floor-list{display:flex;flex-direction:column;gap:5px}
    .floor-row{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);cursor:pointer}
    .floor-row.active{background:var(--soft);border-color:var(--accent)}
    .floor-row .lvl{font-family:var(--mono);font-size:11px;color:var(--muted);background:var(--bg);width:26px;height:22px;display:flex;align-items:center;justify-content:center;border-radius:5px;flex-shrink:0}
    .floor-row.active .lvl{color:var(--accent)}
    .floor-row .nm{flex:1;font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .floor-row .del{opacity:0;color:var(--danger);font-size:16px;line-height:1}
    .floor-row:hover .del{opacity:1}

    .stage-bar{height:46px;display:flex;align-items:center;gap:14px;padding:0 16px;border-bottom:1px solid var(--border);background:var(--bg2);flex-shrink:0}
    .stage-bar .hint{font-size:12px;color:var(--muted)}
    .stage-bar .spacer{flex:1}
    .chk{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);cursor:pointer;user-select:none}
    .chk input{accent-color:var(--accent)}

    .canvas-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:22px;overflow:hidden}
    svg.editor{background:var(--bg2);border:1px solid var(--border);border-radius:10px;width:100%;max-width:920px;height:auto;touch-action:none;box-shadow:0 18px 50px -24px rgba(0,0,0,.6)}

    .rrect{cursor:move}
    .rrect .body{stroke-width:1.5;fill-opacity:.85}
    .rrect.salle .body{fill:var(--room);stroke:var(--room-stroke)}
    .rrect.open .body{fill:var(--open);stroke:var(--open-stroke)}
    .rrect.conn .body{fill:var(--conn);stroke:var(--conn-stroke);stroke-dasharray:4 3}
    .rrect.sel .body{stroke-width:2.5;stroke:var(--accent);fill-opacity:.95}
    .rrect .lbl{font-family:var(--head);font-weight:600;fill:var(--text);text-anchor:middle;dominant-baseline:middle;pointer-events:none}
    .rrect .badge{fill:var(--muted);font-family:var(--mono);font-size:7px;dominant-baseline:hanging}
    .rrect.dest .body{stroke:var(--accent2)}
    .dest-star{fill:var(--accent2);pointer-events:none}
    .handle{fill:var(--accent);stroke:var(--bg2);stroke-width:1.5;cursor:nwse-resize}
    .qr-m rect{fill:var(--text)}
    .qr-m text{fill:var(--bg);font-family:var(--head);font-weight:700;font-size:8px;text-anchor:middle;dominant-baseline:middle}
    .qr-m{cursor:move}
    .qr-m.sel rect{stroke:var(--accent);stroke-width:2}
    .ghost-rect{fill:var(--soft);stroke:var(--accent);stroke-width:1.5;stroke-dasharray:4 3;pointer-events:none}
    .door-m{fill:var(--accent);stroke:var(--bg2);stroke-width:1.5;pointer-events:none}
    .bg-plan{pointer-events:none}
    .vision-drop{border:1.5px dashed var(--border2);border-radius:10px;padding:22px;text-align:center;color:var(--muted);font-size:13px;cursor:pointer;display:block}
    .vision-drop:hover{border-color:var(--accent);color:var(--text)}
    .vision-drop img{max-width:100%;max-height:220px;border-radius:6px;margin-top:10px;display:block;margin-inline:auto}
    .vision-warn{margin-top:10px;font-size:12px;color:var(--warn);line-height:1.6}
    .vision-status{font-size:12px;color:var(--muted);margin-top:10px;min-height:18px}
    .radio-row{display:flex;gap:16px;margin-top:10px;font-size:13px}
    .radio-row label{display:flex;gap:6px;align-items:center;cursor:pointer}

    .item{display:flex;align-items:center;gap:8px;padding:7px 9px;border-radius:7px;background:var(--bg3);border:1px solid var(--border);margin-bottom:5px;font-size:12px;cursor:pointer}
    .item .tag{font-family:var(--mono);font-size:10px;color:var(--muted)}
    .item .x{margin-left:auto;color:var(--danger);cursor:pointer;font-size:15px;line-height:1}
    .empty{color:var(--muted);font-size:12px;padding:8px 0;text-align:center}
    .pill{display:inline-block;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:var(--soft);color:var(--accent)}

    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:50;padding:20px}
    .overlay.show{display:flex}
    .modal{background:var(--bg2);border:1px solid var(--border2);border-radius:14px;width:100%;max-width:760px;max-height:86vh;display:flex;flex-direction:column;overflow:hidden}
    .modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--border)}
    .modal-head h3{font-family:var(--head);font-size:16px;font-weight:700}
    .modal-tabs{display:flex;gap:4px;padding:12px 18px 0}
    .mtab{padding:7px 14px;border-radius:7px 7px 0 0;background:transparent;color:var(--muted);font-size:13px;border:1px solid transparent;border-bottom:none}
    .mtab.active{background:var(--bg3);color:var(--accent);border-color:var(--border)}
    .modal-body{padding:14px 18px;overflow:auto;flex:1}
    pre.code{background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:14px;font-family:var(--mono);font-size:12px;line-height:1.55;color:var(--text);white-space:pre;overflow:auto;max-height:52vh}
    .modal-foot{padding:13px 18px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:flex-end}

    .toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--bg4);border:1px solid var(--border2);color:var(--text);padding:10px 16px;border-radius:9px;font-size:13px;opacity:0;pointer-events:none;transition:all .25s;z-index:60}
    .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

    ::-webkit-scrollbar{width:10px;height:10px}
    ::-webkit-scrollbar-thumb{background:var(--bg4);border-radius:6px;border:2px solid var(--bg2)}
</style>
</head>
<body>
<div class="app">
    <div class="topbar">
        <div class="brand">QRMap <small>Éditeur de plan</small></div>
        <div class="top-actions">
            <a class="btn" href="{{ route('plan-editor.index') }}" style="text-decoration:none">← Plans</a>
            <button class="btn icon" id="themeBtn" title="Thème clair/sombre">
                <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/></svg>
            </button>
            <button class="btn" id="visionBtn" title="Le modèle de Meta lit une photo ou un PDF du plan et propose les pièces">✦ Lire un plan (IA)</button>
            <button class="btn" id="importBtn">Importer JSON</button>
            <button class="btn" id="exportBtn">Exporter</button>
            <button class="btn accent" id="saveBtn">Enregistrer</button>
        </div>
    </div>

    <div class="panel left">
        <div class="sec-title">Site</div>
        <input class="f" id="siteName" placeholder="Nom du site">
        <input class="f" id="siteAddr" placeholder="Adresse" style="margin-top:8px">
        <div class="row2" style="margin-top:8px">
            <input class="f" id="siteLat" placeholder="Latitude" type="number" step="any">
            <input class="f" id="siteLng" placeholder="Longitude" type="number" step="any">
        </div>
        <textarea class="f" id="siteDesc" placeholder="Description" style="margin-top:8px"></textarea>

        <div class="sec-title">Bâtiment</div>
        <input class="f" id="bldName" placeholder="Nom du bâtiment">
        <div class="sec-title" style="margin-top:12px">Modèle 3D (mètres)</div>
        <div class="row3">
            <div><label class="f">Largeur</label><input class="f" id="bldW" type="number" value="40"></div>
            <div><label class="f">Profond.</label><input class="f" id="bldD" type="number" value="25"></div>
            <div><label class="f">H. étage</label><input class="f" id="bldH" type="number" value="3.3"></div>
        </div>

        <div class="sec-title" style="display:flex;align-items:center;justify-content:space-between">
            <span>Étages</span>
            <button class="btn sm ghost" id="addFloorBtn">+ étage</button>
        </div>
        <div class="floor-list" id="floorList"></div>

        <div class="sec-title">Outils</div>
        <div class="tools">
            <button class="tool active" data-tool="select"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7 18 2-7 7-2z"/></svg> Sélection <span class="k">V</span></button>
            <button class="tool" data-tool="room"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg> Dessiner une pièce <span class="k">R</span></button>
            <button class="tool" data-tool="poly"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l9 7-3.5 11h-11L3 9z"/></svg> Polygone (clics) <span class="k">P</span></button>
            <button class="tool" data-tool="qr"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/></svg> Poser un QR <span class="k">Q</span></button>
        </div>
        <button class="btn" id="addLiftBtn" style="width:100%;margin-top:8px">+ Ascenseur aligné (tous étages)</button>
    </div>

    <div class="stage">
        <div class="stage-bar">
            <span class="pill" id="floorPill">RDC</span>
            <span class="hint" id="stageHint">Glissez pour dessiner une pièce, ou sélectionnez un élément pour l'éditer.</span>
            <span class="spacer"></span>
            <label class="chk" id="bgToggle" style="display:none"><input type="checkbox" id="bgChk" checked> Plan importé en fond</label>
            <label class="chk"><input type="checkbox" id="snapChk" checked> Aimanter à la grille</label>
        </div>
        <div class="canvas-wrap">
            <svg class="editor" id="svg" viewBox="0 0 900 600"></svg>
        </div>
    </div>

    <div class="panel right" id="rightPanel">
        <div id="propEmpty" class="empty" style="padding:30px 0">Aucune sélection.<br>Cliquez sur une pièce ou un QR.</div>

        <div id="propRoom" style="display:none">
            <div class="sec-title">Pièce</div>
            <label class="f">Nom <span style="color:var(--muted);font-weight:400">(facultatif)</span></label>
            <input class="f" id="rName" placeholder="(sans nom)">
            <label class="f">Type</label>
            <select class="f" id="rType">
                <option value="salle">Salle / Bureau (obstacle)</option>
                <option value="open">Circulation (couloir, hall, allée…)</option>
                <option value="conn">Connecteur (ascenseur / escalier)</option>
            </select>
            <label class="f">Forme</label>
            <select class="f" id="rShape">
                <option value="rect">Rectangle</option>
                <option value="circle">Cercle / ellipse</option>
                <option value="poly">Polygone (libre)</option>
            </select>
            <label class="f">Identifiant</label>
            <input class="f" id="rId">

            <label class="f">Superposition (ordre d'affichage)</label>
            <div class="zorder">
                <button class="btn sm" id="zBackBtn" title="Envoyer derrière">⤓ Arrière</button>
                <button class="btn sm" id="zFrontBtn" title="Amener devant">⤒ Avant</button>
            </div>

            <div class="row2" style="margin-top:8px">
                <div><label class="f">X (%)</label><input class="f" id="rX" type="number"></div>
                <div><label class="f">Y (%)</label><input class="f" id="rY" type="number"></div>
            </div>
            <div class="row2">
                <div><label class="f">Largeur (%)</label><input class="f" id="rW" type="number"></div>
                <div><label class="f">Hauteur (%)</label><input class="f" id="rH" type="number"></div>
            </div>

            <div class="sec-title">Destination</div>
            <label class="chk" style="margin-bottom:8px"><input type="checkbox" id="rIsDest"> Faire de cette pièce une destination</label>
            <div id="destFields" style="display:none">
                <label class="f">Libellé affiché</label>
                <input class="f" id="dLabel">
                <label class="f">Catégorie</label>
                <select class="f" id="dCat">
                    <option value="service">service</option>
                    <option value="medical">medical</option>
                    <option value="admin">admin</option>
                    <option value="shop">shop</option>
                    <option value="exit">exit</option>
                </select>
                <label class="f">Description</label>
                <input class="f" id="dDesc">
            </div>

            <div class="sec-title">Porte (point d'arrivée de la navigation)</div>
            <div class="zorder">
                <button class="btn sm" id="doorPlaceBtn" title="Cliquez ensuite sur un bord de la pièce">Placer la porte</button>
                <button class="btn sm" id="doorClearBtn">Retirer</button>
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px" id="doorInfo">Aucune porte : la navigation vise le centre.</div>

            <button class="btn danger" id="delRoomBtn" style="width:100%;margin-top:16px">Supprimer la pièce</button>
        </div>

        <div id="propQr" style="display:none">
            <div class="sec-title">QR code</div>
            <label class="f">Libellé (emplacement)</label>
            <input class="f" id="qLabel">
            <div class="row2" style="margin-top:4px">
                <div><label class="f">pos_x (%)</label><input class="f" id="qX" type="number"></div>
                <div><label class="f">pos_y (%)</label><input class="f" id="qY" type="number"></div>
            </div>
            <button class="btn danger" id="delQrBtn" style="width:100%;margin-top:16px">Supprimer le QR</button>
        </div>

        <div class="sec-title" style="margin-top:24px">Pièces de l'étage <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">(haut = devant)</span></div>
        <div id="roomList"></div>
        <div class="sec-title">Destinations de l'étage</div>
        <div id="destList"></div>
        <div class="sec-title">QR de l'étage</div>
        <div id="qrList"></div>
    </div>
</div>

<div class="overlay" id="overlay">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modalTitle">Exporter</h3>
            <button class="btn ghost icon" id="modalClose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        </div>
        <div class="modal-tabs" id="modalTabs">
            <button class="mtab active" data-mt="json">JSON</button>
            <button class="mtab" data-mt="php">Seeder PHP</button>
        </div>
        <div class="modal-body"><pre class="code" id="codeOut"></pre></div>
        <div class="modal-foot">
            <button class="btn" id="copyBtn">Copier</button>
            <button class="btn accent" id="downloadBtn">Télécharger</button>
        </div>
    </div>
</div>

<div class="overlay" id="visionOverlay">
    <div class="modal" style="max-width:560px">
        <div class="modal-head">
            <h3>Lire un plan avec l'IA</h3>
            <button class="btn ghost icon" id="visionClose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        </div>
        <div class="modal-body">
            <p style="font-size:12px;color:var(--muted);margin-bottom:10px">Photo du plan d'évacuation, scan ou PDF d'architecte. Le modèle de Meta propose les pièces de l'étage actif ; vous corrigez ensuite, l'image restant affichée en fond.</p>
            <label class="vision-drop" id="visionDrop">
                <input type="file" id="visionFile" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none">
                <span id="visionDropTxt">Choisir une image ou un PDF (1re page)</span>
                <img id="visionPreview" alt="" style="display:none">
            </label>
            <label class="f" style="margin-top:12px">Précision (facultatif)</label>
            <input class="f" id="visionHint" maxlength="300" placeholder="Ex. : rez-de-chaussée, bâtiment de 40 × 25 m">
            <div class="radio-row">
                <label><input type="radio" name="visionMode" value="replace" checked> Remplacer les pièces de l'étage</label>
                <label><input type="radio" name="visionMode" value="append"> Ajouter aux pièces existantes</label>
            </div>
            <div class="vision-status" id="visionStatus"></div>
            <div class="vision-warn" id="visionWarn"></div>
        </div>
        <div class="modal-foot">
            <button class="btn" id="visionCancel">Annuler</button>
            <button class="btn accent" id="visionRun" disabled>Lire le plan</button>
        </div>
    </div>
</div>

<div class="overlay" id="importOverlay">
    <div class="modal">
        <div class="modal-head">
            <h3>Importer un JSON</h3>
            <button class="btn ghost icon" id="importClose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        </div>
        <div class="modal-body">
            <p style="font-size:12px;color:var(--muted);margin-bottom:8px">Collez un JSON précédemment exporté pour reprendre l'édition.</p>
            <textarea class="f" id="importText" style="min-height:300px;font-family:var(--mono);font-size:12px"></textarea>
        </div>
        <div class="modal-foot">
            <button class="btn" id="importCancel">Annuler</button>
            <button class="btn accent" id="importApply">Charger</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

@include('partials.room-types')

<script>
// VW/VH ne sont plus fixes : ils reflètent le RATIO réel largeur/profondeur
// du bâtiment (width_m / depth_m), pour que les pièces gardent des proportions
// fidèles quel que soit la forme du site (carré, tout en longueur, etc.).
// Les coordonnées restent en % (0-100) : seul le cadre d'affichage s'adapte.
const BASE=900;      // taille de référence du plus grand côté, en pixels d'écran
const MIN_SIDE=420;  // évite un cadre trop écrasé sur un bâtiment très allongé
let VW=900,VH=600;
const pxX=p=>(p/100)*VW,pxY=p=>(p/100)*VH;
const pcX=x=>(x/VW)*100,pcY=y=>(y/VH)*100;

function updateViewBox(){
    const w=Math.max(1,+state.building.width_m||40);
    const d=Math.max(1,+state.building.depth_m||25);
    if(w>=d){ VW=BASE; VH=Math.max(MIN_SIDE,Math.round(BASE*d/w)); }
    else    { VH=BASE; VW=Math.max(MIN_SIDE,Math.round(BASE*w/d)); }
    $('#svg').setAttribute('viewBox',`0 0 ${VW} ${VH}`);
}

let state={
    site:{name:'',address:'',latitude:'',longitude:'',description:''},
    building:{name:'',width_m:40,depth_m:25,floor_height_m:3.3},
    floors:[{level:0,label:'Rez-de-chaussée',rooms:[]}],
    qrcodes:[],
};
let activeFloor=0,tool='select',sel=null,snap=true;

const $=s=>document.querySelector(s);
const svg=$('#svg');
const SVGNS='http://www.w3.org/2000/svg';
let uidc=1;const uid=()=>'r'+(uidc++);

function floor(){return state.floors[activeFloor];}
function snapV(v){return snap?Math.round(v):Math.round(v*10)/10;}
function clamp(v,a,b){return Math.max(a,Math.min(b,v));}
function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),1800);}

function typeOf(room){
    if(room._type)return room._type;
    const n=(room.name||'').toLowerCase();
    const open=['couloir','hall','accueil','sortie','entrée','entree','escalier','ascenseur','attente','circulation','palier','allée','allee','galerie','lobby','lounge','foyer','terrasse','passage','rampe'];
    const conn=['ascenseur','escalier','palier'];
    if(conn.some(k=>n.includes(k)))return 'conn';
    if(open.some(k=>n.includes(k)))return 'open';
    return 'salle';
}

function render(){
    while(svg.firstChild)svg.removeChild(svg.firstChild);
    const defs=document.createElementNS(SVGNS,'defs');
    defs.innerHTML=`<pattern id="g" width="${VW/20}" height="${VH/20}" patternUnits="userSpaceOnUse"><path d="M ${VW/20} 0 L 0 0 0 ${VH/20}" fill="none" stroke="var(--grid)" stroke-width="1"/></pattern>`;
    svg.appendChild(defs);
    add('rect',{x:0,y:0,width:VW,height:VH,fill:'url(#g)'});
    add('rect',{x:1,y:1,width:VW-2,height:VH-2,rx:8,fill:'none',stroke:'var(--border)','stroke-width':1});
    drawBgPlan();
    // Ordre du tableau = ordre d'empilement : index 0 dessiné en premier (derrière),
    // dernier index dessiné en dernier (devant).
    floor().rooms.forEach(r=>drawRoom(r));
    state.qrcodes.filter(q=>q.floorIndex===activeFloor).forEach(q=>drawQr(q));
    refreshLists();
}

function add(tag,attrs,parent){
    const el=document.createElementNS(SVGNS,tag);
    for(const k in attrs)el.setAttribute(k,attrs[k]);
    (parent||svg).appendChild(el);return el;
}

function roomBodyEl(r,x,y,w,h,parent){
    if(r.shape==='circle'){
        return add('ellipse',{class:'body',cx:x+w/2,cy:y+h/2,rx:w/2,ry:h/2},parent);
    }
    if(r.shape==='poly' && Array.isArray(r.points) && r.points.length>=3){
        const pts=r.points.map(([px,py])=>`${pxX(px)},${pxY(py)}`).join(' ');
        return add('polygon',{class:'body',points:pts},parent);
    }
    return add('rect',{class:'body',x,y,width:w,height:h,rx:5},parent);
}

function drawRoom(r){
    const t=typeOf(r);
    const g=add('g',{class:`rrect ${t} ${sel&&sel.kind==='room'&&sel.id===r._id?'sel':''} ${r._dest?'dest':''}`});
    g.dataset.id=r._id;
    const x=pxX(r.x),y=pxY(r.y),w=pxX(r.w),h=pxY(r.h);
    roomBodyEl(r,x,y,w,h,g);
    // ── Décor : teinte + icône selon le type de pièce (visuel uniquement) ──
    const kind = (typeof roomKind==='function') ? roomKind(r) : null;
    if(kind){
        if(kind.tint && kind.tint!=='transparent'){
            // calque de teinte par-dessus le corps de la pièce
            roomBodyEl(r,x,y,w,h,g).setAttribute('fill',kind.tint);
            g.lastChild.setAttribute('stroke','none');
            g.lastChild.setAttribute('pointer-events','none');
        }
        // icône en filigrane, centrée, taille proportionnelle à la pièce
        const isz=Math.max(14,Math.min(34,Math.min(w,h)*0.4));
        if(w>34 && h>28){
            const ic=add('g',{class:'room-ic','pointer-events':'none',
                transform:`translate(${x+w/2-isz/2} ${y+h/2-isz/2}) scale(${isz/24})`},g);
<<<<<<< Updated upstream
            ic.innerHTML=`<svg viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" opacity="0.5">${kind.icon}</svg>`;
=======
            ic.innerHTML=`<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" opacity="0.5">${kind.icon}</svg>`;
>>>>>>> Stashed changes
        }
    }
    const nm=r.name||'';
    if(nm){
        const fs=Math.max(9,Math.min(15,w/6));
        const tx=add('text',{class:'lbl',x:x+w/2,y:y+h/2,'font-size':fs},g);
        tx.textContent=nm;
    }
    add('text',{class:'badge',x:x+5,y:y+5},g).textContent=(r.id||'');
    if(r._dest)add('circle',{class:'dest-star',cx:x+w-12,cy:y+10,r:4},g);
    if(r.door)add('circle',{class:'door-m',cx:pxX(r.door.x),cy:pxY(r.door.y),r:5},g);
    if(sel&&sel.kind==='room'&&sel.id===r._id)add('rect',{class:'handle',x:x+w-7,y:y+h-7,width:10,height:10,rx:2,'data-h':'br'},g);
    g.addEventListener('pointerdown',e=>onRoomDown(e,r));
}

function drawQr(q){
    const g=add('g',{class:`qr-m ${sel&&sel.kind==='qr'&&sel.id===q._id?'sel':''}`});
    g.dataset.id=q._id;
    const x=pxX(q.pos_x),y=pxY(q.pos_y);
    add('rect',{x:x-13,y:y-13,width:26,height:26,rx:5},g);
    add('text',{x,y:y+1},g).textContent='QR';
    g.addEventListener('pointerdown',e=>onQrDown(e,q));
}

let drag=null;

svg.addEventListener('pointerdown',e=>{
    if(tool==='poly'){
        const p=svgPt(e);
        polyPts.push([snapV(pcX(p.x)),snapV(pcY(p.y))]);
        drawPolyGhost();
        return;
    }
    if(e.target.closest('.rrect')||e.target.closest('.qr-m'))return;
    const p=svgPt(e);
    if(tool==='door'){placeDoorAt(p);return;}
    if(tool==='room'){
        drag={mode:'draw',x0:p.x,y0:p.y,ghost:add('rect',{class:'ghost-rect',x:p.x,y:p.y,width:0,height:0,rx:5})};
        svg.setPointerCapture(e.pointerId);
    }else if(tool==='qr'){
        const q={_id:uid(),floorIndex:activeFloor,label:'Nouveau QR',pos_x:snapV(pcX(p.x)),pos_y:snapV(pcY(p.y))};
        state.qrcodes.push(q);select('qr',q._id);render();setTool('select');
    }else select(null);
});

let polyPts=[];
function drawPolyGhost(){
    render();
    if(!polyPts.length)return;
    const pts=polyPts.map(([px,py])=>`${pxX(px)},${pxY(py)}`).join(' ');
    if(polyPts.length>=2) add('polyline',{points:pts,fill:'none',stroke:'var(--accent)','stroke-width':2,'stroke-dasharray':'4 3'});
    polyPts.forEach(([px,py])=>add('circle',{cx:pxX(px),cy:pxY(py),r:4,fill:'var(--accent)'}));
}
function finishPoly(){
    if(polyPts.length>=3){
        const xs=polyPts.map(p=>p[0]),ys=polyPts.map(p=>p[1]);
        const minX=Math.min(...xs),minY=Math.min(...ys),maxX=Math.max(...xs),maxY=Math.max(...ys);
        const r={_id:uid(),id:nextRoomId(),name:'',_type:'salle',shape:'poly',
            points:polyPts.slice(),
            x:snapV(minX),y:snapV(minY),w:snapV(maxX-minX)||2,h:snapV(maxY-minY)||2};
        floor().rooms.push(r);select('room',r._id);
    }
    polyPts=[];setTool('select');render();
}
svg.addEventListener('dblclick',e=>{ if(tool==='poly'){e.preventDefault();finishPoly();} });

function onRoomDown(e,r){
    e.stopPropagation();
    if(tool==='qr')return;
    if(tool==='door'){placeDoorAt(svgPt(e));return;}
    select('room',r._id);
    const p=svgPt(e);
    if(e.target.classList.contains('handle'))drag={mode:'resize',r,startW:r.w,startH:r.h,px:p.x,py:p.y};
    else drag={mode:'move',r,dx:p.x-pxX(r.x),dy:p.y-pxY(r.y)};
    svg.setPointerCapture(e.pointerId);render();
}

function onQrDown(e,q){
    e.stopPropagation();select('qr',q._id);
    const p=svgPt(e);
    drag={mode:'moveqr',q,dx:p.x-pxX(q.pos_x),dy:p.y-pxY(q.pos_y)};
    svg.setPointerCapture(e.pointerId);render();
}

svg.addEventListener('pointermove',e=>{
    if(!drag)return;
    const p=svgPt(e);
    if(drag.mode==='draw'){
        const x=Math.min(drag.x0,p.x),y=Math.min(drag.y0,p.y);
        drag.ghost.setAttribute('x',x);drag.ghost.setAttribute('y',y);
        drag.ghost.setAttribute('width',Math.abs(p.x-drag.x0));drag.ghost.setAttribute('height',Math.abs(p.y-drag.y0));
    }else if(drag.mode==='move'){
        const ndx=clamp(snapV(pcX(p.x-drag.dx)),0,100-drag.r.w);
        const ndy=clamp(snapV(pcY(p.y-drag.dy)),0,100-drag.r.h);
        movePolyWithBox(drag.r,ndx,ndy);
        drag.r.x=ndx;drag.r.y=ndy;
        render();syncProps();
    }else if(drag.mode==='resize'){
        drag.r.w=clamp(snapV(drag.startW+pcX(p.x-drag.px)),2,100-drag.r.x);
        drag.r.h=clamp(snapV(drag.startH+pcY(p.y-drag.py)),2,100-drag.r.y);
        if(drag.r.door)drag.r.door=snapToEdge(drag.r,drag.r.door.x,drag.r.door.y);
        render();syncProps();
    }else if(drag.mode==='moveqr'){
        drag.q.pos_x=clamp(snapV(pcX(p.x-drag.dx)),0,100);
        drag.q.pos_y=clamp(snapV(pcY(p.y-drag.dy)),0,100);
        render();syncProps();
    }
});

// Déplace aussi les sommets d'un polygone quand on bouge sa boîte englobante.
function movePolyWithBox(r,nx,ny){
    const ddx=nx-r.x,ddy=ny-r.y;
    if(r.shape==='poly'&&Array.isArray(r.points)){
        r.points=r.points.map(([px,py])=>[+(px+ddx).toFixed(1),+(py+ddy).toFixed(1)]);
    }
    // La porte se déplace avec sa pièce
    if(r.door)r.door={x:+(r.door.x+ddx).toFixed(1),y:+(r.door.y+ddy).toFixed(1)};
}
// Recolle un point sur le bord le plus proche d'une pièce (même logique que le serveur)
function snapToEdge(r,x,y){
    const cx=clamp(x,r.x,r.x+r.w),cy=clamp(y,r.y,r.y+r.h);
    const d={l:cx-r.x,r:r.x+r.w-cx,t:cy-r.y,b:r.y+r.h-cy};
    const m=Math.min(d.l,d.r,d.t,d.b);
    const o=m===d.l?{x:r.x,y:cy}:m===d.r?{x:r.x+r.w,y:cy}:m===d.t?{x:cx,y:r.y}:{x:cx,y:r.y+r.h};
    return {x:+o.x.toFixed(1),y:+o.y.toFixed(1)};
}
function placeDoorAt(p){
    const r=selRoom();
    if(!r){toast('Sélectionnez d\'abord la pièce');setTool('select');return;}
    r.door=snapToEdge(r,pcX(p.x),pcY(p.y));
    setTool('select');render();syncProps();toast('Porte placée');
}

svg.addEventListener('pointerup',()=>{
    if(drag&&drag.mode==='draw'){
        const g=drag.ghost;
        const w=pcX(+g.getAttribute('width')),h=pcY(+g.getAttribute('height'));
        const x=pcX(+g.getAttribute('x')),y=pcY(+g.getAttribute('y'));
        g.remove();
        if(w>=2&&h>=2){
            const r={_id:uid(),id:nextRoomId(),name:'',_type:'salle',x:snapV(x),y:snapV(y),w:snapV(w),h:snapV(h)};
            floor().rooms.push(r);select('room',r._id);setTool('select');
        }
        render();
    }
    drag=null;
});

function svgPt(e){
    const r=svg.getBoundingClientRect();
    return {x:(e.clientX-r.left)/r.width*VW,y:(e.clientY-r.top)/r.height*VH};
}

function nextRoomId(){
    // id unique et stable, indépendant du nombre de pièces (évite les collisions
    // après suppressions). Basé sur le niveau + compteur interne.
    let base,n=floor().rooms.length+1,id;
    do{ id='F'+floor().level+'R'+n; n++; }while(floor().rooms.some(r=>r.id===id));
    return id;
}

function select(kind,id){sel=kind?{kind,id}:null;render();syncProps();}
function selRoom(){return sel&&sel.kind==='room'?floor().rooms.find(r=>r._id===sel.id):null;}
function selQr(){return sel&&sel.kind==='qr'?state.qrcodes.find(q=>q._id===sel.id):null;}

// ===== Réordonnancement (superposition) =====
function roomIndex(r){return floor().rooms.findIndex(x=>x._id===r._id);}
function bringToFront(){
    const r=selRoom();if(!r)return;
    const i=roomIndex(r);if(i<0)return;
    floor().rooms.splice(i,1);floor().rooms.push(r); // fin = devant
    render();
}
function sendToBack(){
    const r=selRoom();if(!r)return;
    const i=roomIndex(r);if(i<0)return;
    floor().rooms.splice(i,1);floor().rooms.unshift(r); // début = derrière
    render();
}
$('#zFrontBtn').onclick=bringToFront;
$('#zBackBtn').onclick=sendToBack;

function syncProps(){
    const r=selRoom(),q=selQr();
    $('#propEmpty').style.display=(!r&&!q)?'block':'none';
    $('#propRoom').style.display=r?'block':'none';
    $('#propQr').style.display=q?'block':'none';
    if(r){
        $('#rName').value=r.name||'';$('#rId').value=r.id||'';$('#rType').value=typeOf(r);
        $('#rShape').value=r.shape||'rect';
        $('#rX').value=r.x;$('#rY').value=r.y;$('#rW').value=r.w;$('#rH').value=r.h;
        $('#rIsDest').checked=!!r._dest;$('#destFields').style.display=r._dest?'block':'none';
        if(r._dest){$('#dLabel').value=r._dest.label||r.name||'';$('#dCat').value=r._dest.category||'service';$('#dDesc').value=r._dest.description||'';}
    }
    if(q){$('#qLabel').value=q.label||'';$('#qX').value=q.pos_x;$('#qY').value=q.pos_y;}
    if(r)$('#doorInfo').textContent=r.door?`Porte en (${r.door.x} %, ${r.door.y} %) : la navigation s'arrête ici.`:'Aucune porte : la navigation vise le centre.';
}

['rName','rId','rType','rShape','rX','rY','rW','rH'].forEach(id=>{
    $('#'+id).addEventListener('input',()=>{
        const r=selRoom();if(!r)return;
        if(id==='rName')r.name=$('#rName').value;
        if(id==='rId')r.id=$('#rId').value;
        if(id==='rType')r._type=$('#rType').value;
        if(id==='rShape'){
            const v=$('#rShape').value;
            r.shape=v;
            if(v!=='poly') delete r.points;
            else if(!r.points){
                r.points=[[r.x,r.y],[r.x+r.w,r.y],[r.x+r.w,r.y+r.h],[r.x,r.y+r.h]];
            }
        }
        if(id==='rX'){const nv=clamp(+$('#rX').value||0,0,100);movePolyWithBox(r,nv,r.y);r.x=nv;}
        if(id==='rY'){const nv=clamp(+$('#rY').value||0,0,100);movePolyWithBox(r,r.x,nv);r.y=nv;}
        if(id==='rW')r.w=clamp(+$('#rW').value||2,2,100);
        if(id==='rH')r.h=clamp(+$('#rH').value||2,2,100);
        render();
    });
});
$('#rIsDest').addEventListener('change',()=>{
    const r=selRoom();if(!r)return;
    if($('#rIsDest').checked)r._dest={label:r.name||'',category:'service',description:''};
    else delete r._dest;
    syncProps();render();
});
['dLabel','dCat','dDesc'].forEach(id=>{
    $('#'+id).addEventListener('input',()=>{
        const r=selRoom();if(!r||!r._dest)return;
        r._dest.label=$('#dLabel').value;r._dest.category=$('#dCat').value;r._dest.description=$('#dDesc').value;
        render();
    });
});
$('#delRoomBtn').addEventListener('click',()=>{const r=selRoom();if(!r)return;floor().rooms=floor().rooms.filter(x=>x._id!==r._id);select(null);});

['qLabel','qX','qY'].forEach(id=>{
    $('#'+id).addEventListener('input',()=>{
        const q=selQr();if(!q)return;
        if(id==='qLabel')q.label=$('#qLabel').value;
        if(id==='qX')q.pos_x=clamp(+$('#qX').value||0,0,100);
        if(id==='qY')q.pos_y=clamp(+$('#qY').value||0,0,100);
        render();
    });
});
$('#delQrBtn').addEventListener('click',()=>{const q=selQr();if(!q)return;state.qrcodes=state.qrcodes.filter(x=>x._id!==q._id);select(null);});

function refreshLists(){
    // Liste des pièces (du haut = devant). On affiche dans l'ordre inverse du tableau.
    const rl=$('#roomList');
    const rooms=floor().rooms;
    rl.innerHTML=rooms.length?'':'<div class="empty">Aucune pièce</div>';
    [...rooms].reverse().forEach(r=>{
        const d=document.createElement('div');
        d.className='item'+(sel&&sel.kind==='room'&&sel.id===r._id?'':'');
        const t=typeOf(r);
        const nm=r.name||'(sans nom)';
        d.innerHTML=`<span class="tag">${r.id||'—'}</span> ${nm} <span class="tag" style="margin-left:auto">${t}</span> <span class="x">×</span>`;
        d.querySelector('.x').onclick=ev=>{ev.stopPropagation();floor().rooms=floor().rooms.filter(x=>x._id!==r._id);if(sel&&sel.id===r._id)select(null);else render();};
        d.onclick=ev=>{if(!ev.target.classList.contains('x'))select('room',r._id);};
        rl.appendChild(d);
    });

    const dl=$('#destList'),ql=$('#qrList');
    const dests=floor().rooms.filter(r=>r._dest);
    dl.innerHTML=dests.length?'':'<div class="empty">Aucune destination</div>';
    dests.forEach(r=>{
        const d=document.createElement('div');d.className='item';
        d.innerHTML=`<span class="tag">${r._dest.category}</span> ${r._dest.label||r.name||'(sans nom)'} <span class="x">×</span>`;
        d.querySelector('.x').onclick=ev=>{ev.stopPropagation();delete r._dest;render();};
        d.onclick=ev=>{if(!ev.target.classList.contains('x'))select('room',r._id);};
        dl.appendChild(d);
    });
    const qrs=state.qrcodes.filter(q=>q.floorIndex===activeFloor);
    ql.innerHTML=qrs.length?'':'<div class="empty">Aucun QR</div>';
    qrs.forEach(q=>{
        const d=document.createElement('div');d.className='item';
        d.innerHTML=`<span class="tag">${q.pos_x},${q.pos_y}</span> ${q.label} <span class="x">×</span>`;
        d.querySelector('.x').onclick=ev=>{ev.stopPropagation();state.qrcodes=state.qrcodes.filter(x=>x._id!==q._id);if(sel&&sel.id===q._id)select(null);else render();};
        d.onclick=ev=>{if(!ev.target.classList.contains('x'))select('qr',q._id);};
        ql.appendChild(d);
    });
}

function renderFloors(){
    const list=$('#floorList');list.innerHTML='';
    state.floors.forEach((f,i)=>{
        const row=document.createElement('div');
        row.className='floor-row'+(i===activeFloor?' active':'');
        row.innerHTML=`<span class="lvl">N${f.level}</span><span class="nm">${f.label||('Niveau '+f.level)}</span><span class="del">×</span>`;
        row.onclick=e=>{
            if(e.target.classList.contains('del')){
                if(state.floors.length<=1){toast('Au moins un étage requis');return;}
                if(!confirm('Supprimer cet étage ?'))return;
                state.qrcodes=state.qrcodes.filter(q=>q.floorIndex!==i).map(q=>({...q,floorIndex:q.floorIndex>i?q.floorIndex-1:q.floorIndex}));
                state.floors.splice(i,1);
                if(activeFloor>=state.floors.length)activeFloor=state.floors.length-1;
                select(null);renderFloors();render();updateFloorPill();
            }else{activeFloor=i;select(null);renderFloors();render();updateFloorPill();}
        };
        row.querySelector('.nm').addEventListener('dblclick',()=>{
            const nv=prompt('Nom de l\'étage',f.label);if(nv!==null){f.label=nv;renderFloors();updateFloorPill();}
        });
        list.appendChild(row);
    });
}
function updateFloorPill(){$('#floorPill').textContent=floor().label||('Niveau '+floor().level);}

$('#addFloorBtn').onclick=()=>{
    const lvl=Math.max(...state.floors.map(f=>f.level),-1)+1;
    state.floors.push({level:lvl,label:'Niveau '+lvl,rooms:[]});
    activeFloor=state.floors.length-1;select(null);renderFloors();render();updateFloorPill();
};

$('#addLiftBtn').onclick=()=>{
    const lift={name:'Ascenseurs',_type:'conn',x:44,y:46,w:12,h:12};
    state.floors.forEach(f=>{
        if(f.rooms.some(r=>typeOf(r)==='conn'&&Math.abs(r.x-lift.x)<2&&Math.abs(r.y-lift.y)<2))return;
        f.rooms.push({_id:uid(),id:'F'+f.level+'ASC',...lift});
    });
    toast('Ascenseur ajouté à tous les étages');render();
};

function setTool(t){
    if(tool==='poly'&&t!=='poly'){polyPts=[];}
    tool=t;
    document.querySelectorAll('.tool').forEach(b=>b.classList.toggle('active',b.dataset.tool===t));
    const hints={select:'Glissez une pièce pour la déplacer, utilisez la poignée pour redimensionner. Les pièces peuvent se superposer.',room:'Glissez sur le plan pour dessiner une pièce.',qr:'Cliquez sur le plan pour poser un QR code.',poly:'Cliquez pour ajouter des sommets. Double-clic (ou Entrée) pour terminer, Échap pour annuler.',door:'Cliquez sur le bord de la pièce sélectionnée, là où se trouve son entrée.'};
    $('#stageHint').textContent=hints[t];
}
document.querySelectorAll('.tool').forEach(b=>b.onclick=()=>setTool(b.dataset.tool));
document.addEventListener('keydown',e=>{
    if(/input|textarea|select/i.test(e.target.tagName))return;
    if(tool==='poly'){
        if(e.key==='Enter'){finishPoly();return;}
        if(e.key==='Escape'){polyPts=[];setTool('select');render();return;}
    }
    if(e.key==='v')setTool('select');if(e.key==='r')setTool('room');if(e.key==='p')setTool('poly');if(e.key==='q')setTool('qr');
    if((e.key==='Delete'||e.key==='Backspace')&&sel){
        if(sel.kind==='room')floor().rooms=floor().rooms.filter(x=>x._id!==sel.id);
        else state.qrcodes=state.qrcodes.filter(x=>x._id!==sel.id);
        select(null);
    }
    // Raccourcis superposition : [ = arrière, ] = avant
    if(e.key===']'&&sel&&sel.kind==='room')bringToFront();
    if(e.key==='['&&sel&&sel.kind==='room')sendToBack();
});
$('#snapChk').onchange=e=>snap=e.target.checked;

const bind=(id,obj,key,num)=>{$('#'+id).addEventListener('input',()=>{obj[key]=num?(+$('#'+id).value||0):$('#'+id).value;});};
bind('siteName',state.site,'name');bind('siteAddr',state.site,'address');
bind('siteLat',state.site,'latitude');bind('siteLng',state.site,'longitude');bind('siteDesc',state.site,'description');
bind('bldName',state.building,'name');
bind('bldH',state.building,'floor_height_m',1);
// Largeur/Profondeur : en plus de mettre à jour l'état, on adapte le cadre
// d'affichage à la vraie forme du bâtiment et on redessine.
['bldW','bldD'].forEach(id=>{
    $('#'+id).addEventListener('input',()=>{
        const key=id==='bldW'?'width_m':'depth_m';
        state.building[key]=+$('#'+id).value||(key==='width_m'?40:25);
        updateViewBox();render();
    });
});

function pushSiteToInputs(){
    $('#siteName').value=state.site.name;$('#siteAddr').value=state.site.address;
    $('#siteLat').value=state.site.latitude;$('#siteLng').value=state.site.longitude;
    $('#siteDesc').value=state.site.description;$('#bldName').value=state.building.name;
    $('#bldW').value=state.building.width_m;$('#bldD').value=state.building.depth_m;$('#bldH').value=state.building.floor_height_m;
}

$('#themeBtn').onclick=()=>{
    const cur=document.documentElement.getAttribute('data-theme')==='light'?'light':'dark';
    const next=cur==='light'?'dark':'light';
    document.documentElement.setAttribute('data-theme',next);
    try{localStorage.setItem('qrmap-theme',next);}catch(e){}
    render();
};
// Restaurer le thème choisi (partagé avec les autres vues via 'qrmap-theme')
(function(){let t='dark';try{t=localStorage.getItem('qrmap-theme')||'dark';}catch(e){}document.documentElement.setAttribute('data-theme',t);})();

function buildJSON(){
    return {
        site:{...state.site},
        building:{name:state.building.name,floors_count:state.floors.length,
            model_3d:{width_m:+state.building.width_m,depth_m:+state.building.depth_m,floor_height_m:+state.building.floor_height_m}},
        floors:state.floors.map(f=>({level:f.level,label:f.label,rooms:f.rooms.map(r=>{
            const o={id:r.id,name:r.name||'',x:r.x,y:r.y,w:r.w,h:r.h};
            if(r.shape&&r.shape!=='rect')o.shape=r.shape;
            if(r.shape==='poly'&&r.points)o.points=r.points;
            if(r.door)o.door={x:r.door.x,y:r.door.y};
            return o;
        })})),
        destinations:state.floors.flatMap(f=>f.rooms.filter(r=>r._dest).map((r,idx)=>({floor_level:f.level,room_id:r.id,label:r._dest.label||r.name||r.id,category:r._dest.category,description:r._dest.description,sort_order:idx+1,is_active:true}))),
        qrcodes:state.qrcodes.map(q=>({db_id:q.db_id||null,floor_level:state.floors[q.floorIndex].level,label:q.label,pos_x:q.pos_x,pos_y:q.pos_y})),
    };
}

function php(v){return String(v).replace(/\\/g,'\\\\').replace(/'/g,"\\'");}
function buildPHP(){
    const cls=(state.site.name||'Generated').replace(/[^a-zA-Z0-9]/g,'')+'Seeder';
    let s=`<?php\n\nnamespace Database\\Seeders;\n\nuse App\\Models\\Destination;\nuse App\\Models\\Site;\nuse App\\Services\\QrCodeService;\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\Storage;\n\nclass ${cls} extends Seeder\n{\n    public function run(): void\n    {\n        Storage::disk('public')->makeDirectory('qrcodes');\n        $service = app(QrCodeService::class);\n\n`;
    s+=`        $site = Site::create([\n            'name'        => '${php(state.site.name)}',\n            'address'     => '${php(state.site.address)}',\n            'latitude'    => ${state.site.latitude||'null'},\n            'longitude'   => ${state.site.longitude||'null'},\n            'description' => '${php(state.site.description)}',\n        ]);\n\n`;
    s+=`        $building = $site->buildings()->create([\n            'name'         => '${php(state.building.name)}',\n            'floors_count' => ${state.floors.length},\n            'model_3d'     => ['width_m' => ${+state.building.width_m}, 'depth_m' => ${+state.building.depth_m}, 'floor_height_m' => ${+state.building.floor_height_m}],\n        ]);\n\n`;
    state.floors.forEach((f,i)=>{
        s+=`        $floor${i} = $building->floors()->create([\n            'level' => ${f.level},\n            'label' => '${php(f.label)}',\n            'rooms' => [\n`;
        f.rooms.forEach(r=>{
            let extra='';
            if(r.shape&&r.shape!=='rect')extra+=",'shape'=>'"+r.shape+"'";
            if(r.shape==='poly'&&r.points)extra+=",'points'=>"+JSON.stringify(r.points);
            if(r.door)extra+=",'door'=>['x'=>"+r.door.x+",'y'=>"+r.door.y+"]";
            s+=`                ['id'=>'${php(r.id)}','name'=>'${php(r.name||'')}','x'=>${r.x},'y'=>${r.y},'w'=>${r.w},'h'=>${r.h}${extra}],\n`;
        });
        s+=`            ],\n        ]);\n\n`;
    });
    const dests=[];
    state.floors.forEach((f,fi)=>f.rooms.filter(r=>r._dest).forEach(r=>dests.push({fi,r})));
    if(dests.length){
        s+=`        $destData = [\n`;
        dests.forEach((d,idx)=>{s+=`            [$floor${d.fi}, '${php(d.r.id)}', '${php(d.r._dest.label||d.r.name||d.r.id)}', '${php(d.r._dest.category)}', '${php(d.r._dest.description)}', ${idx+1}],\n`;});
        s+=`        ];\n\n        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {\n            $floor->destinations()->create([\n                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,'description'=>$desc,'sort_order'=>$order,'is_active'=>true,\n            ]);\n        }\n\n`;
    }
    if(state.qrcodes.length){
        s+=`        $qrPlacements = [\n`;
        state.qrcodes.forEach(q=>{s+=`            ['floor'=>$floor${q.floorIndex}, 'label'=>'${php(q.label)}', 'pos_x'=>${q.pos_x}, 'pos_y'=>${q.pos_y}],\n`;});
        s+=`        ];\n\n        foreach ($qrPlacements as $p) {\n            $service->generate([\n                'site_id'=>$site->id,'building_id'=>$building->id,'floor_id'=>$p['floor']->id,\n                'location_label'=>$p['label'],'pos_x'=>$p['pos_x'],'pos_y'=>$p['pos_y'],\n            ]);\n        }\n\n`;
    }
    s+=`        $this->command->info('✓ ${php(state.site.name)} — '.$building->floors()->count().' étages.');\n    }\n}\n`;
    return s;
}

let modalMode='json';
function openExport(){$('#overlay').classList.add('show');showExport(modalMode);}
function showExport(m){
    modalMode=m;
    document.querySelectorAll('.mtab').forEach(b=>b.classList.toggle('active',b.dataset.mt===m));
    $('#codeOut').textContent=m==='json'?JSON.stringify(buildJSON(),null,2):buildPHP();
}
$('#exportBtn').onclick=openExport;
$('#modalClose').onclick=()=>$('#overlay').classList.remove('show');
document.querySelectorAll('.mtab').forEach(b=>b.onclick=()=>showExport(b.dataset.mt));
$('#copyBtn').onclick=()=>{navigator.clipboard.writeText($('#codeOut').textContent).then(()=>toast('Copié'));};
$('#downloadBtn').onclick=()=>{
    const isJson=modalMode==='json';
    const base=(state.site.name||'qrmap').replace(/[^a-z0-9]/gi,'_');
    const fn=isJson?base+'.json':(state.site.name||'Generated').replace(/[^a-zA-Z0-9]/g,'')+'Seeder.php';
    const blob=new Blob([$('#codeOut').textContent],{type:'text/plain'});
    const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=fn;a.click();toast('Téléchargé');
};

$('#importBtn').onclick=()=>$('#importOverlay').classList.add('show');
$('#importClose').onclick=$('#importCancel').onclick=()=>$('#importOverlay').classList.remove('show');
$('#importApply').onclick=()=>{
    try{loadFrom(JSON.parse($('#importText').value));$('#importOverlay').classList.remove('show');toast('Plan chargé');}
    catch(err){toast('JSON invalide');}
};
function loadFrom(data){
    state.site={name:'',address:'',latitude:'',longitude:'',description:'',...(data.site||{})};
    const m=(data.building&&data.building.model_3d)||{};
    state.building={name:(data.building&&data.building.name)||'',width_m:m.width_m||40,depth_m:m.depth_m||25,floor_height_m:m.floor_height_m||3.3};
    state.floors=(data.floors||[]).map(f=>({level:f.level,label:f.label,rooms:(f.rooms||[]).map(r=>({_id:uid(),id:r.id,name:r.name||'',x:r.x,y:r.y,w:r.w,h:r.h,shape:r.shape||'rect',points:r.points||undefined,door:(r.door&&isFinite(r.door.x)&&isFinite(r.door.y))?{x:+r.door.x,y:+r.door.y}:undefined}))}));
    if(!state.floors.length)state.floors=[{level:0,label:'Rez-de-chaussée',rooms:[]}];
    (data.destinations||[]).forEach(d=>{
        const f=state.floors.find(fl=>fl.level===d.floor_level);if(!f)return;
        const r=f.rooms.find(rr=>rr.id===d.room_id);if(!r)return;
        r._dest={label:d.label,category:d.category||'service',description:d.description||''};
    });
    state.qrcodes=(data.qrcodes||[]).map(q=>{
        const fi=state.floors.findIndex(fl=>fl.level===q.floor_level);
        return {_id:uid(),db_id:q.db_id||null,floorIndex:fi<0?0:fi,label:q.label,pos_x:q.pos_x,pos_y:q.pos_y};
    });
    activeFloor=0;sel=null;
    pushSiteToInputs();updateViewBox();renderFloors();updateFloorPill();render();
}

// ===== Sauvegarde serveur =====
let siteId = window.__SITE_ID__ || null;

function validateBeforeSave(){
    if(!state.site.name.trim()){toast('Renseignez le nom du site');return false;}
    if(!state.building.name.trim()){toast('Renseignez le nom du bâtiment');return false;}
    if(state.floors.every(f=>f.rooms.length===0)){toast('Dessinez au moins une pièce');return false;}
    return true;
}

async function saveToServer(){
    if(!validateBeforeSave())return;
    const btn=$('#saveBtn');btn.disabled=true;const prev=btn.textContent;btn.textContent='Enregistrement…';
    const payload=buildJSON();
    const token=document.querySelector('meta[name="csrf-token"]').content;
    const isUpdate=!!siteId;
    const url=isUpdate?`${window.__URLS__.update}/${siteId}`:window.__URLS__.store;
    try{
        const res=await fetch(url,{
            method:isUpdate?'PUT':'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
            body:JSON.stringify(payload),
        });
        if(!res.ok){
            const err=await res.json().catch(()=>({}));
            throw new Error(err.message||('Erreur '+res.status));
        }
        const data=await res.json();
        if(data.site_id){siteId=data.site_id;}
        if(Array.isArray(data.qrcodes)){
            data.qrcodes.forEach(srv=>{
                const match=state.qrcodes.find(q=>q.floorIndex===state.floors.findIndex(f=>f.level===srv.floor_level)&&q.label===srv.label&&!q.db_id);
                if(match){match.db_id=srv.id;match.uuid=srv.uuid;}
            });
        }
        toast(isUpdate?'Plan mis à jour':'Plan enregistré ✓');
    }catch(e){
        toast('Échec : '+e.message);
    }finally{
        btn.disabled=false;btn.textContent=prev;
    }
}
$('#saveBtn').onclick=saveToServer;

// ═════════ Portes ═════════
$('#doorPlaceBtn').onclick=()=>{if(!selRoom()){toast('Sélectionnez une pièce');return;}setTool('door');};
$('#doorClearBtn').onclick=()=>{const r=selRoom();if(!r)return;delete r.door;render();syncProps();};

// ═════════ Plan importé en fond (calque de correction) ═════════
// Gardé en mémoire uniquement (non enregistré), un par étage.
const bgByFloor={};
function drawBgPlan(){
    const bg=bgByFloor[activeFloor];
    $('#bgToggle').style.display=bg?'':'none';
    if(!bg||!$('#bgChk').checked)return;
    // bbox = contour du bâtiment dans l'image (en %) → on étire l'image pour que
    // ce contour recouvre exactement le cadre 0-100 % du plan
    const b=bg.bbox, iw=VW*100/b.w, ih=VH*100/b.h;
    add('image',{class:'bg-plan',href:bg.url,x:-b.x/100*iw,y:-b.y/100*ih,width:iw,height:ih,preserveAspectRatio:'none',opacity:.35});
}
$('#bgChk').onchange=render;

// ═════════ Lecture de plan par l'IA ═════════
let visionImage=null;
const PDFJS='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/';

function loadScript(src){return new Promise((ok,ko)=>{const s=document.createElement('script');s.src=src;s.onload=ok;s.onerror=()=>ko(new Error('Chargement impossible : '+src));document.head.appendChild(s);});}

// Image réduite (côté max 2000 px, JPEG) : envoi plus rapide, lecture suffisante
function canvasToJpeg(c){
    const k=Math.min(1,2000/Math.max(c.width,c.height));
    if(k===1)return c.toDataURL('image/jpeg',0.85);
    const o=document.createElement('canvas');o.width=Math.round(c.width*k);o.height=Math.round(c.height*k);
    const g=o.getContext('2d');g.fillStyle='#fff';g.fillRect(0,0,o.width,o.height);g.drawImage(c,0,0,o.width,o.height);
    return o.toDataURL('image/jpeg',0.85);
}
async function fileToImage(file){
    if(file.type==='application/pdf'){
        if(!window.pdfjsLib){await loadScript(PDFJS+'pdf.min.js');pdfjsLib.GlobalWorkerOptions.workerSrc=PDFJS+'pdf.worker.min.js';}
        const pdf=await pdfjsLib.getDocument({data:await file.arrayBuffer()}).promise;
        const page=await pdf.getPage(1);
        const v1=page.getViewport({scale:1});
        const vp=page.getViewport({scale:Math.min(4,2000/Math.max(v1.width,v1.height))});
        const c=document.createElement('canvas');c.width=vp.width;c.height=vp.height;
        const g=c.getContext('2d');g.fillStyle='#fff';g.fillRect(0,0,c.width,c.height);
        await page.render({canvasContext:g,viewport:vp}).promise;
        return canvasToJpeg(c);
    }
    const url=URL.createObjectURL(file);
    try{
        const img=await new Promise((ok,ko)=>{const i=new Image();i.onload=()=>ok(i);i.onerror=()=>ko(new Error('Image illisible'));i.src=url;});
        const c=document.createElement('canvas');c.width=img.naturalWidth;c.height=img.naturalHeight;
        const g=c.getContext('2d');g.fillStyle='#fff';g.fillRect(0,0,c.width,c.height);g.drawImage(img,0,0);
        return canvasToJpeg(c);
    }finally{URL.revokeObjectURL(url);}
}

function openVision(){
    visionImage=null;$('#visionFile').value='';$('#visionPreview').style.display='none';
    $('#visionDropTxt').textContent='Choisir une image ou un PDF (1re page)';
    $('#visionStatus').textContent='';$('#visionWarn').innerHTML='';$('#visionRun').disabled=true;
    $('#visionOverlay').classList.add('show');
}
function closeVision(){$('#visionOverlay').classList.remove('show');}
$('#visionBtn').onclick=openVision;
$('#visionClose').onclick=$('#visionCancel').onclick=closeVision;

$('#visionFile').onchange=async e=>{
    const f=e.target.files[0];if(!f)return;
    if(f.size>25*1024*1024){toast('Fichier trop lourd (25 Mo max)');return;}
    $('#visionStatus').textContent='Préparation de l\'image…';$('#visionRun').disabled=true;
    try{
        visionImage=await fileToImage(f);
        $('#visionPreview').src=visionImage;$('#visionPreview').style.display='block';
        $('#visionDropTxt').textContent=f.name;
        $('#visionStatus').textContent='Prêt. La lecture prend en général 20 à 60 secondes.';
        $('#visionRun').disabled=false;
    }catch(err){visionImage=null;$('#visionStatus').textContent='Erreur : '+err.message;}
};

$('#visionRun').onclick=async()=>{
    if(!visionImage)return;
    const mode=document.querySelector('input[name="visionMode"]:checked').value;
    if(mode==='replace'&&floor().rooms.length&&!confirm('Remplacer les '+floor().rooms.length+' pièces de cet étage ?'))return;
    const btn=$('#visionRun');btn.disabled=true;
    $('#visionStatus').textContent='Le modèle lit le plan…';$('#visionWarn').innerHTML='';
    try{
        const res=await fetch(window.__URLS__.vision,{
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:JSON.stringify({image:visionImage,level:floor().level,hint:$('#visionHint').value||null}),
        });
        const data=await res.json().catch(()=>({}));
        if(!res.ok)throw new Error(data.message||('Erreur '+res.status));
        applyVision(data,mode);
    }catch(err){$('#visionStatus').textContent='Échec : '+err.message;}
    finally{btn.disabled=false;}
};

function applyVision(data,mode){
    const rooms=Array.isArray(data.rooms)?data.rooms:[];
    if(!rooms.length){
        $('#visionStatus').textContent='Aucune pièce détectée.';
        $('#visionWarn').innerHTML=(data.warnings||[]).map(w=>'⚠ '+escHtml(w)).join('<br>');
        return;
    }
    if(mode==='replace'){
        const ids=new Set(floor().rooms.map(r=>r._id));
        floor().rooms=[];
        if(sel&&ids.has(sel.id))sel=null;
    }
    rooms.forEach(r=>{
        const room={_id:uid(),id:nextRoomId(),name:r.name||'',x:+r.x,y:+r.y,w:+r.w,h:+r.h,shape:'rect'};
        if(r.door)room.door={x:+r.door.x,y:+r.door.y};
        floor().rooms.push(room);
    });
    // Dimensions réelles : seulement si le modèle a pu les lire (cotes, échelle)
    const b=data.building||{};
    if(b.width_m)state.building.width_m=b.width_m;
    if(b.depth_m)state.building.depth_m=b.depth_m;
    if(b.width_m||b.depth_m){pushSiteToInputs();updateViewBox();}
    bgByFloor[activeFloor]={url:visionImage,bbox:data.bbox||{x:0,y:0,w:100,h:100}};
    $('#bgChk').checked=true;
    closeVision();render();syncProps();
    const warns=(data.warnings||[]).length;
    toast(rooms.length+' pièce(s) importée(s)'+(warns?' — '+warns+' point(s) à vérifier':'')+'. Corrigez en vous aidant du plan en fond.');
    if(warns)setTimeout(()=>alertWarnings(data.warnings),400);
}
function alertWarnings(list){$('#stageHint').textContent='À vérifier : '+list.join(' · ');}
function escHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

if(window.__INITIAL__){
    try{ loadFrom(window.__INITIAL__); }catch(e){ console.error(e); }
}

updateViewBox();renderFloors();updateFloorPill();render();syncProps();
</script>
</body>
</html>