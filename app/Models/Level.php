<?php

namespace App\Models;

use App\Traits\HasResolveModelBinding;
use App\Traits\HasRouteKeyName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Level extends Model
{
    use HasFactory, HasResolveModelBinding, HasRouteKeyName, HasSlug;

    protected $fillable = [
        'name',
        'description',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
