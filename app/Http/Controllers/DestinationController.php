<?php
// app/Http/Controllers/DestinationController.php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Destination;
use App\Models\Floor;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * Liste des destinations d'un étage — page admin.
     */
    public function index(Building $building, Floor $floor)
    {
        $destinations = $floor->destinations()->orderBy('sort_order')->orderBy('label')->get();
        return view('destinations.index', compact('building', 'floor', 'destinations'));
    }

    /**
     * Formulaire de création.
     */
    public function create(Building $building, Floor $floor)
    {
        return view('destinations.form', compact('building', 'floor'));
    }

    /**
     * Enregistrement d'une nouvelle destination.
     */
    public function store(Request $request, Building $building, Floor $floor)
    {
        $data = $request->validate([
            'room_id'     => 'required|string|max:30',
            'label'       => 'required|string|max:255',
            'category'    => 'required|in:medical,admin,service,exit,other',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $floor->destinations()->create([
            ...$data,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('buildings.floors.destinations.index', [$building, $floor])
            ->with('success', 'Destination « ' . $data['label'] . ' » ajoutée.');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Building $building, Floor $floor, Destination $destination)
    {
        return view('destinations.form', compact('building', 'floor', 'destination'));
    }

    /**
     * Mise à jour.
     */
    public function update(Request $request, Building $building, Floor $floor, Destination $destination)
    {
        $data = $request->validate([
            'room_id'     => 'required|string|max:30',
            'label'       => 'required|string|max:255',
            'category'    => 'required|in:medical,admin,service,exit,other',
            'description' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $destination->update([
            ...$data,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('buildings.floors.destinations.index', [$building, $floor])
            ->with('success', 'Destination mise à jour.');
    }

    /**
     * Suppression.
     */
    public function destroy(Building $building, Floor $floor, Destination $destination)
    {
        $label = $destination->label;
        $destination->delete();

        return redirect()
            ->route('buildings.floors.destinations.index', [$building, $floor])
            ->with('success', '« ' . $label . ' » supprimée.');
    }
}