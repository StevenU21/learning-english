<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'google_id',
        'github_id',
        'github_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'github_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Append name attribute for JSON serialization.
     *
     * @var array<string>
     */
    protected $appends = ['name'];

    /**
     * Get the user's name attribute for JSON.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get the user's short name (first name and first last name).
     */
    public function getShortNameAttribute(): string
    {
        $firstName = $this->first_name;
        $lastName = $this->last_name;
        $firstLastName = $lastName ? explode(' ', trim($lastName))[0] : '';
        return trim("$firstName $firstLastName");
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function userExerciseAttempts()
    {
        return $this->hasMany(UserExerciseAttempt::class);
    }

    public function lessonUserProgress()
    {
        return $this->hasMany(LessonUserProgress::class);
    }

    public function unitUserProgress()
    {
        return $this->hasMany(UnitUserProgress::class);
    }
}
