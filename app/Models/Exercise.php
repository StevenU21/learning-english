<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Exercise extends Model
{
    protected $fillable = [
        'prompt',
        'file',
        'options',
        'solution',
        'explanation',
        'exercise_type_id',
        'lesson_id'
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'solution' => 'array',
        ];
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }

    public function setFileAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            if (!empty($this->attributes['file'])) {
                Storage::disk('public')->delete($this->attributes['file']);
            }
            $this->attributes['file'] = $value->store('units', 'public');
        } elseif (is_string($value) || is_null($value)) {
            $this->attributes['file'] = $value;
        }
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class);
    }

    public function userExerciseAttempts()
    {
        return $this->hasMany(UserExerciseAttempt::class);
    }
}
