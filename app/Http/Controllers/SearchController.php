<?php

// =============================================
// app/Http/Controllers/SearchController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\Building;
use App\Models\QrCode;
use App\Models\Site;
use Illuminate\Http\Request;
 
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
 
        if (strlen($q) < 2) {
            return view('search', ['sites' => collect(), 'buildings' => collect(), 'qrcodes' => collect(), 'total' => 0]);
        }
 
        $like = '%' . $q . '%';
 
        $sites = Site::withCount('buildings')
            ->where(fn($qb) => $qb->where('name', 'like', $like)->orWhere('address', 'like', $like))
            ->limit(10)->get();
 
        $buildings = Building::with('site')
            ->where('name', 'like', $like)
            ->limit(10)->get();
 
        $qrcodes = QrCode::with(['site', 'building', 'floor'])
            ->where('location_label', 'like', $like)
            ->orWhere('uuid', 'like', $like)
            ->limit(20)->get();
 
        $total = $sites->count() + $buildings->count() + $qrcodes->count();
 
        return view('search', compact('sites', 'buildings', 'qrcodes', 'total'));
    }
}
 
