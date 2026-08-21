<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

class TutorAgent implements Agent, HasStructuredOutput, Conversational, \Laravel\Ai\Contracts\HasProviderOptions
{
    use Promptable;

    public function __construct(
        public array $history = [],
        public ?string $level = 'intermedio',
        public ?float $temperature = 0.7
    ) {}

    public function providerOptions(\Laravel\Ai\Enums\Lab|string $provider): array
    {
        return ['temperature' => $this->temperature];
    }

    public function instructions(): string
    {
        $base = collect([
            'You are Nativo, a friendly AI tutor guiding Spanish-speaking students as they practice conversational English.',
            'Speak directly to the student in English, keep a warm and encouraging tone, give concise explanations, and motivate them to expand on their ideas.',
            'Base everything on the student\'s most recent message. Be concise and keep the response fast.',
        ])->implode(' ');

        $guidance = collect([
            'basico' => 'Use simple vocabulary, short sentences, and clear examples. Avoid idioms and advanced grammar.',
            'intermedio' => 'Use everyday vocabulary with occasional new expressions. Provide quick notes to clarify grammar or vocabulary.',
            'avanzado' => 'Use nuanced language, challenge the student with complex questions, and highlight advanced expressions.',
        ]);

        return $base . ' ' . ($guidance->get($this->level, $guidance->get('intermedio')));
    }

    public function messages(): iterable
    {
        return collect($this->history)
            ->map(fn ($m) => new Message($m['role'], $m['content']))
            ->all();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->description('Your conversational reply to the student in plain English (using "you" statements).')->required(),
            'vocabulary' => $schema->array()
                ->items(
                    $schema->object(fn ($schema) => [
                        'term' => $schema->string()->required(),
                        'definition' => $schema->string()->required(),
                        'example' => $schema->string(),
                    ])
                )
                ->description('1 or 2 new words relevant to the conversation.')
                ->required(),
            'grammarTips' => $schema->array()
                ->items($schema->string())
                ->description('1 short, actionable grammar tip starting with "Try", "Remember", or "Consider".')
                ->required(),
            'followUpQuestions' => $schema->array()
                ->items($schema->string())
                ->description('1 short example reply the student could use next.')
                ->required(),
        ];
    }
}
