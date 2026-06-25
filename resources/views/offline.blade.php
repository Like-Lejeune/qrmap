<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4fffb0">
    <title>Hors ligne — QRMap</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:#0d0f14; color:#f0f2f5; font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; text-align:center; padding:2rem; }
        .logo { font-family:'Syne',sans-serif; font-weight:800; font-size:1.5rem; color:#4fffb0; margin-bottom:2rem; display:flex; align-items:center; justify-content:center; gap:10px; }
        .logo::before { content:''; width:12px; height:12px; background:#4fffb0; border-radius:2px; transform:rotate(45deg); display:inline-block; }
        h1 { font-family:'Syne',sans-serif; font-size:2.5rem; font-weight:700; margin-bottom:1rem; }
        h1 span { color:#4fffb0; }
        p { color:#8892a4; font-size:15px; line-height:1.7; max-width:400px; margin:0 auto 2rem; }
        .btn { display:inline-block; padding:12px 24px; background:#4fffb0; color:#0d0f14; border-radius:8px; font-weight:600; text-decoration:none; font-size:14px; cursor:pointer; border:none; }
        .btn:hover { background:#3de89d; }
        .icon { font-size:4rem; margin-bottom:1.5rem; display:block; }
    </style>
</head>
<body>
    <div>
        <div class="logo">QRMap</div>
        <span class="icon">📡</span>
        <h1>Hors <span>ligne</span></h1>
        <p>Vous n'êtes pas connecté à Internet. Les plans déjà consultés restent disponibles en cache.</p>
        <button class="btn" onclick="window.location.reload()">Réessayer</button>
    </div>
</body>
</html>