# QRMap — Cartographie de bâtiments par QR Code
## Guide d'installation complet

---

## Prérequis
- PHP 8.1+
- Composer
- Laravel 10 ou 11
- MySQL / PostgreSQL / SQLite
- Extension PHP GD (pour la génération des QR codes)

---

## Installation pas à pas

### 1. Créer le projet Laravel
```bash
composer create-project laravel/laravel qrmap
cd qrmap
```

### 2. Installer les dépendances
```bash
# Générateur de QR codes
composer require simplesoftwareio/simple-qrcode

# Vérifier que l'extension GD est activée
php -m | grep gd
```

### 3. Configurer la base de données
```env
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qrmap
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
```

### 4. Créer les fichiers

Copiez tous les fichiers fournis dans les bons dossiers :

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── SiteController.php
│   ├── BuildingController.php
│   ├── FloorController.php
│   ├── QrCodeController.php
│   ├── ScanController.php
│   └── Api/
│       └── BuildingApiController.php
├── Models/
│   ├── Site.php
│   ├── Building.php
│   ├── Floor.php
│   └── QrCode.php
└── Services/
    └── QrCodeService.php

resources/views/
├── layouts/app.blade.php
├── dashboard.blade.php
├── sites/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── form.blade.php
├── buildings/
│   ├── show.blade.php
│   └── form.blade.php
├── floors/
│   └── form.blade.php
├── qrcodes/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
└── scan/
    └── show.blade.php

routes/
├── web.php
└── api.php
```

### 5. Créer les migrations

```bash
php artisan make:migration create_sites_table
php artisan make:migration create_buildings_table
php artisan make:migration create_floors_table
php artisan make:migration create_qr_codes_table
```
Copiez le contenu des migrations depuis `routes_and_migrations.php`

### 6. Lancer les migrations
```bash
php artisan migrate
```

### 7. Créer le lien storage
```bash
php artisan storage:link
```

### 8. Enregistrer le service QrCodeService dans AppServiceProvider
```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $app->bind(\App\Services\QrCodeService::class);
}
```

### 9. Lancer le serveur
```bash
php artisan serve
# Ouvrez : http://localhost:8000
```

---

## Utilisation

### Flux de travail complet

1. **Créer un site** → `/sites/create`
   - Renseignez le nom, l'adresse, les coordonnées GPS

2. **Ajouter un bâtiment** → Dans la page du site, cliquez "+ Bâtiment"
   - Définissez les dimensions (largeur, profondeur, hauteur par étage)
   - Ces dimensions génèrent automatiquement la vue 3D

3. **Configurer les étages** → Dans la page du bâtiment, cliquez "+ Étage"
   - Dessinez les pièces avec l'éditeur visuel (positions en %)
   - Ou collez un SVG personnalisé pour un plan précis

4. **Générer les QR Codes** → `/qrcodes/create`
   - Associez à un site, bâtiment et étage
   - Définissez la position (X%, Y%) sur le plan
   - Téléchargez le PNG et imprimez

5. **Tester le scan** → Cliquez "Tester" sur un QR code
   - Vue plan 2D avec marqueur de votre position
   - Vue 3D interactive du bâtiment (THREE.js)
   - Informations complètes du lieu

---

## Structure de la page de scan (vue publique)

La page `/scan/{uuid}` est la vue destinée aux utilisateurs finaux (via leur téléphone) :

- **Plan 2D** : plan de l'étage avec votre position marquée en vert
- **Vue 3D** : rendu THREE.js du bâtiment complet, étage courant mis en valeur
- **Infos** : adresse, coordonnées GPS, statistiques de fréquentation

---

## Données de test rapide (Seeder optionnel)

```php
// database/seeders/DemoSeeder.php
use App\Models\Site;
use App\Models\Building;
use App\Models\Floor;

$site = Site::create([
    'name' => 'Campus de démonstration',
    'address' => 'Yaoundé, Cameroun',
    'latitude' => 3.8667, 'longitude' => 11.5167,
]);

$building = $site->buildings()->create([
    'name' => 'Bâtiment Principal',
    'floors_count' => 3,
    'model_3d' => ['width_m' => 40, 'depth_m' => 25, 'floor_height_m' => 3.5],
]);

$building->floors()->create([
    'level' => 0, 'label' => 'Rez-de-chaussée',
    'rooms' => [
        ['id' => 'R1', 'name' => 'Accueil',   'x' => 5,  'y' => 5,  'w' => 25, 'h' => 40],
        ['id' => 'R2', 'name' => 'Cafétéria', 'x' => 35, 'y' => 5,  'w' => 30, 'h' => 40],
        ['id' => 'R3', 'name' => 'Salle A',   'x' => 70, 'y' => 5,  'w' => 25, 'h' => 40],
        ['id' => 'R4', 'name' => 'Couloir',   'x' => 5,  'y' => 50, 'w' => 90, 'h' => 15],
        ['id' => 'R5', 'name' => 'Bureaux',   'x' => 5,  'y' => 70, 'w' => 55, 'h' => 25],
        ['id' => 'R6', 'name' => 'Archives',  'x' => 65, 'y' => 70, 'w' => 30, 'h' => 25],
    ]
]);
```

```bash
php artisan db:seed --class=DemoSeeder
```

---

## Évolutions possibles

| Fonctionnalité | Description |
|---|---|
| Auth | Ajouter `laravel/breeze` pour protéger l'admin |
| Import DWG/PDF | Convertir des plans architecturaux en SVG |
| Modèle glTF | Charger de vrais modèles 3D via URL |
| Multi-marqueur | Afficher plusieurs QR codes sur le même plan |
| Historique | Logger chaque scan avec heure et user-agent |
| PWA | Transformer la page scan en Progressive Web App |
| Géolocalisation | Comparaison GPS avec la position QR |