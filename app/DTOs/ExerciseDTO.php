<?php

namespace App\DTOs;

use App\Http\Requests\ExerciseRequest;
use Illuminate\Http\UploadedFile;

readonly class ExerciseDTO
{
    public function __construct(
        public ?string $prompt,
        public UploadedFile|string|null $file,
        public UploadedFile|string|null $file_b,
        public ?array $options,
        public ?array $solution,
        public ?string $explanation,
        public int $exercise_type_id,
        public int $lesson_id,
    ) {}

    public static function fromRequest(ExerciseRequest $request): self
    {
        return new self(
            prompt: $request->input('prompt'),
            file: $request->file('file') ?? $request->input('file'),
            file_b: $request->file('file_b') ?? $request->input('file_b'),
            options: $request->input('options'),
            solution: $request->input('solution'),
            explanation: $request->input('explanation'),
            exercise_type_id: (int) $request->input('exercise_type_id'),
            lesson_id: (int) $request->input('lesson_id'),
        );
    }

    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'file' => $this->file,
            'file_b' => $this->file_b,
            'options' => $this->options,
            'solution' => $this->solution,
            'explanation' => $this->explanation,
            'exercise_type_id' => $this->exercise_type_id,
            'lesson_id' => $this->lesson_id,
        ];
    }
}
