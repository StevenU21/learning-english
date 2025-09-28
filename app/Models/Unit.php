<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'expected_time',
        'image',
        'level_id',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    /**
     * Handle image file uploads and deletion.
     */
    public function setImageAttribute($value)
    {
        // If a new file is uploaded, store it and delete old one
        if ($value instanceof \Illuminate\Http\UploadedFile) {
            // Delete old file if exists
            if (!empty($this->attributes['image'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->attributes['image']);
            }
            // Store new file in 'units' directory
            $this->attributes['image'] = $value->store('units', 'public');
        } elseif (is_string($value) || is_null($value)) {
            // Allow setting existing path or null
            $this->attributes['image'] = $value;
        }
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function unitUserProgress()
    {
        return $this->hasMany(UnitUserProgress::class);
    }
}
