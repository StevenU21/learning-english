<?php

namespace App\DTOs;

use App\Models\User;

class ProfileStatsDTO
{
    public function __construct(
        public readonly User $user,
    ) {}
}
