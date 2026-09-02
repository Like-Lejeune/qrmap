{{-- resources/views/partials/room-types.blade.php --}}
{{--
    Catalogue PARTAGÉ des types de pièce : icône SVG + teinte + mots-clés.
    Inclus tel quel dans plan-editor ET dans les vues de scan pour garantir
    un rendu identique côté admin et côté utilisateur.

    Usage :  @include('partials.room-types')   (pose window.ROOM_TYPES)

    Chaque type : { key, label, tint, icon(SVG path/inner), kw:[mots-clés] }
    - tint  : couleur de remplissage douce de la pièce (fonctionne clair/sombre)
    - icon  : contenu INTÉRIEUR d'un <svg viewBox="0 0 24 24"> (paths only)
    - kw    : mots-clés pour deviner le type depuis le nom de la pièce
    L'A* n'utilise RIEN de tout ça : purement visuel.
--}}
<script>
window.ROOM_TYPES = (function(){
    // Icônes = inner-SVG (stroke currentColor), viewBox 0 0 24 24, trait fin.
    const I = {
        bed:      '<path d="M3 7v10M3 12h18v5M21 12v5M7 12V9a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/>',
        sofa:     '<path d="M4 10V7a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v3M3 10h18v6H3zM6 16v2M18 16v2"/>',
        kitchen:  '<path d="M6 3v6M4 3v3a2 2 0 0 0 2 2M8 3v3a2 2 0 0 1-2 2M6 9v12M14 3h4a1 1 0 0 1 1 1v6h-6V4a1 1 0 0 1 1-1zM16 14v7"/>',
        bath:     '<path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4zM6 12V5a2 2 0 0 1 2-2 2 2 0 0 1 2 2M8 22l-1 1M17 22l1 1"/>',
        wc:       '<path d="M7 4v6a3 3 0 0 0 3 3v0a3 3 0 0 0 3-3V4M10 16v4M8 20h4M17 4v16"/>',
        desk:     '<path d="M3 8h18M4 8v10M20 8v10M7 8V6a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2M8 18v2M16 18v2M8 13h8"/>',
        shop:     '<path d="M6 2 3 6v2a2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0V6l-3-4zM5 12v8h14v-8M9 20v-4h6v4"/>',
        food:     '<path d="M4 3v7a2 2 0 0 0 2 2h0V3M6 3v18M14 3c-1 2-1 5 0 7 1 2 1 5 0 11M18 3v18"/>',
        stairs:   '<path d="M4 20h4v-4h4v-4h4V8h4"/>',
        elevator: '<path d="M4 3h16v18H4zM12 3v18M8 8l1.5-2L11 8M13 16l1.5 2 1.5-2"/>',
        door:     '<path d="M5 21V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v17M5 21h14M13 12v1"/>',
        info:     '<path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 8h.01M11 12h1v5h1"/>',
        exit:     '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        meeting:  '<path d="M9 7a3 3 0 1 0 6 0 3 3 0 0 0-6 0zM3 21v-1a5 5 0 0 1 5-5h8a5 5 0 0 1 5 5v1"/>',
        parking:  '<path d="M6 21V4h7a4 4 0 0 1 0 8H6"/>',
        room:     '<path d="M4 4h16v16H4z"/>',
    };
    // tint : rgba douce, lisible en clair comme en sombre
    const T = {
        rest:  'rgba(120,140,255,.14)',   // repos / chambre
        live:  'rgba(255,190,90,.14)',    // séjour / salon
        cook:  'rgba(90,210,180,.16)',    // cuisine / cellier
        wet:   'rgba(90,180,255,.16)',    // sanitaires
        work:  'rgba(150,150,170,.14)',   // bureau / réunion
        shop:  'rgba(255,140,190,.14)',   // commerce
        food:  'rgba(255,160,90,.16)',    // restauration
        move:  'rgba(120,255,190,.10)',   // circulation
        care:  'rgba(255,110,110,.14)',   // médical
        util:  'rgba(180,180,180,.12)',   // technique / parking
        none:  'transparent',
    };
    return [
        {key:'chambre',   label:'Chambre',       tint:T.rest, icon:I.bed,      kw:['chambre','chb','bed','dortoir']},
        {key:'sejour',    label:'Séjour/Salon',  tint:T.live, icon:I.sofa,     kw:['séjour','sejour','salon','living','lounge']},
        {key:'cuisine',   label:'Cuisine',       tint:T.cook, icon:I.kitchen,  kw:['cuisine','kitchen','cellier','office']},
        {key:'bain',      label:'Salle de bain', tint:T.wet,  icon:I.bath,     kw:['bain','douche','salle d','sdb']},
        {key:'wc',        label:'Toilettes/WC',  tint:T.wet,  icon:I.wc,       kw:['wc','toilette','sanitaire','latrine']},
        {key:'bureau',    label:'Bureau',        tint:T.work, icon:I.desk,     kw:['bureau','office','open space','openspace']},
        {key:'reunion',   label:'Salle réunion', tint:T.work, icon:I.meeting,  kw:['réunion','reunion','meeting','conférence','conference','salle de r']},
        {key:'boutique',  label:'Boutique/Stand',tint:T.shop, icon:I.shop,     kw:['boutique','magasin','stand','shop','commerce','échoppe']},
        {key:'resto',     label:'Restauration',  tint:T.food, icon:I.food,     kw:['resto','restaurant','cafétéria','cafeteria','buvette','snack','food']},
        {key:'accueil',   label:'Accueil/Info',  tint:T.none, icon:I.info,     kw:['accueil','information','info','réception','reception']},
        {key:'escalier',  label:'Escalier',      tint:T.move, icon:I.stairs,   kw:['escalier','stair','marche']},
        {key:'ascenseur', label:'Ascenseur',     tint:T.move, icon:I.elevator, kw:['ascenseur','élévateur','elevateur','lift','elevator']},
        {key:'sortie',    label:'Sortie',        tint:T.move, icon:I.exit,     kw:['sortie','exit','issue','évacuation','evacuation']},
        {key:'medical',   label:'Médical',       tint:T.care, icon:I.info,     kw:['infirmerie','soin','médical','medical','urgence','cardio','pédiatrie','pediatrie','pharmacie','consultation','bloc']},
        {key:'parking',   label:'Parking',       tint:T.util, icon:I.parking,  kw:['parking','garage','stationnement']},
        {key:'porte',     label:'Porte',         tint:T.none, icon:I.door,     kw:['porte','entrée','entree','entrance']},
    ];
})();

// Devine le type d'une pièce : champ explicite room.kind sinon mots-clés du nom.
window.roomKind = function(room){
    if(room && room.kind){
        const found = window.ROOM_TYPES.find(t=>t.key===room.kind);
        if(found) return found;
    }
    const n = ((room && room.name) || '').toLowerCase();
    for(const t of window.ROOM_TYPES){
        if(t.kw.some(k=>n.includes(k))) return t;
    }
    return null; // pas de type reconnu → rendu neutre (comme avant)
};
</script>