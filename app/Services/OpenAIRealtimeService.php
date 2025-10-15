<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class OpenAIRealtimeService
{
    /**
     * Create a short-lived voice conversation session with OpenAI's Realtime API.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createVoiceSession(
        ?string $voice = null,
        ?string $instructions = null,
        ?string $model = null,
        int $expiresIn = 60
    ): array {

        $model ??= config('openai.realtime_model', 'gpt-realtime-mini');
        $voice ??= config('openai.realtime_voice', 'alloy');
        $expiresIn = max(1, min($expiresIn, 60));

        $payload = [
            'model' => $model,
            'voice' => $voice,
            'modalities' => ['text', 'audio'],
        ];

        if ($instructions) {
            $payload['instructions'] = $instructions;
        }

        // Use the openaiRealtime HTTP macro to include headers and timeout
        $response = Http::openaiRealtime()
            ->post('https://api.openai.com/v1/realtime/sessions', $payload);

        $response->throw();

        $data = $response->json();

        $expiresInResponse = isset($data['expires_in']) ? (int) $data['expires_in'] : null;
        $expiresAt = $data['expires_at'] ?? null;

        if ($expiresInResponse === null && $expiresAt) {
            $expiresAtTs = is_numeric($expiresAt) ? (int) $expiresAt : strtotime($expiresAt);
            if ($expiresAtTs) {
                $expiresInResponse = max(0, $expiresAtTs - time());
            }
        }

        return [
            'id' => $data['id'] ?? null,
            'model' => $data['model'] ?? $model,
            'voice' => $data['voice'] ?? $voice,
            'client_secret' => $data['client_secret']['value'] ?? null,
            'expires_in' => $expiresInResponse ?? $expiresIn,
            'expires_at' => $expiresAt,
        ];
    }
}
