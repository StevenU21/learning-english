<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExerciseAttempt extends Model
{
    protected $table = 'user_exercise_attempts';

    protected $fillable = [
        'user_id',
        'exercise_id',
        'attempt_number',
        'answer_given',
        'is_correct',
        'started_at',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answer_given' => 'array',
            'is_correct' => 'boolean',
            'started_at' => 'datetime',
            'answered_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function lesson()
    {
        return $this->hasOneThrough(
            Lesson::class,
            Exercise::class,
            'id', // Exercise.id
            'id', // Lesson.id
            'exercise_id', // UserExerciseAttempt.exercise_id
            'lesson_id' // Exercise.lesson_id
        );
    }
}
