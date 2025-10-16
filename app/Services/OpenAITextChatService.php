<?php

namespace App\Services;

use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class OpenAITextChatService
{
    /**
     * Generate a conversational reply tailored for English learning.
     *
     * @param array<int, array{role: string, content: string}> $messages
     *
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
     * Stream a conversational reply and yield incremental chunks for the UI.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function streamReply(array $messages, ?string $level = null, ?float $temperature = null): Generator
    {
        $normalizedMessages = $this->normalizeMessages($messages);

        $model = config('openai.text_chat_model', 'gpt-4o-mini');
        $temperature ??= (float) config('openai.text_chat_temperature', 0.7);
        $systemPrompt = $this->buildSystemPrompt($level);

        $payload = [
            'model' => $model,
            'temperature' => max(0, min($temperature, 2)),
            'messages' => array_merge([
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
            ], $normalizedMessages),
        ];

        $contentBuffer = '';

        try {
            $stream = OpenAI::chat()->createStreamed($payload);

            foreach ($stream as $chunk) {
                foreach ($chunk->choices as $choice) {
                    $delta = $choice->delta->content ?? null;

                    if (is_string($delta) && $delta !== '') {
                        $contentBuffer .= $delta;

                        yield [
                            'type' => 'delta',
                            'text' => $delta,
                        ];
                    }

                    if ($choice->finishReason === 'stop') {
                        $normalized = $this->normalizeCompletedContent($contentBuffer);

                        yield [
                            'type' => 'complete',
                            'message' => $normalized,
                        ];

                        return;
                    }
                }
            }
        } catch (ErrorException $exception) {
            $message = $exception->getErrorMessage();

            yield [
                'type' => 'error',
                'message' => $message,
            ];

            return;
        }

        if ($contentBuffer !== '') {
            $normalized = $this->normalizeCompletedContent($contentBuffer);

            yield [
                'type' => 'complete',
                'message' => $normalized,
            ];

            return;
        }

        yield [
            'type' => 'error',
            'message' => 'No se recibió ninguna respuesta válida del servicio de IA.',
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
            'You are Nativo, a friendly AI tutor guiding Spanish-speaking students as they practice conversational English.',
            'Speak directly to the student in English, use a warm and encouraging tone, give concise explanations, and motivate them to expand on their ideas.',
            'Every response must be a well-formed JSON object with the keys reply, vocabulary, grammar_tips, and follow_up_questions.',
            'The reply field must contain the conversational answer in plain English sentences (no Markdown formatting) that addresses the student using "you" language.',
            'The vocabulary field must be an array of objects with term, definition, and optional example phrased for the student (e.g., "Use this word when you talk about...").',
            'The grammar_tips array must contain short, actionable coaching statements written to the student. Each item should begin with a second-person cue such as "Try", "Remember", or "Make sure you" (e.g., "Try using the past tense when you describe..."), and must never include instructions for the assistant.',
            'The follow_up_questions array must list engaging questions that you are asking the student directly (e.g., "What hobbies do you enjoy most?") so they can continue the conversation. Do not include meta-instructions for yourself.',
        ]);

        $levelGuidance = [
            'basico' => 'Use simple vocabulary, short sentences, and clear examples. Avoid idioms and advanced grammar.',
            'intermedio' => 'Use everyday vocabulary with occasional new expressions. Provide quick notes to clarify grammar or vocabulary.',
            'avanzado' => 'Use nuanced language, challenge the student with complex questions, and highlight advanced expressions.',
        ];

        $selected = $levelGuidance[$level] ?? $levelGuidance['intermedio'];

        return $base . ' ' . $selected . ' Ensure the JSON stays valid even while being streamed progressively.';
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

    /**
     * @return array{
     *     reply: string,
     *     vocabulary: array<int, array{term: string, definition: string, example?: string}>,
     *     grammar_tips: array<int, string>,
     *     follow_up_questions: array<int, string>
     * }
     */
    protected function normalizeCompletedContent(string $content): array
    {
        $parsed = $this->decodeContent($content);

        if (empty($parsed)) {
            $parsed = $this->decodeLenientJson($content);
        }

        $reply = '';

        if (isset($parsed['reply']) && is_string($parsed['reply'])) {
            $reply = trim($parsed['reply']);
        }

        if ($reply === '') {
            $reply = $this->extractReplyFromRaw($content);
        }

        if ($reply === '') {
            $reply = 'I could not generate a detailed response this time. Please try asking again.';
        }

        return [
            'reply' => $reply,
            'vocabulary' => $this->sanitizeVocabulary($parsed['vocabulary'] ?? []),
            'grammar_tips' => $this->sanitizeStringArray($parsed['grammar_tips'] ?? []),
            'follow_up_questions' => $this->sanitizeStringArray($parsed['follow_up_questions'] ?? []),
        ];
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

    /**
     * Attempt to decode JSON that may include trailing commas or spacing glitches.
     *
     * @return array<string, mixed>
     */
    protected function decodeLenientJson(string $content): array
    {
        $candidate = trim($content);

        if ($candidate === '') {
            return [];
        }

        $attempts = [$candidate];

        $strippedTrailingComma = preg_replace('/,\s*}$/', '}', $candidate);
        if (is_string($strippedTrailingComma) && $strippedTrailingComma !== $candidate) {
            $attempts[] = $strippedTrailingComma;
        }

        foreach ($attempts as $attempt) {
            $decoded = json_decode($attempt, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Extract the reply text from a raw JSON-like string when decoding fails.
     */
    protected function extractReplyFromRaw(string $content): string
    {
        if (!preg_match('/"reply"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/u', $content, $matches)) {
            return '';
        }

        $candidate = '"' . $matches[1] . '"';

        $decoded = json_decode($candidate, true);

        if (is_string($decoded)) {
            return trim($decoded);
        }

        $fallback = stripcslashes($matches[1]);

        return trim($fallback);
    }
}
