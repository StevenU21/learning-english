<?php

namespace App\DTOs;

use App\Models\Lesson;

class UpdateLessonProgressDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly Lesson $lesson,
        public readonly array $attempts,
    ) {}
}
