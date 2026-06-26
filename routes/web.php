<?php
// =============================================
// routes/web.php  — VERSION FINALE COMPLÈTE
// =============================================
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\PlanEditorController;
use App\Http\Controllers\NavigationPathController;
use Illuminate\Support\Facades\Route;
 

// ── PUBLIC ───────────────────────────────
// Page d'accueil = vitrine (accessible à tous)
//Route::get('/', fn() => view('vitrine'))->name('home');
Route::get('/pitch', function () {
    return response()->file(public_path('pitch.html'));
});
Route::view('/', 'launch')->name('launch');
Route::get('/', fn() => view('launch', ['demoUrl' => route('demo')]));
Route::get('/demo', fn() => view('demo'))->name('demo');

// ── AUTHENTIFICATION ──────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── PUBLIC (sans auth) ───────────────────────────
Route::get('/scan/{uuid}', [ScanController::class, 'show'])->name('scan.show');
Route::get('/offline',     fn() => view('offline'))->name('offline');
 
// ── ADMINISTRATION (auth requise) ────────────────
Route::middleware('auth')->group(function () {
 
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
 
    // Recherche globale
    Route::get('/search', [SearchController::class, 'index'])->name('search');
 
    // Sites
    // Route::resource('sites', SiteController::class);

    Route::resource('buildings/{building}/floors/{floor}/destinations',
    DestinationController::class)->names('buildings.floors.destinations');
 
    // Bâtiments
    Route::get('sites/{site}/buildings/create',
        [BuildingController::class, 'create'])->name('sites.buildings.create');
    Route::post('sites/{site}/buildings',
        [BuildingController::class, 'store'])->name('sites.buildings.store');
    Route::get('sites/{site}/buildings/{building}/edit',
        [BuildingController::class, 'edit'])->name('sites.buildings.edit');
    Route::put('sites/{site}/buildings/{building}',
        [BuildingController::class, 'update'])->name('sites.buildings.update');
    Route::delete('sites/{site}/buildings/{building}',
        [BuildingController::class, 'destroy'])->name('sites.buildings.destroy');
    Route::get('buildings/{building}',
        [BuildingController::class, 'show'])->name('buildings.show');
 
    // Étages
    Route::get('buildings/{building}/floors/import',
        [FloorController::class, 'import'])->name('buildings.floors.import');
    Route::get('buildings/{building}/floors/create',
        [FloorController::class, 'create'])->name('buildings.floors.create');
    Route::post('buildings/{building}/floors',
        [FloorController::class, 'store'])->name('buildings.floors.store');
    Route::get('buildings/{building}/floors/{floor}/edit',
        [FloorController::class, 'edit'])->name('buildings.floors.edit');
    Route::put('buildings/{building}/floors/{floor}',
        [FloorController::class, 'update'])->name('buildings.floors.update');
    Route::delete('buildings/{building}/floors/{floor}',
        [FloorController::class, 'destroy'])->name('buildings.floors.destroy');
 
    // QR Codes
    Route::get('qrcodes/print',
        [QrCodeController::class, 'print'])->name('qrcodes.print');
    Route::resource('qrcodes', QrCodeController::class)->except(['edit', 'update']);
 
    // Statistiques
    Route::get('stats',             [StatsController::class, 'index'])->name('stats.index');
    Route::get('stats/qr/{qrcode}', [StatsController::class, 'qrcode'])->name('stats.qrcode');
 
    // Exports CSV
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('qrcodes',        [ExportController::class, 'qrCodesCsv'])->name('qrcodes');
        Route::get('scanlogs',       [ExportController::class, 'scanLogsCsv'])->name('scanlogs');
        Route::get('sites/{site}',   [ExportController::class, 'siteCsv'])->name('site');
    });

        // Affichage de l'éditeur
        Route::get('/plan-editor',              [PlanEditorController::class, 'index'])->name('plan-editor.index');
        Route::get('/plan-editor/create',       [PlanEditorController::class, 'create'])->name('plan-editor.create');
        Route::get('/plan-editor/{site}/edit',  [PlanEditorController::class, 'edit'])->name('plan-editor.edit');

        Route::post('/plan-editor',             [PlanEditorController::class, 'store'])->name('plan-editor.store');
        Route::put('/plan-editor/{site}',       [PlanEditorController::class, 'update'])->name('plan-editor.update');

        Route::post('/plan-editor/import',      [PlanEditorController::class, 'import'])->name('plan-editor.import');

        Route::delete('/plan-editor/{site}',            [PlanEditorController::class, 'destroy'])->name('plan-editor.destroy');
        Route::post('/plan-editor/{site}/duplicate',    [PlanEditorController::class, 'duplicate'])->name('plan-editor.duplicate');
  
        Route::get('/paths/{site}/editor', [NavigationPathController::class, 'editor'])->name('paths.editor');
        Route::get('/paths/compute',       [NavigationPathController::class, 'compute'])->name('paths.compute');
        Route::post('/paths',              [NavigationPathController::class, 'store'])->name('paths.store');
        Route::delete('/paths',            [NavigationPathController::class, 'destroy'])->name('paths.destroy');
 
 
});