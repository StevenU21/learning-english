<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAITextChatService
{
    /**
     * Generate a conversational reply tailored for English learning.
     *
     * @param array<int, array{role: string, content: string}> $messages
    * @return array{
    *     reply: string,
    *     vocabulary: array<int, array{term: string, definition: string, example?: string}>,
    *     grammar_tips: array<int, string>,
    *     follow_up_questions: array<int, string>,
    *     raw: mixed
     * }
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generateReply(array $messages, ?string $level = null, ?float $temperature = null): array
    {
        $normalizedMessages = $this->normalizeMessages($messages);

        $model = config('openai.text_chat_model', 'gpt-4o-mini');
        $temperature ??= (float) config('openai.text_chat_temperature', 0.7);
        $systemPrompt = $this->buildSystemPrompt($level);

        $payload = [
            'model' => $model,
            'temperature' => max(0, min($temperature, 2)),
            'response_format' => ['type' => 'json_object'],
            'messages' => array_merge([
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
            ], $normalizedMessages),
        ];

        $response = $this->client()->post('chat/completions', $payload);
        $response->throw();

        $data = $response->json();
        $rawContent = $data['choices'][0]['message']['content'] ?? '';

        $parsed = $this->decodeContent($rawContent);

        return [
            'reply' => $parsed['reply'] ?? (is_string($rawContent) ? $rawContent : ''),
            'vocabulary' => $this->sanitizeVocabulary($parsed['vocabulary'] ?? []),
            'grammar_tips' => $this->sanitizeStringArray($parsed['grammar_tips'] ?? []),
            'follow_up_questions' => $this->sanitizeStringArray($parsed['follow_up_questions'] ?? []),
            'raw' => $parsed,
        ];
    }

    /**
     * @param array<int, array{term?: mixed, definition?: mixed, example?: mixed}> $items
     * @return array<int, array{term: string, definition: string, example?: string}>
     */
    protected function sanitizeVocabulary(array $items): array
    {
        $clean = [];

        foreach ($items as $item) {
            $term = isset($item['term']) ? trim((string) $item['term']) : '';
            $definition = isset($item['definition']) ? trim((string) $item['definition']) : '';
            $example = isset($item['example']) ? trim((string) $item['example']) : '';

            if ($term === '' || $definition === '') {
                continue;
            }

            $entry = [
                'term' => $term,
                'definition' => $definition,
            ];

            if ($example !== '') {
                $entry['example'] = $example;
            }

            $clean[] = $entry;
        }

        return $clean;
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, string>
     */
    protected function sanitizeStringArray(array $items): array
    {
        $clean = [];

        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value !== '') {
                $clean[] = $value;
            }
        }

        return $clean;
    }

    /**
     * @param array<int, array{role?: mixed, content?: mixed}> $messages
     * @return array<int, array{role: string, content: string}>
     */
    protected function normalizeMessages(array $messages): array
    {
        $normalized = [];

        foreach ($messages as $message) {
            $role = isset($message['role']) ? (string) $message['role'] : '';
            $content = isset($message['content']) ? trim((string) $message['content']) : '';

            if ($content === '') {
                continue;
            }

            if (!in_array($role, ['user', 'assistant'], true)) {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        return $normalized;
    }

    protected function buildSystemPrompt(?string $level): string
    {
        $base = implode(' ', [
            'You are Nativo, a friendly AI tutor helping Spanish-speaking students practice conversational English.',
            'Respond only in English, and keep a warm, encouraging tone.',
            'Offer concise explanations in English, include gentle corrections when needed, and encourage the student to elaborate.',
            'Use Markdown for structure (paragraphs and bullet lists) when it improves clarity.',
        ]);

        $levelGuidance = [
            'basico' => 'Use simple vocabulary, short sentences, and clear examples. Avoid idioms and advanced grammar.',
            'intermedio' => 'Use everyday vocabulary with occasional new expressions. Provide quick notes to clarify grammar or vocabulary.',
            'avanzado' => 'Use nuanced language, challenge the student with complex questions, and highlight advanced expressions.',
        ];

        $selected = $levelGuidance[$level] ?? $levelGuidance['intermedio'];

        return $base . ' ' . $selected . ' Present your answer as JSON with the keys reply, vocabulary, grammar_tips, and follow_up_questions.';
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

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function client(): PendingRequest
    {
        $apiKey = config('openai.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $baseUri = rtrim(config('openai.base_uri') ?: 'https://api.openai.com/v1', '/');

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($baseUri)->timeout(config('openai.request_timeout', 30));
    }
}
