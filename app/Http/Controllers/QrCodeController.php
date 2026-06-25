<?php
// =============================================
// app/Http/Controllers/QrCodeController.php — VERSION FINALE
// =============================================
namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use App\Models\QrCode;
use App\Models\Site;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrCodeController extends Controller
{
    public function index()
    {
        $qrcodes = QrCode::with(['site', 'building', 'floor'])->latest()->paginate(30);
        return view('qrcodes.index', compact('qrcodes'));
    }

    public function create()
    {
        $sites     = Site::all();
        $buildings = Building::when(request('site_id'), fn($q) => $q->where('site_id', request('site_id')))->get();
        $floors    = Floor::when(request('building_id'), fn($q) => $q->where('building_id', request('building_id')))->get();
        return view('qrcodes.create', compact('sites', 'buildings', 'floors'));
    }

    public function store(Request $request, QrCodeService $service)
    {
        $data = $request->validate([
            'site_id'        => 'required|exists:sites,id',
            'building_id'    => 'nullable|exists:buildings,id',
            'floor_id'       => 'nullable|exists:floors,id',
            'location_label' => 'required|string|max:255',
            'pos_x'          => 'nullable|numeric|min:0|max:100',
            'pos_y'          => 'nullable|numeric|min:0|max:100',
        ]);

        $result = $service->generate($data);
        return redirect()
            ->route('qrcodes.show', $result['qr_code'])
            ->with('success', 'QR Code généré avec succès !');
    }

    public function show(QrCode $qrcode)
    {
        $qrcode->load(['site', 'building', 'floor']);
        return view('qrcodes.show', compact('qrcode'));
    }

 public function print()
    {
        $sites   = Site::all();
        $qrcodes = QrCode::with(['site', 'building', 'floor'])
            ->orderBy('site_id')
            ->orderBy('building_id')
            ->get();

        // Préparer les données JS ici, pas dans la vue
        $qrcodesJs = $qrcodes->map(function ($q) {
            return [
                'id'       => $q->id,
                'uuid'     => $q->uuid,
                'label'    => $q->location_label,
                'site'     => $q->site?->name,
                'building' => $q->building?->name,
                'floor'    => $q->floor?->label,
                'image'    => '"\"'.Storage::disk('public')->exists('qrcodes/'.$q->uuid.'.svg')
                                ? asset('storage/qrcodes/'.$q->uuid.'.svg')
                                : null,
                'url'      => route('scan.show', $q->uuid),
                'site_id'  => $q->site_id,
            ];
        })->values();

    return view('qrcodes.print', compact('qrcodes', 'sites', 'qrcodesJs'));
}

    public function destroy(QrCode $qrcode)
    {
        Storage::disk('public')->delete('qrcodes/' . $qrcode->uuid . '.svg');
        $qrcode->delete();
        return redirect()->route('qrcodes.index')->with('success', 'QR Code supprimé.');
    }
}