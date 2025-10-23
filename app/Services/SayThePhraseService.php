<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class SayThePhraseService
{
    public function transcribeAudio(string $audioPath, string $language = 'en'): string
    {
        $response = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($audioPath, 'r'),
            'language' => $language,
        ]);

        return trim($response->text);
    }

    public function evaluate(string $expected, string $userText): array
    {
        $expected = strtolower(trim($expected));
        $userText = strtolower(trim($userText));

        similar_text($expected, $userText, $percent);
        $percent = round($percent, 2);

        $result = $percent >= 80 ? 'Aprobado 🎉' : 'Intenta de nuevo 🔁';

        return [
            'score' => $percent,
            'result' => $result,
            'user_text' => $userText,
            'expected' => $expected,
        ];
    }

    public function processAttempt(array $data): array
    {
        $audioPath = $data['audio_path'] ?? null;
        $solution = $data['solution'] ?? '';
        $language = $data['language'] ?? 'en';

        if (!$audioPath || !$solution) {
            return [
                'error' => 'Faltan datos requeridos: audio_path o solution.'
            ];
        }

        $userText = $this->transcribeAudio($audioPath, $language);
        $evaluation = $this->evaluate($solution, $userText);

        return array_merge([
            'transcription' => $userText,
        ], $evaluation);
    }
}
