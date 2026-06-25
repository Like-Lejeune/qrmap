<?php

// =============================================
// app/Http/Controllers/Api/BuildingApiController.php
// =============================================
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Site;
 
class BuildingApiController extends Controller
{
    public function bySite(Site $site)
    {
        return response()->json($site->buildings()->select('id', 'name', 'floors_count')->get());
    }
 
    public function floors(Building $building)
    {
        return response()->json($building->floors()->select('id', 'level', 'label')->orderBy('level')->get());
    }
}