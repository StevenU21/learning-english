<?php

namespace App\Models;

use App\Traits\HasResolveModelBinding;
use App\Traits\HasRouteKeyName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Unit extends Model
{
    use HasFactory, HasSlug, HasResolveModelBinding, HasRouteKeyName;
    protected $fillable = [
        'name',
        'description',
        'expected_time',
        'image',
        'level_id',
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
            $this->attributes['image'] = $value->store('units', 'public');
        } elseif (is_string($value) || is_null($value)) {
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
