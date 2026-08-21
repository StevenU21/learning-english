<?php

namespace App\Services;

use App\Ai\Agents\TutorAgent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OpenAITextChatService
{
    public function generateReply(array $messages, ?string $level = null, ?float $temperature = null): array
    {
        $normalizedMessages = $this->normalizeMessages($messages);

        if (empty($normalizedMessages)) {
            throw new \Exception('No valid messages provided.');
        }

        $lastMessage = array_pop($normalizedMessages);

        $agent = new TutorAgent(
            history: $normalizedMessages, 
            level: $level,
            temperature: $temperature ?? 0.7
        );

        $response = $agent->prompt(
            $lastMessage['content']
        );

        return [
            'content' => trim($response['content'] ?? ''),
            'vocabulary' => $this->sanitizeVocabulary($response['vocabulary'] ?? []),
            'grammarTips' => $this->sanitizeStringArray($response['grammarTips'] ?? []),
            'followUpQuestions' => $this->sanitizeStringArray($response['followUpQuestions'] ?? []),
        ];
    }

    protected function sanitizeVocabulary(iterable $items): array
    {
        return collect($items)
            ->map(fn ($i) => ['term' => trim(Arr::get($i, 'term', '')), 'definition' => trim(Arr::get($i, 'definition', ''))] +
                (trim(Arr::get($i, 'example', '')) ? ['example' => trim($i['example'])] : []))
            ->filter(fn ($e) => $e['term'] !== '' && $e['definition'] !== '')
            ->values()->toArray();
    }

    protected function sanitizeStringArray(iterable $items): array
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
}
