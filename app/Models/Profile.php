<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar',
        'nickname',
        'birthdate',
        'daily_goal_minutes',
        'total_minutes',
        'streak_days',
        'gender',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la URL pública del avatar almacenado en disco o devuelve URL absoluta si está definida.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        // If avatar is an absolute URL, return it directly
        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }
        return asset('storage/' . $this->avatar);
    }
}
