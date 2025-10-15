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
        $apiKey = config('openai.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $model ??= config('openai.realtime_model', 'gpt-4o-realtime-preview-2024-12-17');
        $voice ??= config('openai.realtime_voice', 'alloy');
        $expiresIn = max(1, min($expiresIn, 60));

        $payload = [
            'model' => $model,
            'voice' => $voice,
            'modalities' => ['text', 'audio'],
            'expires_in' => $expiresIn,
            'audio' => [
                'voice' => $voice,
                'format' => 'wav',
            ],
        ];

        if ($instructions) {
            $payload['instructions'] = $instructions;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'OpenAI-Beta' => 'realtime=v1',
        ])->timeout(10)->post('https://api.openai.com/v1/realtime/sessions', $payload);

        $response->throw();

        $data = $response->json();

        return [
            'id' => $data['id'] ?? null,
            'model' => $data['model'] ?? $model,
            'voice' => $data['voice'] ?? $voice,
            'client_secret' => $data['client_secret']['value'] ?? null,
            'expires_in' => min($expiresIn, (int) ($data['expires_in'] ?? $expiresIn)),
        ];
    }
}
