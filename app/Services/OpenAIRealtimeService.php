<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class OpenAIRealtimeService
{
    // Voces soportadas por OpenAI Realtime API (oficial 2025)
    private const SUPPORTED_VOICES = [
        'marin', 'sage', 'oak', 'juniper', 'ember', 'orion', 'shimmer'
    ];

    // Modelos soportados
    private const SUPPORTED_MODELS = [
        'gpt-4o-realtime-preview-2024-12-17',
        'gpt-4o-mini-realtime-preview-2024-12-17',
        'gpt-4o-realtime-preview',
        'gpt-4o-mini-realtime-preview',
    ];

    public function createVoiceSession(
        ?string $voice = null,
        ?string $instructions = null,
        ?string $model = null,
        int $expiresIn = 120,
        ?string $conversationLevel = null,
        ?string $userId = null
    ): array {
        // Validar y asignar modelo
        $model = $model ?? Config::get('openai.realtime_model', 'gpt-4o-mini-realtime-preview-2024-12-17');
        if (!in_array($model, self::SUPPORTED_MODELS)) {
            throw new InvalidArgumentException(
                "Modelo no soportado: {$model}. Use uno de: " . implode(', ', self::SUPPORTED_MODELS)
            );
        }

        // Validar y asignar voz
        $voice = $voice ?? Config::get('openai.realtime_voice', 'marin');
        if (!in_array($voice, self::SUPPORTED_VOICES)) {
            throw new InvalidArgumentException(
                "Voz no soportada: {$voice}. Use una de: " . implode(', ', self::SUPPORTED_VOICES)
            );
        }

        // Validar expiración
        $expiresIn = max(1, min($expiresIn, 120));

        // Construir instrucciones
        $instructions = $instructions ?? $this->buildInstructions($conversationLevel);

        // Configuración de sesión según documentación oficial
        $payload = [
            'model' => $model,
            'voice' => $voice,
        ];

        if ($instructions) {
            $payload['instructions'] = $instructions;
        }

        try {
            $headers = [
                'Authorization' => 'Bearer ' . config('ai.providers.openai.key'),
            ];

            // Incluir safety identifier si el usuario está disponible
            if ($userId) {
                $headers['OpenAI-Safety-Identifier'] = $this->hashUserId($userId);
            }

            $response = Http::withHeaders($headers)
                ->post('https://api.openai.com/v1/realtime/sessions', $payload);

            $response->throw();
        } catch (RequestException|ConnectionException $e) {
            throw $e;
        }

        $data = $response->json();

        // Procesar expiración
        $expiresInResponse = isset($data['expires_in']) ? (int) $data['expires_in'] : null;
        $expiresAt = $data['expires_at'] ?? null;

        if ($expiresInResponse === null && $expiresAt) {
            if (is_numeric($expiresAt)) {
                $expiresAtTs = (int) $expiresAt;
            } else {
                $expiresAtTs = Carbon::parse($expiresAt)->timestamp;
            }
            $expiresInResponse = max(0, $expiresAtTs - Carbon::now()->timestamp);
        }

        return [
            'id' => $data['id'] ?? null,
            'model' => $data['model'] ?? $model,
            'voice' => $data['voice'] ?? $voice,
            'client_secret' => data_get($data, 'value', data_get($data, 'client_secret.value')),
            'expires_in' => $expiresInResponse ?? $expiresIn,
            'expires_at' => $expiresAt,
        ];
    }

    protected function buildInstructions(?string $conversationLevel): string
    {
        $baseInstructions = collect([
            'You are Nativo, the AI assistant of the Nativo web application.',
            'Your role is to engage in natural voice conversations with Spanish-speaking students.',
            'Always respond in English and prioritize using English throughout.',
            'Your goal is to help students practice their English speaking skills.',
            'Be encouraging, patient, and provide constructive feedback on pronunciation.',
            'Keep the conversation natural, engaging, and motivating.',
        ])->implode(' ');

        $levelGuidance = [
            'basico' => implode(' ', [
                'Use simple vocabulary and speak slowly (120-140 words per minute).',
                'Focus on present tense and basic topics like greetings, introductions, and daily routines.',
                'Frequently check understanding and repeat key vocabulary.',
                'Be very patient and encouraging.',
            ]),
            'intermedio' => implode(' ', [
                'Use varied vocabulary and speak at normal pace (150-170 words per minute).',
                'Mix verb tenses and discuss hobbies, travel, and personal experiences.',
                'Ask follow-up questions to encourage longer responses.',
                'Provide gentle corrections with explanations.',
            ]),
            'avanzado' => implode(' ', [
                'Use sophisticated vocabulary and idiomatic expressions.',
                'Speak at natural pace (170+ words per minute) and use all verb tenses.',
                'Discuss complex topics, current events, and abstract ideas.',
                'Challenge the student with debate and nuanced discussions.',
                'Correct subtle grammar and pronunciation errors.',
            ]),
        ];

        $selectedGuidance = data_get($levelGuidance, $conversationLevel, $levelGuidance['intermedio']);

        return $baseInstructions . ' ' . $selectedGuidance;
    }

    /**
     * Hash user ID para safety identifier
     */
    private function hashUserId(string $userId): string
    {
        return hash('sha256', $userId . config('app.key'));
    }
}
