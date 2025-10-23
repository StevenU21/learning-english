<?php
namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SayThePhraseService
{
    private function normalizeContractions($text)
    {
        $contractions = [
            "/\bi'm\b/" => 'i am',
            "/\byou're\b/" => 'you are',
            "/\bhe's\b/" => 'he is',
            "/\bshe's\b/" => 'she is',
            "/\bit's\b/" => 'it is',
            "/\bwe're\b/" => 'we are',
            "/\bthey're\b/" => 'they are',
            "/\bi've\b/" => 'i have',
            "/\byou've\b/" => 'you have',
            "/\bwe've\b/" => 'we have',
            "/\bthey've\b/" => 'they have',
            "/\bi'd\b/" => 'i would',
            "/\byou'd\b/" => 'you would',
            "/\bhe'd\b/" => 'he would',
            "/\bshe'd\b/" => 'she would',
            "/\bwe'd\b/" => 'we would',
            "/\bthey'd\b/" => 'they would',
            "/\bi'll\b/" => 'i will',
            "/\byou'll\b/" => 'you will',
            "/\bhe'll\b/" => 'he will',
            "/\bshe'll\b/" => 'she will',
            "/\bwe'll\b/" => 'we will',
            "/\bthey'll\b/" => 'they will',
            "/\bcan't\b/" => 'cannot',
            "/\bwon't\b/" => 'will not',
            "/\bdon't\b/" => 'do not',
            "/\bdoesn't\b/" => 'does not',
            "/\bdidn't\b/" => 'did not',
            "/\baren't\b/" => 'are not',
            "/\bisn't\b/" => 'is not',
            "/\bwasn't\b/" => 'was not',
            "/\bweren't\b/" => 'were not',
            "/\bhaven't\b/" => 'have not',
            "/\bhasn't\b/" => 'has not',
            "/\bhadn't\b/" => 'had not',
            "/\bwouldn't\b/" => 'would not',
            "/\bshouldn't\b/" => 'should not',
            "/\bcouldn't\b/" => 'could not',
            "/\bmustn't\b/" => 'must not',
        ];
        foreach ($contractions as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }
        return $text;
    }
    public function transcribeAudio(string $audioPath, string $language = 'en'): string
    {
        $stream = Storage::readStream($audioPath);
        if (!$stream) {
            return '';
        }
        $response = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file' => $stream,
            'language' => $language,
        ]);
        return Str::of($response->text)->trim();
    }

    public function evaluate(string $expected, string $userText): array
    {

        // Normalizar: minúsculas, trim, quitar punto final y expandir contracciones
        $expected = Str::of($expected)->lower()->trim()->rtrim('.');
        $userText = Str::of($userText)->lower()->trim()->rtrim('.');
        $expected = $this->normalizeContractions($expected);
        $userText = $this->normalizeContractions($userText);

        similar_text($expected, $userText, $percent);
        $percent = round($percent, 2);

        $expectedWords = collect(explode(' ', $expected));
        $userWords = collect(explode(' ', $userText));

        $matched = $expectedWords->filter(function ($word, $i) use ($userWords) {
            return $userWords->get($i) === $word;
        })->count();
        $total = $expectedWords->count() > 0 ? $expectedWords->count() : 1; // evitar división por cero
        $wordScore = collect([($matched / $total) * 100])->map(fn($v) => round($v, 2))->first();

        // Penalización fuerte si la primera palabra no coincide
        $firstWordExpected = $expectedWords->first();
        $firstWordUser = $userWords->first();
        $firstWordPenalty = ($firstWordExpected !== $firstWordUser) ? 0.7 : 1; // penaliza 30% el score final si la primera palabra no coincide

        // Ajustar pesos: 40% global, 60% palabra por palabra
        $finalScore = round((($percent * 0.4) + ($wordScore * 0.6)) * $firstWordPenalty, 2);

        return [
            'score' => $finalScore,
            'score_global' => $percent,
            'score_words' => $wordScore,
            'user_text' => (string) $userText,
            'expected' => (string) $expected,
        ];
    }

    public function processAttempt(array $data): array
    {
        $audioPath = Arr::get($data, 'audio_path');
        $solution = Arr::get($data, 'solution', '');
        $language = Arr::get($data, 'language', 'en');

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
