<?php

namespace App\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class LocalFileDTO
{
    public function __construct(
        public readonly Model $model,
        public readonly string $fileAttribute,
        public readonly UploadedFile $file,
        public readonly ?string $folder = null,
    ) {}
}
