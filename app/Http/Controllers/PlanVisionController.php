<?php
// app/Http/Controllers/PlanVisionController.php
//
// POST /plan-editor/vision   (zone admin, authentifiée, CSRF)
// Body JSON : { image: "data:image/jpeg;base64,...", level: 0, hint: "RDC, 40 × 25 m" }
// Réponse   : { rooms: [...], building: {width_m, depth_m}, bbox: {x,y,w,h},
//               floor_label, warnings: [...] }
//
// L'éditeur convertit lui-même les PDF en image et réduit les photos
// (≤ 2000 px) avant l'envoi : le serveur ne reçoit qu'une image raisonnable.

namespace App\Http\Controllers;

use App\Services\Ai\ModelClient;
use App\Services\Ai\PlanVision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlanVisionController extends Controller
{
    private const MAX_BASE64_CHARS = 12_000_000;   // ≈ 9 Mo d'image

    public function extract(Request $request, PlanVision $vision): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'string', 'max:' . self::MAX_BASE64_CHARS,
                        'regex:/^data:image\/(jpeg|jpg|png|webp);base64,[A-Za-z0-9+\/=]+$/'],
            'level' => 'nullable|integer|min:-10|max:200',
            'hint'  => 'nullable|string|max:300',
        ], [
            'image.regex' => 'Format accepté : image JPEG, PNG ou WebP (les PDF sont convertis par l\'éditeur).',
        ]);

        $req = $vision->request($data['image'], $data['hint'] ?? null);

        try {
            // Lire un plan demande plus de réflexion qu'une réponse de guide
            $client = ModelClient::fromConfig(config('services.ai.vision_reasoning_effort', 'medium'), 120);
            $msg = $client->chat($req['messages'], [], $req['response_format'], 8000);
        } catch (Throwable $e) {
            Log::warning('Lecture de plan IA impossible', ['error' => $e->getMessage()]);
            return response()->json(['message' => "Le modèle n'a pas pu lire ce plan. Réessayez ou dessinez-le à la main."], 503);
        }

        $raw = $this->decode((string) ($msg['content'] ?? ''));
        if ($raw === null) {
            return response()->json(['message' => "La réponse du modèle n'était pas exploitable. Réessayez avec une image plus nette."], 422);
        }

        return response()->json($vision->normalize($raw, (int) ($data['level'] ?? 0)));
    }

    /** JSON du modèle, y compris s'il est entouré de texte ou de ```json. */
    private function decode(string $content): ?array
    {
        $json = json_decode($content, true);
        if (is_array($json)) return $json;

        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) return null;

        $json = json_decode(substr($content, $start, $end - $start + 1), true);
        return is_array($json) ? $json : null;
    }
}