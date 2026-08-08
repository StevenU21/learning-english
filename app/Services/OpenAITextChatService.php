<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAITextChatService
{
    public function generateStreamedReply(array $messages, ?string $level = null, ?float $temperature = null): \Closure
    {
        $normalizedMessages = $this->normalizeMessages($messages);

        $model = Config::get('openai.text_chat_model', 'gpt-4o-mini');
        $temperature ??= (float) Config::get('openai.text_chat_temperature', 0.7);
        $systemPrompt = $this->buildSystemPrompt($level);

        $payload = [
            'model' => $model,
            'temperature' => max(0, min($temperature, 2)),
            'stream' => true,
            'messages' => array_merge([['role' => 'system', 'content' => $systemPrompt]], $normalizedMessages),
        ];

        return function () use ($payload) {
            $apiKey = config('openai.api_key');
            $baseUrl = config('openai.base_uri');
            if (! is_string($baseUrl) || trim($baseUrl) === '') {
                $baseUrl = 'https://api.openai.com/v1';
            }
            $url = rtrim($baseUrl, '/').'/chat/completions';

            // Limpiar buffers
            if (ob_get_level() > 0) {
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
            }

            // Padding para forzar flush inicial
            echo ': '.str_repeat(' ', 4096)."\n\n";
            flush();

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0); // Disable auto-output
            curl_setopt($ch, CURLOPT_ENCODING, ''); // Auto-decode gzip
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix Laragon SSL issues

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) {
                echo $data;
                flush();

                return strlen($data);
            });

            $result = curl_exec($ch);
            if ($result === false) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }
            curl_close($ch);
        };
    }

    protected function sanitizeVocabulary(array $items): array
    {
        return collect($items)
            ->map(fn ($i) => ['term' => trim(Arr::get($i, 'term', '')), 'definition' => trim(Arr::get($i, 'definition', ''))] +
                (trim(Arr::get($i, 'example', '')) ? ['example' => trim($i['example'])] : []))
            ->filter(fn ($e) => $e['term'] !== '' && $e['definition'] !== '')
            ->values()->toArray();
    }

    protected function sanitizeStringArray(array $items): array
    {
        return collect($items)->map(fn ($i) => trim((string) $i))->filter()->values()->toArray();
    }

    protected function normalizeMessages(array $messages): array
    {
        return collect($messages)
            ->filter(fn ($m) => Str::of(Arr::get($m, 'content', ''))->trim()->isNotEmpty()
                && in_array(Arr::get($m, 'role'), ['user', 'assistant'], true))
            ->map(fn ($m) => ['role' => Arr::get($m, 'role'), 'content' => trim(Arr::get($m, 'content'))])
            ->values()->toArray();
    }

    protected function buildSystemPrompt(?string $level): string
    {
        $base = collect([
            'You are Nativo, a friendly AI tutor guiding Spanish-speaking students as they practice conversational English.',
            'Speak directly to the student in English, keep a warm and encouraging tone, give concise explanations, and motivate them to expand on their ideas.',
            'Do not use JSON formatting. Respond with plain text, using Markdown to structure your response.',
            'First, write your conversational reply to the student in plain English (using "you" statements to address them directly).',
            'Then, add a "### Vocabulary" section with 1 or 2 new words relevant to the conversation.',
            'Then, add a "### Grammar" section with 1 short, actionable grammar tip starting with "Try", "Remember", or "Consider".',
            'Then, add a "### Follow-up" section with 1 short example reply the student could use next.',
            'Base everything on the student\'s most recent message. Be concise and keep the response fast.',
        ])->implode(' ');
        $guidance = collect([
            'basico' => 'Use simple vocabulary, short sentences, and clear examples. Avoid idioms and advanced grammar.',
            'intermedio' => 'Use everyday vocabulary with occasional new expressions. Provide quick notes to clarify grammar or vocabulary.',
            'avanzado' => 'Use nuanced language, challenge the student with complex questions, and highlight advanced expressions.',
        ]);

        return $base.' '.($guidance->get($level, $guidance->get('intermedio')));
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeContent(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }
        if (! is_string($content) || trim($content) === '') {
            return [];
        }

        return json_decode($content, true) ?? [];
    }

    protected function client(): PendingRequest
    {
        $maxRetries = max(0, (int) Config::get('openai.max_retries', 2));
        $retryDelayMs = max(0, (int) Config::get('openai.retry_delay_ms', 1000));

        $client = Http::openai()
            ->withHeaders(['Content-Type' => 'application/json']);

        if ($maxRetries > 0) {
            $client = $client->retry($maxRetries, $retryDelayMs, throw: false);
        }

        return $client;
    }
}
