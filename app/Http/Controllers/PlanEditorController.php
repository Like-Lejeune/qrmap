<?php
// app/Http/Controllers/PlanEditorController.php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\QrCode;
use App\Models\Site;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanEditorController extends Controller
{
    /**
     * Liste des plans (éditeur visuel), intégrée au layout.
     * GET /admin/plan-editor
     */
    public function index()
    {
        $sites = Site::withCount('buildings')
            ->with(['buildings' => fn($q) => $q->withCount('floors')])
            ->latest()
            ->get()
            ->map(function (Site $site) {
                $building = $site->buildings->first();
                $floorsCount = $site->buildings->sum(fn($b) => $b->floors_count);

                $floorIds = $site->buildings->flatMap(fn($b) => $b->floors->pluck('id'));
                if ($floorIds->isEmpty() && $building) {
                    $floorIds = $building->floors()->pluck('id');
                }

                return [
                    'id'            => $site->id,
                    'name'          => $site->name,
                    'address'       => $site->address,
                    'building'      => $building?->name,
                    'floors'        => $floorsCount,
                    'destinations'  => Destination::whereIn('floor_id', $floorIds)->count(),
                    'qrcodes'       => QrCode::whereIn('floor_id', $floorIds)->count(),
                    'edit_url'      => route('plan-editor.edit', $site->id),
                    'duplicate_url' => route('plan-editor.duplicate', $site->id),
                    'destroy_url'   => route('plan-editor.destroy', $site->id),
                ];
            });

        return view('admin.plan-list', [
            'sites'       => $sites,
            'createUrl'   => route('plan-editor.create'),
            'importUrl'   => route('plan-editor.import'),
            'siteOptions' => $sites->map(fn($s) => ['id' => $s['id'], 'name' => $s['name']])->values(),
        ]);
    }

    /**
     * Affiche l'éditeur en mode CRÉATION (plan vierge).
     * GET /admin/plan-editor/create
     */
    public function create()
    {
        return view('admin.plan-editor', [
            'initial' => null,
            'siteId'  => null,
        ]);
    }

    /**
     * Affiche l'éditeur en mode ÉDITION, pré-chargé avec un site existant.
     * GET /admin/plan-editor/{site}/edit
     */
    public function edit(Site $site)
    {
        return view('admin.plan-editor', [
            'initial' => $this->siteToEditorPayload($site),
            'siteId'  => $site->id,
        ]);
    }

    /**
     * Crée un site complet à partir du plan de l'éditeur.
     * POST /admin/plan-editor
     */
    public function store(Request $request, QrCodeService $service): JsonResponse
    {
        $data = $this->validatePayload($request);
        $result = $this->persistNewSite($data, $service);

        return response()->json([
            'site_id'  => $result['site']->id,
            'qrcodes'  => $result['qrs'],
            'message'  => 'Plan enregistré',
        ], 201);
    }

    /**
     * Importe un plan depuis un fichier JSON exporté par l'éditeur (autonome ou intégré).
     * Accepte un upload de fichier (champ "file") OU du JSON collé (champ "json").
     * POST /admin/plan-editor/import
     */
    public function import(Request $request, QrCodeService $service)
    {
        // 1) Récupérer le contenu JSON (fichier uploadé prioritaire, sinon texte collé)
        $raw = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (!$file->isValid()) {
                return $this->importError($request, 'Fichier invalide.');
            }
            if (strtolower($file->getClientOriginalExtension()) !== 'json') {
                return $this->importError($request, 'Le fichier doit être un .json.');
            }
            $raw = file_get_contents($file->getRealPath());
        } elseif ($request->filled('json')) {
            $raw = $request->input('json');
        }

        if (!$raw) {
            return $this->importError($request, 'Aucun plan fourni.');
        }

        // 2) Décoder
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $this->importError($request, 'JSON illisible : ' . json_last_error_msg());
        }

        // 3) Valider avec les mêmes règles que la sauvegarde de l'éditeur
        $validator = validator($data, $this->rules());
        if ($validator->fails()) {
            return $this->importError($request, 'Plan invalide : ' . $validator->errors()->first());
        }
        $valid = $validator->validated();

        // Réinjecte les pièces brutes (shape/points) que validated() a retirées
        foreach ($valid['floors'] as $i => &$f) {
            if (isset($data['floors'][$i]['rooms'])) {
                $f['rooms'] = $data['floors'][$i]['rooms'];
            }
        }
        unset($f);

        // 4a) Import DANS un site existant → mise à jour (préserve les QR existants)
        if ($request->filled('target_site_id')) {
            $target = Site::find($request->input('target_site_id'));
            if (!$target) {
                return $this->importError($request, 'Site cible introuvable.');
            }

            $service2 = $service;
            DB::transaction(function () use ($target, $valid, $service2) {
                $target->update([
                    'name'        => $valid['site']['name'],
                    'address'     => $valid['site']['address'] ?? null,
                    'latitude'    => $valid['site']['latitude'] ?: null,
                    'longitude'   => $valid['site']['longitude'] ?: null,
                    'description' => $valid['site']['description'] ?? null,
                ]);

                $building = $target->buildings()->first()
                    ?? $target->buildings()->create([
                        'name' => $valid['building']['name'],
                        'floors_count' => count($valid['floors']),
                    ]);

                $building->update([
                    'name'         => $valid['building']['name'],
                    'floors_count' => count($valid['floors']),
                    'model_3d'     => $valid['building']['model_3d'] ?? null,
                ]);

                // false = mode mise à jour : réécrit étages/destinations, préserve les QR par db_id
                $this->syncFloorsAndContent($building, $valid, $service2, false);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'site_id'  => $target->id,
                    'edit_url' => route('plan-editor.edit', $target->id),
                    'message'  => 'Plan importé dans le site existant',
                ]);
            }
            return redirect()->route('plan-editor.edit', $target->id)
                ->with('success', 'Plan importé dans le site existant.');
        }

        // 4b) Import comme NOUVEAU site
        $result = $this->persistNewSite($valid, $service);

        // Réponse : JSON pour fetch, redirection pour soumission de formulaire classique
        if ($request->expectsJson()) {
            return response()->json([
                'site_id'  => $result['site']->id,
                'edit_url' => route('plan-editor.edit', $result['site']->id),
                'message'  => 'Plan importé',
            ], 201);
        }

        return redirect()
            ->route('plan-editor.edit', $result['site']->id)
            ->with('success', 'Plan importé avec succès.');
    }

    private function importError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }
        return back()->withErrors(['file' => $message]);
    }

    /**
     * Crée un site complet (Site → Bâtiment → Étages → Destinations → QR) dans une transaction.
     */
    private function persistNewSite(array $data, QrCodeService $service): array
    {
        return DB::transaction(function () use ($data, $service) {
            $site = Site::create([
                'name'        => $data['site']['name'],
                'address'     => $data['site']['address'] ?? null,
                'latitude'    => $data['site']['latitude'] ?: null,
                'longitude'   => $data['site']['longitude'] ?: null,
                'description' => $data['site']['description'] ?? null,
            ]);

            $building = $site->buildings()->create([
                'name'         => $data['building']['name'],
                'floors_count' => count($data['floors']),
                'model_3d'     => $data['building']['model_3d'] ?? null,
            ]);

            $qrs = $this->syncFloorsAndContent($building, $data, $service, true);

            return ['site' => $site, 'qrs' => $qrs];
        });
    }

    /**
     * Met à jour un site existant à partir du plan de l'éditeur.
     * PUT /admin/plan-editor/{site}
     */
    public function update(Request $request, Site $site, QrCodeService $service): JsonResponse
    {
        $data = $this->validatePayload($request);

        $result = DB::transaction(function () use ($site, $data, $service) {
            $site->update([
                'name'        => $data['site']['name'],
                'address'     => $data['site']['address'] ?? null,
                'latitude'    => $data['site']['latitude'] ?: null,
                'longitude'   => $data['site']['longitude'] ?: null,
                'description' => $data['site']['description'] ?? null,
            ]);

            // On travaille sur le premier bâtiment du site (1 bâtiment par plan ici)
            $building = $site->buildings()->first()
                ?? $site->buildings()->create(['name' => $data['building']['name'], 'floors_count' => count($data['floors'])]);

            $building->update([
                'name'         => $data['building']['name'],
                'floors_count' => count($data['floors']),
                'model_3d'     => $data['building']['model_3d'] ?? null,
            ]);

            $qrs = $this->syncFloorsAndContent($building, $data, $service, false);

            return ['qrs' => $qrs];
        });

        return response()->json([
            'site_id' => $site->id,
            'qrcodes' => $result['qrs'],
            'message' => 'Plan mis à jour',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Suppression / duplication
    // ──────────────────────────────────────────────────────────────────

    /**
     * Supprime un site et tout son contenu (bâtiments, étages, destinations, QR).
     * DELETE /admin/plan-editor/{site}
     */
    public function destroy(Site $site): JsonResponse
    {
        DB::transaction(function () use ($site) {
            foreach ($site->buildings as $building) {
                foreach ($building->floors as $floor) {
                    $floor->destinations()->delete();

                    // Purge des logs de scan liés (évite l'erreur de contrainte FK)
                    $qrIds = QrCode::where('floor_id', $floor->id)->pluck('id');
                    if ($qrIds->isNotEmpty() && class_exists(\App\Models\ScanLog::class)) {
                        \App\Models\ScanLog::whereIn('qr_code_id', $qrIds)->delete();
                    }

                    QrCode::where('floor_id', $floor->id)->delete();
                }
                $building->floors()->delete();
            }
            $site->buildings()->delete();
            $site->delete();
        });

        return response()->json(['message' => 'Site supprimé']);
    }

    /**
     * Duplique un site (plan + destinations) sous un nouveau nom.
     * Les QR ne sont PAS copiés : ils devront être (re)générés depuis l'éditeur,
     * pour éviter des UUID en double pointant vers des emplacements physiques distincts.
     * POST /admin/plan-editor/{site}/duplicate
     */
    public function duplicate(Site $site): JsonResponse
    {
        $newSite = DB::transaction(function () use ($site) {
            $copy = Site::create([
                'name'        => $site->name . ' (copie)',
                'address'     => $site->address,
                'latitude'    => $site->latitude,
                'longitude'   => $site->longitude,
                'description' => $site->description,
            ]);

            foreach ($site->buildings as $building) {
                $newBuilding = $copy->buildings()->create([
                    'name'         => $building->name,
                    'floors_count' => $building->floors_count,
                    'model_3d'     => $building->model_3d,
                ]);

                foreach ($building->floors as $floor) {
                    $newFloor = $newBuilding->floors()->create([
                        'level' => $floor->level,
                        'label' => $floor->label,
                        'rooms' => $floor->rooms,
                    ]);

                    foreach ($floor->destinations as $d) {
                        $newFloor->destinations()->create([
                            'room_id'     => $d->room_id,
                            'label'       => $d->label,
                            'category'    => $d->category,
                            'description' => $d->description,
                            'sort_order'  => $d->sort_order,
                            'is_active'   => $d->is_active,
                        ]);
                    }
                }
            }

            return $copy;
        });

        return response()->json([
            'site_id'  => $newSite->id,
            'edit_url' => route('plan-editor.edit', $newSite->id),
            'message'  => 'Site dupliqué',
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Recrée étages + destinations et synchronise les QR codes.
     * Les QR existants (db_id) sont détachés temporairement pour permettre la
     * suppression des anciens étages, puis réaffectés au nouvel étage.
     * Les QR retirés côté éditeur (et ceux sans db_id) sont supprimés avec leurs scans.
     */
       private function syncFloorsAndContent($building, array $data, QrCodeService $service, bool $isCreate): array
    {
        // ── 1. Étages : mise à jour en place, indexés par niveau ───────────
        $floorsByLevel = [];
        $keptFloorIds  = [];
 
        foreach ($data['floors'] as $f) {
            $level = (int) $f['level'];
 
            // updateOrCreate : conserve l'ID si l'étage existe déjà
            $floor = $building->floors()->updateOrCreate(
                ['level' => $level],
                [
                    'label' => $f['label'] ?? ('Niveau ' . $level),
                    'rooms' => $f['rooms'] ?? [],   // conserve shape/points/door
                ]
            );
 
            $floorsByLevel[$level] = $floor;
            $keptFloorIds[]        = $floor->id;
        }
 
        // ── 2. Étages supprimés côté éditeur ──────────────────────────────
        if (!$isCreate) {
            $staleFloors = $building->floors()
                ->whereNotIn('id', $keptFloorIds)
                ->get();
 
            foreach ($staleFloors as $stale) {
                // Les QR de cet étage disparaissent avec lui
                $qrIds = QrCode::where('floor_id', $stale->id)->pluck('id');
                if ($qrIds->isNotEmpty()) {
                    if (class_exists(\App\Models\ScanLog::class)) {
                        \App\Models\ScanLog::whereIn('qr_code_id', $qrIds)->delete();
                    }
                    QrCode::whereIn('id', $qrIds)->delete();
                }
                $stale->destinations()->delete();
                $stale->delete();
            }
        }
 
        $building->load('floors');
 
        // ── 3. Destinations : mise à jour en place (IDs préservés) ─────────
        $keptDestIds = [];
 
        foreach ($data['destinations'] ?? [] as $d) {
            $floor = $floorsByLevel[$d['floor_level']] ?? null;
            if (!$floor) continue;
 
            $attrs = [
                'room_id'     => $d['room_id'] ?? null,
                'label'       => $d['label'],
                'category'    => $d['category'] ?? 'service',
                'description' => $d['description'] ?? null,
                'sort_order'  => $d['sort_order'] ?? 0,
                'is_active'   => $d['is_active'] ?? true,
            ];
 
            $existing = null;
 
            // a) L'éditeur transmet l'ID en base : correspondance certaine
            if (!empty($d['db_id'])) {
                $existing = Destination::where('id', $d['db_id'])
                    ->where('floor_id', $floor->id)
                    ->first();
            }
 
            // b) Repli : clé métier stable (étage + pièce visée)
            if (!$existing && !empty($d['room_id'])) {
                $existing = $floor->destinations()
                    ->where('room_id', $d['room_id'])
                    ->whereNotIn('id', $keptDestIds ?: [0])
                    ->first();
            }
 
            if ($existing) {
                $existing->update($attrs);   // ← l'ID ne change PAS
                $dest = $existing;
            } else {
                $dest = $floor->destinations()->create($attrs);
            }
 
            $keptDestIds[] = $dest->id;
        }
 
        // ── 4. Destinations retirées côté éditeur ─────────────────────────
        // (leurs NavigationPath tombent en cascade, ce qui est voulu ici :
        //  la destination n'existe plus)
        if (!$isCreate) {
            Destination::whereIn('floor_id', $keptFloorIds)
                ->when(!empty($keptDestIds), fn($q) => $q->whereNotIn('id', $keptDestIds))
                ->delete();
        }
 
        // ── 5. QR codes : réaffecter les existants, générer les nouveaux ───
        $keepQrIds = collect($data['qrcodes'] ?? [])->pluck('db_id')->filter()->all();
 
        if (!$isCreate) {
            // Supprimer les QR retirés côté éditeur (+ leurs logs de scan)
            $toDelete = QrCode::whereIn('floor_id', $keptFloorIds)
                ->when(!empty($keepQrIds), fn($q) => $q->whereNotIn('id', $keepQrIds))
                ->pluck('id');
 
            if ($toDelete->isNotEmpty()) {
                if (class_exists(\App\Models\ScanLog::class)) {
                    \App\Models\ScanLog::whereIn('qr_code_id', $toDelete)->delete();
                }
                QrCode::whereIn('id', $toDelete)->delete();
            }
        }
 
        $out = [];
        foreach ($data['qrcodes'] ?? [] as $q) {
            $floor = $floorsByLevel[$q['floor_level']] ?? null;
            if (!$floor) continue;
 
            $existing = !empty($q['db_id']) ? QrCode::find($q['db_id']) : null;
 
            if ($existing) {
                $existing->update([
                    'floor_id'       => $floor->id,
                    'location_label' => $q['label'],
                    'pos_x'          => $q['pos_x'],
                    'pos_y'          => $q['pos_y'],
                ]);
                $qr = $existing;
            } else {
                $res = $service->generate([
                    'site_id'        => $building->site_id,
                    'building_id'    => $building->id,
                    'floor_id'       => $floor->id,
                    'location_label' => $q['label'],
                    'pos_x'          => $q['pos_x'],
                    'pos_y'          => $q['pos_y'],
                ]);
                $qr = $res['qr_code'];
            }
 
            $out[] = [
                'id'          => $qr->id,
                'uuid'        => $qr->uuid,
                'floor_level' => $q['floor_level'],
                'label'       => $q['label'],
            ];
        }
 
        return $out;
    }

    /**
     * Transforme un Site en structure attendue par l'éditeur (même format que l'export JSON).
     */
    private function siteToEditorPayload(Site $site): array
    {
        $building = $site->buildings()->with(['floors.destinations'])->first();

        $floors = [];
        $destinations = [];
        $qrcodes = [];

        if ($building) {
            foreach ($building->floors->sortBy('level') as $floor) {
                $floors[] = [
                    'level' => $floor->level,
                    'label' => $floor->label,
                    'rooms' => $floor->rooms ?? [],   // renvoie shape/points tels quels
                ];
                foreach ($floor->destinations as $d) {
                    $destinations[] = [
                        'floor_level' => $floor->level,
                        'room_id'     => $d->room_id,
                        'label'       => $d->label,
                        'category'    => $d->category,
                        'description' => $d->description,
                        'sort_order'  => $d->sort_order,
                        'is_active'   => (bool) $d->is_active,
                    ];
                }
                foreach (QrCode::where('floor_id', $floor->id)->get() as $q) {
                    $qrcodes[] = [
                        'db_id'       => $q->id,
                        'floor_level' => $floor->level,
                        'label'       => $q->location_label,
                        'pos_x'       => $q->pos_x,
                        'pos_y'       => $q->pos_y,
                    ];
                }
            }
        }

        return [
            'site' => [
                'name'        => $site->name,
                'address'     => $site->address,
                'latitude'    => $site->latitude,
                'longitude'   => $site->longitude,
                'description' => $site->description,
            ],
            'building' => [
                'name'     => $building?->name ?? '',
                'model_3d' => $building?->model_3d ?? ['width_m' => 40, 'depth_m' => 25, 'floor_height_m' => 3.3],
            ],
            'floors'       => $floors,
            'destinations' => $destinations,
            'qrcodes'      => $qrcodes,
        ];
    }

    /**
     * Validation du payload de l'éditeur (depuis une requête),
     * puis réinjection des pièces brutes (shape/points) que validate() retire.
     */
    private function validatePayload(Request $request): array
    {
        $data = $request->validate($this->rules());

        $rawFloors = $request->input('floors', []);
        foreach ($data['floors'] as $i => &$f) {
            if (isset($rawFloors[$i]['rooms'])) {
                $f['rooms'] = $rawFloors[$i]['rooms'];
            }
        }
        unset($f);

        return $data;
    }

    /**
     * Règles de validation partagées (sauvegarde éditeur + import fichier).
     */
    private function rules(): array
    {
        return [
            'target_site_id'          => 'nullable|integer|exists:sites,id',
            'site'                    => 'required|array',
            'site.name'               => 'required|string|max:255',
            'site.address'            => 'nullable|string|max:255',
            'site.latitude'           => 'nullable|numeric',
            'site.longitude'          => 'nullable|numeric',
            'site.description'        => 'nullable|string',

            'building'                => 'required|array',
            'building.name'           => 'required|string|max:255',
            'building.model_3d'       => 'nullable|array',
            

            'floors'                  => 'required|array|min:1',
            'floors.*.level'          => 'required|integer',
            'floors.*.label'          => 'nullable|string|max:255',
            'floors.*.rooms'          => 'array',
            'floors.*.rooms.*.id'     => 'nullable|string|max:64',
            'floors.*.rooms.*.name'   => 'nullable|string|max:255',
            'floors.*.rooms.*.x'      => 'required|numeric',
            'floors.*.rooms.*.y'      => 'required|numeric',
            'floors.*.rooms.*.w'      => 'required|numeric',
            'floors.*.rooms.*.h'      => 'required|numeric',
            'floors.*.rooms.*.shape'    => 'nullable|in:rect,circle,poly',
            'floors.*.rooms.*.points'   => 'nullable|array',
            'floors.*.rooms.*.points.*' => 'array',
            

            // Porte d'une pièce (optionnelle) — coordonnées en %
            'floors.*.rooms.*.door'   => 'nullable|array',
            'floors.*.rooms.*.door.x' => 'nullable|numeric|min:0|max:100',
            'floors.*.rooms.*.door.y' => 'nullable|numeric|min:0|max:100',

            'destinations'            => 'array',
            'destinations.*.db_id'   => 'nullable|integer',
            'destinations.*.floor_level' => 'required|integer',
            'destinations.*.room_id'  => 'nullable|string|max:64',
            'destinations.*.label'    => 'required|string|max:255',
            'destinations.*.category' => 'nullable|string|max:32',
            'destinations.*.description' => 'nullable|string',
            'destinations.*.sort_order'  => 'nullable|integer',

            'qrcodes'                 => 'array',
            'qrcodes.*.db_id'         => 'nullable|integer',
            'qrcodes.*.floor_level'   => 'required|integer',
            'qrcodes.*.label'         => 'required|string|max:255',
            'qrcodes.*.pos_x'         => 'required|numeric',
            'qrcodes.*.pos_y'         => 'required|numeric',
        ];
    }
}