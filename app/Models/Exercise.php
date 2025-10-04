<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Exercise extends Model
{
    protected $appends = [
        'file_url',
        'file_b_url',
    ];
    protected $fillable = [
        'prompt',
        'file',
        'file_b',
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

    public function getFileBUrlAttribute(): ?string
    {
        return $this->file_b ? asset('storage/' . $this->file_b) : null;
    }

    public function setFileAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            if (!empty($this->attributes['file'])) {
                $deleted = Storage::disk('public')->delete($this->attributes['file']);
                Log::info('Exercise file deleted', ['path' => $this->attributes['file'], 'deleted' => $deleted]);
            }
            $stored = $value->store('units', 'public');
            Log::info('Exercise file stored', ['path' => $stored, 'original' => $value->getClientOriginalName()]);
            $this->attributes['file'] = $stored;
        } elseif (is_string($value) || is_null($value)) {
            $this->attributes['file'] = $value;
        }
    }

    public function setFileBAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            if (!empty($this->attributes['file_b'])) {
                $deleted = Storage::disk('public')->delete($this->attributes['file_b']);
                Log::info('Exercise file_b deleted', ['path' => $this->attributes['file_b'], 'deleted' => $deleted]);
            }
            $stored = $value->store('units', 'public');
            Log::info('Exercise file_b stored', ['path' => $stored, 'original' => $value->getClientOriginalName()]);
            $this->attributes['file_b'] = $stored;
        } elseif (is_string($value) || is_null($value)) {
            $this->attributes['file_b'] = $value;
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
