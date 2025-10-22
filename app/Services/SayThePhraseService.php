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
}
