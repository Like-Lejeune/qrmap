<?php
// app/Services/Ai/ModelClient.php
//
// Client Chat Completions pour la Meta Model API (Muse Spark), avec repli
// possible vers tout fournisseur compatible OpenAI (Groq, Ollama…).
// Remplace LlamaClient.php.
//
// Particularités Meta prises en compte (doc dev.meta.ai) :
//   - endpoint  POST https://api.meta.ai/v1/chat/completions, Bearer token
//   - max_completion_tokens (max_tokens est déprécié)
//   - temperature : Meta recommande de ne pas la fixer
//   - reasoning_effort : Muse Spark raisonne toujours ("none" est refusé) ;
//     "low" garde le guide rapide
//   - tool_choice : seul "auto" est accepté
//   - parallel_tool_calls : on force false (un outil à la fois, plus simple)
//   - 429 : on réessaie avec un délai croissant
//
// Config (config/services.php → 'ai') :
//   AI_PROVIDER=meta            (ou openai_compat pour Groq / Ollama)
//   AI_BASE_URL=https://api.meta.ai/v1
//   AI_API_KEY=...
//   AI_MODEL=muse-spark-1.3
//   AI_REASONING_EFFORT=low

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ModelClient
{
    public function __construct(
        private string $baseUrl,
        private ?string $apiKey,
        private string $model,
        private string $provider = 'meta',
        private ?string $reasoningEffort = 'low',
        private int $timeout = 45,
    ) {
    }

    /**
     * @param string|null $reasoningEffort  surcharge (ex. 'medium' pour lire un plan)
     * @param int|null    $timeout          surcharge (la lecture d'image est plus longue)
     */
    public static function fromConfig(?string $reasoningEffort = null, ?int $timeout = null): self
    {
        return new self(
            rtrim((string) config('services.ai.base_url', 'https://api.meta.ai/v1'), '/'),
            config('services.ai.api_key'),
            (string) config('services.ai.model', 'muse-spark-1.3'),
            (string) config('services.ai.provider', 'meta'),
            $reasoningEffort ?? (config('services.ai.reasoning_effort', 'low') ?: null),
            $timeout ?? (int) config('services.ai.timeout', 45),
        );
    }

    /**
     * Corps de requête Chat Completions. Public et sans effet de bord → testable.
     *
     * @param array      $messages
     * @param array      $tools
     * @param array|null $responseFormat  ex. ['type'=>'json_schema','json_schema'=>[...]]
     * @param int        $maxTokens
     */
    public function payload(array $messages, array $tools = [], ?array $responseFormat = null, int $maxTokens = 800): array
    {
        $body = [
            'model'    => $this->model,
            'messages' => $messages,
        ];

        if ($this->provider === 'meta') {
            $body['max_completion_tokens'] = $maxTokens;
            if ($this->reasoningEffort) {
                $body['reasoning_effort'] = $this->reasoningEffort;
            }
        } else {
            $body['max_tokens'] = $maxTokens;
            $body['temperature'] = 0.2;
        }

        if ($tools) {
            $body['tools'] = $tools;
            $body['tool_choice'] = 'auto';
            $body['parallel_tool_calls'] = false;
        }
        if ($responseFormat) {
            $body['response_format'] = $responseFormat;
        }
        return $body;
    }

    /**
     * @return array Message assistant : ['role'=>'assistant','content'=>?string,'tool_calls'=>?array]
     */
    public function chat(array $messages, array $tools = [], ?array $responseFormat = null, int $maxTokens = 800): array
    {
        $body = $this->payload($messages, $tools, $responseFormat, $maxTokens);

        $res = null;
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $req = Http::timeout($this->timeout)->acceptJson();
            if ($this->apiKey) {
                $req = $req->withToken($this->apiKey);
            }
            $res = $req->post($this->baseUrl . '/chat/completions', $body);

            if ($res->status() !== 429) {
                break;
            }
            usleep((int) (500000 * (2 ** $attempt)));   // 0,5 s puis 1 s
        }

        if (!$res->successful()) {
            throw new RuntimeException('Modèle IA : HTTP ' . $res->status() . ' — ' . mb_substr($res->body(), 0, 300));
        }

        $msg = $res->json('choices.0.message');
        if (!is_array($msg)) {
            throw new RuntimeException('Réponse du modèle inattendue');
        }
        return $msg;
    }
}