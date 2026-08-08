<?php

namespace App\DTOs;

class RecalcUnitProgressDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $unitId,
    ) {}
}
