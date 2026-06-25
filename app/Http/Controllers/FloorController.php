<?php
// =============================================
// app/Http/Controllers/FloorController.php  — VERSION COMPLÈTE
// =============================================
namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function create(Building $building)
    {
        return view('floors.form', compact('building'));
    }

    /** Page import SVG/PNG drag & drop */
    public function import(Building $building)
    {
        return view('floors.import', compact('building'));
    }

    public function store(Request $request, Building $building)
    {
        $data = $request->validate([
            'level'           => 'required|integer',
            'label'           => 'nullable|string|max:100',
            'plan_svg'        => 'nullable|string',
            'plan_image_b64'  => 'nullable|string',
            'rooms'           => 'nullable|string',
        ]);

        $plan2d = null;
        if ($request->plan_svg && trim($request->plan_svg) !== '') {
            $plan2d = ['type' => 'svg', 'svg' => trim($request->plan_svg)];
        } elseif ($request->plan_image_b64) {
            $plan2d = ['type' => 'image', 'data' => $request->plan_image_b64];
        }

        $rooms = json_decode($request->rooms ?? '[]', true) ?: [];

        $building->floors()->create([
            'level'   => $data['level'],
            'label'   => $data['label'] ?? $this->autoLabel($data['level']),
            'plan_2d' => $plan2d,
            'rooms'   => $rooms,
        ]);

        return redirect()->route('buildings.show', $building)
            ->with('success', 'Étage ajouté avec succès.');
    }

    public function edit(Building $building, Floor $floor)
    {
        return view('floors.form', compact('building', 'floor'));
    }

    public function update(Request $request, Building $building, Floor $floor)
    {
        $data = $request->validate([
            'level'           => 'required|integer',
            'label'           => 'nullable|string|max:100',
            'plan_svg'        => 'nullable|string',
            'plan_image_b64'  => 'nullable|string',
            'rooms'           => 'nullable|string',
        ]);

        $plan2d = $floor->plan_2d;
        if ($request->plan_svg && trim($request->plan_svg) !== '') {
            $plan2d = ['type' => 'svg', 'svg' => trim($request->plan_svg)];
        } elseif ($request->plan_image_b64) {
            $plan2d = ['type' => 'image', 'data' => $request->plan_image_b64];
        }

        $floor->update([
            'level'   => $data['level'],
            'label'   => $data['label'],
            'plan_2d' => $plan2d,
            'rooms'   => json_decode($request->rooms ?? '[]', true) ?: [],
        ]);

        return redirect()->route('buildings.show', $building)
            ->with('success', 'Étage mis à jour.');
    }

    public function destroy(Building $building, Floor $floor)
    {
        $floor->delete();
        return redirect()->route('buildings.show', $building)
            ->with('success', 'Étage supprimé.');
    }

    private function autoLabel(int $level): string
    {
        return match(true) {
            $level === 0  => 'Rez-de-chaussée',
            $level < 0    => 'Sous-sol ' . abs($level),
            $level === 1  => '1er étage',
            default       => $level . 'e étage',
        };
    }
}