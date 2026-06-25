<?php

// =============================================
// app/Http/Controllers/BuildingController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\Site;
use App\Models\Building;
use Illuminate\Http\Request;
 
class BuildingController extends Controller
{
    public function create(Site $site)
    {
        return view('buildings.form', compact('site'));
    }
 
    public function store(Request $request, Site $site)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'floors_count'  => 'required|integer|min:1',
            'floor_plan_2d' => 'nullable|string',
            'width_m'       => 'nullable|numeric',
            'depth_m'       => 'nullable|numeric',
            'floor_height_m'=> 'nullable|numeric',
            'model_3d_url'  => 'nullable|url',
        ]);
 
        $model3d = array_filter([
            'width_m'       => $request->width_m,
            'depth_m'       => $request->depth_m,
            'floor_height_m'=> $request->floor_height_m ?? 3,
            'url'           => $request->model_3d_url,
        ]);
 
        $plan2d = null;
        if ($request->floor_plan_2d) {
            $raw = trim($request->floor_plan_2d);
            $plan2d = str_starts_with($raw, '<svg') ? ['svg' => $raw] : (json_decode($raw, true) ?? ['svg' => $raw]);
        }
 
        $building = $site->buildings()->create([
            'name'          => $data['name'],
            'floors_count'  => $data['floors_count'],
            'floor_plan_2d' => $plan2d,
            'model_3d'      => !empty($model3d) ? $model3d : null,
        ]);
 
        return redirect()->route('buildings.show', $building)->with('success', 'Bâtiment créé.');
    }
 
    public function show(Building $building)
    {
        $building->load(['site', 'floors.qrCodes', 'qrCodes.floor']);
        return view('buildings.show', compact('building'));
    }
 
    public function edit(Site $site, Building $building)
    {
        return view('buildings.form', compact('site', 'building'));
    }
 
    public function update(Request $request, Site $site, Building $building)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'floors_count'  => 'required|integer|min:1',
            'floor_plan_2d' => 'nullable|string',
            'width_m'       => 'nullable|numeric',
            'depth_m'       => 'nullable|numeric',
            'floor_height_m'=> 'nullable|numeric',
            'model_3d_url'  => 'nullable|url',
        ]);
 
        $model3d = array_filter([
            'width_m'        => $request->width_m,
            'depth_m'        => $request->depth_m,
            'floor_height_m' => $request->floor_height_m ?? 3,
            'url'            => $request->model_3d_url,
        ]);
 
        $plan2d = null;
        if ($request->floor_plan_2d) {
            $raw = trim($request->floor_plan_2d);
            $plan2d = str_starts_with($raw, '<svg') ? ['svg' => $raw] : (json_decode($raw, true) ?? ['svg' => $raw]);
        }
 
        $building->update([
            'name'          => $data['name'],
            'floors_count'  => $data['floors_count'],
            'floor_plan_2d' => $plan2d,
            'model_3d'      => !empty($model3d) ? $model3d : null,
        ]);
 
        return redirect()->route('buildings.show', $building)->with('success', 'Bâtiment mis à jour.');
    }
 
    public function destroy(Site $site, Building $building)
    {
        $building->delete();
        return redirect()->route('sites.show', $site)->with('success', 'Bâtiment supprimé.');
    }
}