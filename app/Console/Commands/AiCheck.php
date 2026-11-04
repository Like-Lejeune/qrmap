<?php
// app/Console/Commands/AiCheck.php
//
// Vérifie la connexion à Muse Spark (Meta Model API) avant la démo.
//   php artisan qrmap:ai-check
//   php artisan qrmap:ai-check --image=storage/app/plan.jpg
//
// 1. Réponse simple  2. Appel d'outil  3. (option) Lecture d'un plan

namespace App\Console\Commands;

use App\Services\Ai\ModelClient;
use App\Services\Ai\PlanVision;
use Illuminate\Console\Command;
use Throwable;

class AiCheck extends Command
{
    protected $signature = 'qrmap:ai-check {--image= : chemin d\'une photo de plan (jpg, png, webp)}';
    protected $description = 'Teste la connexion à Muse Spark : texte, appel d\'outil, lecture de plan';

    public function handle(PlanVision $vision): int
    {
        $this->line('Fournisseur : <info>' . config('services.ai.provider') . '</info> · modèle : <info>'
            . config('services.ai.model') . '</info> · URL : ' . config('services.ai.base_url'));
        if (!config('services.ai.api_key')) {
            $this->error('AI_API_KEY est vide. Ajoute-la dans .env puis : php artisan config:clear');
            return self::FAILURE;
        }

        $ok = true;

        // 1. Texte simple
        $ok = $this->step('1. Réponse simple', function () {
            $msg = ModelClient::fromConfig()->chat([
                ['role' => 'user', 'content' => 'Réponds uniquement : QRMap est prêt.'],
            ], [], null, 200);
            return trim((string) ($msg['content'] ?? '(vide)'));
        }) && $ok;

        // 2. Appel d'outil (ce que fait le guide)
        $ok = $this->step('2. Appel d\'outil', function () {
            $tools = [[
                'type' => 'function',
                'function' => [
                    'name' => 'calculer_itineraire',
                    'description' => 'Calcule le trajet vers une destination du bâtiment.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => ['destination_id' => ['type' => 'integer']],
                        'required' => ['destination_id'],
                    ],
                ],
            ]];
            $msg = ModelClient::fromConfig()->chat([
                ['role' => 'system', 'content' => 'Destinations : 8 = Afriland First Bank, 12 = Pharmacie. Utilise l\'outil pour guider.'],
                ['role' => 'user', 'content' => 'Je cherche une banque.'],
            ], $tools, null, 400);
            $call = $msg['tool_calls'][0]['function'] ?? null;
            if (!$call) {
                throw new \RuntimeException('Pas d\'appel d\'outil. Réponse : ' . mb_substr((string) ($msg['content'] ?? ''), 0, 200));
            }
            return $call['name'] . '(' . $call['arguments'] . ')';
        }) && $ok;

        // 3. Lecture de plan (optionnel)
        if ($path = $this->option('image')) {
            $ok = $this->step('3. Lecture de plan', function () use ($vision, $path) {
                if (!is_file($path)) {
                    throw new \RuntimeException("Fichier introuvable : $path");
                }
                $mime = mime_content_type($path) ?: 'image/jpeg';
                $req = $vision->request('data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)));
                $client = ModelClient::fromConfig(config('services.ai.vision_reasoning_effort', 'medium'), 120);
                $msg = $client->chat($req['messages'], [], $req['response_format'], 8000);
                $raw = json_decode((string) ($msg['content'] ?? ''), true);
                if (!is_array($raw)) {
                    throw new \RuntimeException('JSON illisible : ' . mb_substr((string) ($msg['content'] ?? ''), 0, 200));
                }
                $out = $vision->normalize($raw, 0);
                $names = implode(', ', array_slice(array_column($out['rooms'], 'name'), 0, 8));
                return count($out['rooms']) . ' pièce(s) : ' . $names;
            }) && $ok;
        }

        $this->newLine();
        $ok ? $this->info('Muse Spark répond. Tu peux lancer la démo.')
            : $this->error('Au moins un test a échoué : regarde le message HTTP ci-dessus.');
        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function step(string $label, callable $fn): bool
    {
        $t = microtime(true);
        try {
            $out = $fn();
            $this->line("<info>✔</info> $label (" . round(microtime(true) - $t, 1) . " s) → $out");
            return true;
        } catch (Throwable $e) {
            $this->line("<error>✘</error> $label → " . $e->getMessage());
            $m = $e->getMessage();
            if (str_contains($m, 'HTTP 401') || str_contains($m, 'HTTP 403')) {
                $this->warn('   Clé refusée : vérifie AI_API_KEY, et que l\'accès API est actif sur ton compte dev.meta.ai.');
            } elseif (str_contains($m, 'HTTP 402') || str_contains($m, 'quota') || str_contains($m, 'credit')) {
                $this->warn('   Crédits ou quota épuisés sur le compte Meta.');
            } elseif (str_contains($m, 'HTTP 400')) {
                $this->warn('   Requête refusée : envoie-moi ce message complet, j\'ajuste le format.');
            } elseif (str_contains($m, 'cURL error')) {
                $this->warn('   Pas de connexion à api.meta.ai depuis cette machine (réseau, proxy, pare-feu).');
            }
            return false;
        }
    }
}