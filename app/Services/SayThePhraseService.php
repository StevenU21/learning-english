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

        // Score global (similar_text)
        similar_text($expected, $userText, $percent);
        $percent = round($percent, 2);

        // Score palabra por palabra
        $expectedWords = preg_split('/\s+/', $expected);
        $userWords = preg_split('/\s+/', $userText);
        $matched = 0;
        $total = max(count($expectedWords), 1); // evitar división por cero
        foreach ($expectedWords as $i => $word) {
            if (isset($userWords[$i]) && $userWords[$i] === $word) {
                $matched++;
            }
        }
        $wordScore = round(($matched / $total) * 100, 2);

        // Score combinado (puedes ajustar el peso)
        $finalScore = round(($percent * 0.6) + ($wordScore * 0.4), 2);

        $result = $finalScore >= 80 ? 'Aprobado 🎉' : 'Intenta de nuevo 🔁';

        return [
            'score' => $finalScore,
            'score_global' => $percent,
            'score_words' => $wordScore,
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
