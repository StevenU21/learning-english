<?php

namespace App\DTOs;

use App\Models\User;

class UserStreakDTO
{
    public function __construct(
        public readonly User $user,
    ) {}
}
