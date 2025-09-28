<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitUserProgress extends Model
{
    protected $table = 'unit_user_progress';

    protected $fillable = [
        'user_id',
        'unit_id',
        'progress',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
