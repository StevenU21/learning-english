<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileStreak extends Model
{
    protected $fillable = [
        'profile_id',
        'activity_date',
        'minutes',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
