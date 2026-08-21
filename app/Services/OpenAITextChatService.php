<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAITextChatService
{
    public function generateReply(array $messages, ?string $level = null, ?float $temperature = null): array
    {
        $normalizedMessages = $this->normalizeMessages($messages);

        $model = Config::get('openai.text_chat_model', 'gpt-4o-mini');
        $temperature ??= (float) Config::get('openai.text_chat_temperature', 0.7);
        $systemPrompt = $this->buildSystemPrompt($level);

        $payload = [
            'model' => $model,
            'temperature' => max(0, min($temperature, 2)),
            'response_format' => ['type' => 'json_object'],
            'messages' => array_merge([['role' => 'system', 'content' => $systemPrompt]], $normalizedMessages),
        ];

        $response = $this->client()->post('chat/completions', $payload);

        if ($response->failed()) {
            throw new \Exception('API Error: '.$response->body());
        }

        $content = $response->json('choices.0.message.content', '');
        $data = $this->decodeContent($content);

        return [
            'content' => trim(Arr::get($data, 'content', '')),
            'vocabulary' => $this->sanitizeVocabulary(Arr::get($data, 'vocabulary', [])),
            'grammarTips' => $this->sanitizeStringArray(Arr::get($data, 'grammarTips', [])),
            'followUpQuestions' => $this->sanitizeStringArray(Arr::get($data, 'followUpQuestions', [])),
        ];
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
            'You must respond in JSON format with the following keys:',
            '- "content": Your conversational reply to the student in plain English (using "you" statements).',
            '- "vocabulary": An array of 1 or 2 new words relevant to the conversation. Each object must have "term", "definition", and optionally "example".',
            '- "grammarTips": An array with 1 short, actionable grammar tip starting with "Try", "Remember", or "Consider".',
            '- "followUpQuestions": An array with 1 short example reply the student could use next.',
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
