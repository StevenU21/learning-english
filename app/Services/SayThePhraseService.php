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
        $expected = Str::of($expected)->lower()->trim()->rtrim('.');
        $userText = Str::of($userText)->lower()->trim()->rtrim('.');
        $expected = $this->normalizeContractions($expected);
        $userText = $this->normalizeContractions($userText);

        // Score tradicional
        similar_text($expected, $userText, $percent);
        $percent = round($percent, 2);

        $expectedWords = collect(explode(' ', $expected));
        $userWords = collect(explode(' ', $userText));

        $matched = $expectedWords->filter(function ($word, $i) use ($userWords) {
            return $userWords->get($i) === $word;
        })->count();
        $total = $expectedWords->count() > 0 ? $expectedWords->count() : 1;
        $wordScore = collect([($matched / $total) * 100])->map(fn($v) => round($v, 2))->first();

        $firstWordExpected = $expectedWords->first();
        $firstWordUser = $userWords->first();
        $firstWordPenalty = ($firstWordExpected !== $firstWordUser) ? 0.7 : 1;

        // Embeddings OpenAI
        try {
            $embeddings = OpenAI::embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => [$expected, $userText],
            ]);
            $vec1 = $embeddings['data'][0]['embedding'] ?? [];
            $vec2 = $embeddings['data'][1]['embedding'] ?? [];
            $semanticScore = $this->cosineSimilarity($vec1, $vec2) * 100;
        } catch (\Exception $e) {
            $semanticScore = 0;
        }

        // Score final combinando semántica y tradicional
        $finalScore = round((($percent * 0.2) + ($wordScore * 0.3) + ($semanticScore * 0.5)) * $firstWordPenalty, 2);

        return [
            'score' => $finalScore,
            'score_global' => $percent,
            'score_words' => $wordScore,
            'score_semantic' => round($semanticScore, 2),
            'user_text' => (string) $userText,
            'expected' => (string) $expected,
        ];
    }

    // Calcula la similitud coseno entre dos vectores
    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (empty($vec1) || empty($vec2) || count($vec1) !== count($vec2)) {
            return 0.0;
        }
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        foreach ($vec1 as $i => $v) {
            $dot += $v * $vec2[$i];
            $normA += $v * $v;
            $normB += $vec2[$i] * $vec2[$i];
        }
        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }
        return $dot / (sqrt($normA) * sqrt($normB));
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
