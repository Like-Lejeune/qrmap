<?php

// =============================================
// app/Http/Controllers/SiteController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\Site;
use Illuminate\Http\Request;
 
class SiteController extends Controller
{
 
    public function create()
    {
        return view('sites.form');
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);
        $site = Site::create($data);
        return redirect()->route('sites.show', $site)->with('success', 'Site créé avec succès.');
    }
 
    public function show(Site $site)
    {
        $site->load(['buildings.floors', 'qrCodes.building', 'qrCodes.floor']);
        return view('sites.show', compact('site'));
    }
 
    public function edit(Site $site)
    {
        return view('sites.form', compact('site'));
    }
 
    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);
        $site->update($data);
        return redirect()->route('sites.show', $site)->with('success', 'Site mis à jour.');
    }
 
}