<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAITextChatService
{
    public function generateReply(array $messages, ?string $level = null, ?float $temperature = null): array
    {
        $normalizedMessages = collect($messages)
            ->filter(fn($m) => Str::of(Arr::get($m, 'content', ''))->trim()->isNotEmpty()
                && in_array(Arr::get($m, 'role'), ['user', 'assistant'], true))
            ->map(fn($m) => ['role' => Arr::get($m, 'role'), 'content' => trim(Arr::get($m, 'content'))])
            ->values()
            ->toArray();

        $model = Config::get('openai.text_chat_model', 'gpt-4.1');
        $temperature ??= (float) Config::get('openai.text_chat_temperature', 0.7);
        $systemPrompt = $this->buildSystemPrompt($level);

        $payload = [
            'model' => $model,
            'temperature' => max(0, min($temperature, 2)),
            'response_format' => ['type' => 'json_object'],
            'messages' => array_merge([['role' => 'system', 'content' => $systemPrompt]], $normalizedMessages),
        ];

        $response = $this->client()->post('chat/completions', $payload);
        $response->throw();

        $data = $response->json();
        $rawContent = Arr::get($data, 'choices.0.message.content', '');

        $parsed = $this->decodeContent($rawContent);

        return [
            'reply' => $parsed['reply'] ?? (is_string($rawContent) ? $rawContent : ''),
            'vocabulary' => collect(Arr::get($parsed, 'vocabulary', []))
                ->map(fn($i) => ['term' => trim(Arr::get($i, 'term', '')), 'definition' => trim(Arr::get($i, 'definition', ''))] +
                    (trim(Arr::get($i, 'example', '')) ? ['example' => trim($i['example'])] : []))
                ->filter(fn($e) => $e['term'] !== '' && $e['definition'] !== '')->values()->toArray(),
            'grammar_tips' => collect(Arr::get($parsed, 'grammar_tips', []))
                ->map(fn($i) => trim((string) $i))->filter()->values()->toArray(),
            'follow_up_questions' => collect(Arr::get($parsed, 'follow_up_questions', []))
                ->map(fn($i) => trim((string) $i))->filter()->values()->toArray(),
            'raw' => $parsed,
        ];
    }

    protected function sanitizeVocabulary(array $items): array
    {
        return collect($items)
            ->map(fn($i) => ['term' => trim(Arr::get($i, 'term', '')), 'definition' => trim(Arr::get($i, 'definition', ''))] +
                (trim(Arr::get($i, 'example', '')) ? ['example' => trim($i['example'])] : []))
            ->filter(fn($e) => $e['term'] !== '' && $e['definition'] !== '')
            ->values()->toArray();
    }

    protected function sanitizeStringArray(array $items): array
    {
        return collect($items)->map(fn($i) => trim((string) $i))->filter()->values()->toArray();
    }

    protected function normalizeMessages(array $messages): array
    {
        return collect($messages)
            ->filter(fn($m) => Str::of(Arr::get($m, 'content', ''))->trim()->isNotEmpty()
                && in_array(Arr::get($m, 'role'), ['user', 'assistant'], true))
            ->map(fn($m) => ['role' => Arr::get($m, 'role'), 'content' => trim(Arr::get($m, 'content'))])
            ->values()->toArray();
    }

    protected function buildSystemPrompt(?string $level): string
    {
        $base = collect([
            'You are Nativo, a friendly AI tutor guiding Spanish-speaking students as they practice conversational English.',
            'Speak directly to the student in English, keep a warm and encouraging tone, give concise explanations, and motivate them to expand on their ideas.',
            'Every response must be a well-formed JSON object with exactly the keys reply, vocabulary, grammar_tips, and follow_up_questions. Never add extra keys or commentary outside the JSON.',
            'The reply field must contain the conversational answer in plain English sentences (no Markdown) that uses "you" statements to address the student directly.',
            'Every string you output must talk to the student, never to yourself or to another assistant. Do not include phrases like "Ask the student" or "The student should" inside the JSON.',
            'Base every vocabulary item, grammar tip, and follow-up question on the student\'s most recent message and the reply you are providing right now.',
            'Each vocabulary entry must explain how the student can use the word specifically to answer your current questions or continue the present topic, and the example must be a sentence the student could actually say next.',
            'Each grammar_tips entry must be a short coaching sentence that begins with "Try", "Remember", "Consider", or "Make sure you", explicitly referencing how the student just wrote or how they can improve their next reply.',
            'Treat the follow_up_questions array as follow_up_responses: provide 2 or 3 short example replies the student could send next. Each example must be written in the first person from the student\'s perspective and must build on the specific details you just mentioned or asked about so the student feels guided.',
        ])->implode(' ');
        $guidance = collect([
            'basico' => 'Use simple vocabulary, short sentences, and clear examples. Avoid idioms and advanced grammar.',
            'intermedio' => 'Use everyday vocabulary with occasional new expressions. Provide quick notes to clarify grammar or vocabulary.',
            'avanzado' => 'Use nuanced language, challenge the student with complex questions, and highlight advanced expressions.',
        ]);
        return $base . ' ' . ($guidance->get($level, $guidance->get('intermedio')));
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeContent(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }
        if (!is_string($content) || trim($content) === '') {
            return [];
        }
        return json_decode($content, true) ?? [];
    }

    protected function client(): PendingRequest
    {
        $apiKey = Config::get('openai.api_key');
        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }
        $baseUri = rtrim(Config::get('openai.base_uri', 'https://api.openai.com/v1'), '/');
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($baseUri)->timeout(Config::get('openai.request_timeout', 30));
    }

}
