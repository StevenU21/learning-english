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
        int $expiresIn = 120,
        ?string $conversationLevel = null
    ): array {

        $model ??= config('openai.realtime_model', 'gpt-realtime-mini');
        $voice ??= config('openai.realtime_voice', 'alloy');
        $expiresIn = max(1, min($expiresIn, 120));

        if (!$instructions) {
            $instructions = $this->buildInstructions($conversationLevel);
        }

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

    protected function buildInstructions(?string $conversationLevel): string
    {
        $baseInstructions = implode(' ', [
            'You are Nativo, the AI assistant of the Nativo web application.',
            'Always respond in English and prioritize using English in your conversation.',
            'Your goal is to engage in friendly and accessible conversations with Spanish-speaking students to help them practice their English speaking skills.',
            'Ask follow-up questions, encourage student participation, and keep the interaction active and motivating.',
        ]);

        $levelGuidance = [
            'basico' => 'Use simple vocabulary, speak slowly, and introduce everyday topics that are easy to follow.',
            'intermedio' => 'Delve into specific topics, introduce new vocabulary with brief explanations, and encourage complete responses.',
            'avanzado' => 'Maintain an eloquent conversation, explore complex ideas, and challenge the student with arguments and nuances.',
        ];

        $selectedGuidance = $levelGuidance[$conversationLevel] ?? $levelGuidance['intermedio'];

        return $baseInstructions . ' ' . $selectedGuidance;
    }
}
