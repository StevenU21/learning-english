<?php

namespace App\Models;

use App\Traits\HasResolveModelBinding;
use App\Traits\HasRouteKeyName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Lesson extends Model
{
    use HasFactory, HasSlug, HasResolveModelBinding, HasRouteKeyName;

    protected $fillable = [
        'name',
        'image',
        'duration',
        'description',
        'unit_id'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function setImageAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            if (!empty($this->attributes['image'])) {
                Storage::disk('public')->delete($this->attributes['image']);
            }
            $this->attributes['image'] = $value->store('lessons', 'public');
        } elseif (is_string($value) || is_null($value)) {
            $this->attributes['image'] = $value;
        }
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    public function lessonUserProgress()
    {
        return $this->hasMany(LessonUserProgress::class);
    }
}
