<?php

namespace App\DTOs;

use App\Models\Lesson;
use App\Models\User;

class AddLessonActivityDTO
{
    public function __construct(
        public readonly User $user,
        public readonly Lesson $lesson,
    ) {}
}
