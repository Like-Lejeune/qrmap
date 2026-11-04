{{-- resources/views/partials/guide-ia.blade.php
     Guide IA (Llama) : le visiteur pose sa question au clavier ou à la voix,
     le guide répond et propose « Suivre sur le plan » (guidage pas à pas existant).
     À inclure juste avant </body> dans scan-v2. Utilise, s'ils existent :
     QR_UUID, startNavigation(), speak(), voiceOn, SR (déjà définis dans scan-v2). --}}
<style>
    .ai-fab{position:fixed;right:16px;bottom:18px;z-index:40;display:flex;align-items:center;gap:8px;padding:12px 16px;border-radius:999px;border:none;background:var(--accent);color:var(--bg);font-family:var(--body);font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.3);cursor:pointer}
    .ai-fab svg{width:18px;height:18px}
    body:has(.nav.show) .ai-fab, body.ai-open .ai-fab{display:none}
    .ai-sheet{position:fixed;inset:auto 0 0 0;z-index:50;max-width:520px;margin:0 auto;height:min(78dvh,620px);background:var(--bg2);border:1px solid var(--border);border-radius:20px 20px 0 0;box-shadow:0 -10px 30px rgba(0,0,0,.35);display:none;flex-direction:column}
    body.ai-open .ai-sheet{display:flex;animation:aiUp .25s cubic-bezier(.16,1,.3,1)}
    @keyframes aiUp{from{transform:translateY(30px);opacity:0}to{transform:none;opacity:1}}
    .ai-hd{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border)}
    .ai-hd b{flex:1;font-family:var(--head);font-size:15px}
    .ai-hd small{display:block;font-family:var(--body);font-weight:400;color:var(--muted);font-size:11px}
    .ai-x{width:32px;height:32px;border-radius:9px;border:1px solid var(--border);background:var(--bg3);color:var(--text);cursor:pointer}
    .ai-log{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:10px;overscroll-behavior:contain}
    .ai-msg{max-width:88%;padding:10px 13px;border-radius:14px;font-size:14px;line-height:1.45;white-space:pre-wrap}
    .ai-msg.u{align-self:flex-end;background:var(--accent);color:var(--bg);border-bottom-right-radius:4px}
    .ai-msg.a{align-self:flex-start;background:var(--bg3);border:1px solid var(--border);border-bottom-left-radius:4px}
    .ai-msg.wait{color:var(--muted);font-style:italic}
    .ai-go{align-self:flex-start;margin-top:-2px;padding:9px 14px;border-radius:10px;border:1px solid var(--accent);background:var(--accent-soft);color:var(--accent-strong);font-weight:700;font-size:13px;cursor:pointer}
    .ai-sugg{display:flex;gap:8px;flex-wrap:wrap;padding:0 16px 10px}
    .ai-sugg button{padding:7px 11px;border-radius:999px;border:1px solid var(--border);background:var(--bg3);color:var(--text);font-size:12px;cursor:pointer}
    .ai-in{display:flex;gap:8px;padding:12px 16px calc(12px + env(safe-area-inset-bottom));border-top:1px solid var(--border)}
    .ai-in input{flex:1;min-width:0;background:var(--bg3);border:1px solid var(--border);border-radius:12px;padding:12px 13px;color:var(--text);font-family:var(--body);font-size:15px;outline:none}
    .ai-in input:focus{border-color:var(--accent)}
    .ai-in button{width:46px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center}
    .ai-in button.send{background:var(--accent);color:var(--bg);border-color:var(--accent)}
    .ai-in button.rec{border-color:var(--danger);color:var(--danger)}
    .ai-in svg{width:19px;height:19px}
</style>

<button class="ai-fab" id="aiFab" onclick="aiOpen()" aria-label="Demander au guide">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/></svg>
    Demander au guide
</button>

<div class="ai-sheet" id="aiSheet" role="dialog" aria-label="Guide QRMap">
    <div class="ai-hd">
        <b>Guide QRMap<small>Posez votre question, à l'écrit ou à la voix</small></b>
        <button class="ai-x" onclick="aiClose()" aria-label="Fermer">✕</button>
    </div>
    <div class="ai-log" id="aiLog"></div>
    <div class="ai-sugg" id="aiSugg">
        <button onclick="aiAsk(this.textContent)">Où sont les toilettes ?</button>
        <button onclick="aiAsk(this.textContent)">Je cherche l'accueil</button>
        <button onclick="aiAsk(this.textContent)">Où est la sortie ?</button>
    </div>
    <form class="ai-in" onsubmit="event.preventDefault();aiAsk(document.getElementById('aiInput').value)">
        <input id="aiInput" placeholder="Ex. : je dois aller en cardiologie" autocomplete="off" maxlength="500">
        <button type="button" id="aiMic" onclick="aiListen()" aria-label="Parler">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0M12 19v3"/></svg>
        </button>
        <button type="submit" class="send" aria-label="Envoyer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg>
        </button>
    </form>
</div>

<script>
(function(){
    const log=document.getElementById('aiLog');
    let history=[], busy=false, rec=null;
    const Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
    if(!Rec){ document.getElementById('aiMic').style.display='none'; }

    function bubble(cls,text){
        const d=document.createElement('div');
        d.className='ai-msg '+cls; d.textContent=text;   // textContent : jamais de HTML injecté
        log.appendChild(d); log.scrollTop=log.scrollHeight; return d;
    }

    window.aiOpen=function(){
        document.body.classList.add('ai-open');
        if(!log.children.length) bubble('a',"Bonjour ! Dites-moi où vous voulez aller, ou ce dont vous avez besoin. Je vous guide depuis votre position.");
        setTimeout(()=>document.getElementById('aiInput').focus(),250);
    };
    window.aiClose=function(){ document.body.classList.remove('ai-open'); };

    window.aiAsk=async function(text){
        text=(text||'').trim(); if(!text||busy) return;
        busy=true;
        document.getElementById('aiInput').value='';
        document.getElementById('aiSugg').style.display='none';
        bubble('u',text);
        history.push({role:'user',content:text});
        history=history.slice(-10);
        const wait=bubble('a wait','…');
        try{
            const res=await fetch(`/api/v1/scan/${QR_UUID}/guide`,{
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json'},
                body:JSON.stringify({messages:history}),
            });
            const data=await res.json().catch(()=>({}));
            wait.remove();
            const reply=data.reply||"Désolé, je n'ai pas compris. Pouvez-vous reformuler ?";
            bubble('a',reply);
            history.push({role:'assistant',content:reply});
            if(typeof voiceOn!=='undefined' && voiceOn && typeof speak==='function') speak(reply);
            if(data.route && data.route.destination_id && typeof startNavigation==='function'){
                const b=document.createElement('button');
                b.className='ai-go'; b.textContent='Suivre sur le plan →';
                b.onclick=()=>{ aiClose(); startNavigation(data.route.destination_id, data.route.label); };
                log.appendChild(b); log.scrollTop=log.scrollHeight;
            }
            try{ if(typeof trackEvent==='function') trackEvent('ai_guide_question'); }catch(e){}
        }catch(e){
            wait.remove();
            bubble('a',"Connexion impossible. Vous pouvez choisir votre destination dans la liste.");
        }finally{ busy=false; }
    };

    window.aiListen=function(){
        if(!Rec) return;
        const mic=document.getElementById('aiMic');
        if(rec){ rec.stop(); return; }
        rec=new Rec(); rec.lang=document.documentElement.lang==='en'?'en-US':'fr-FR'; rec.interimResults=false;
        mic.classList.add('rec');
        rec.onresult=e=>aiAsk(e.results[0][0].transcript);
        rec.onend=()=>{ mic.classList.remove('rec'); rec=null; };
        rec.onerror=()=>{ mic.classList.remove('rec'); rec=null; };
        rec.start();
    };
})();
</script>