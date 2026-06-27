<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['template', 'status', 'show_in_navigation', 'sort_order', 'published_at'])]
class Page extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'show_in_navigation' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /** @return HasMany<PageTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    /** @return MorphMany<SeoMeta, $this> */
    public function seoMetas(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'seoable');
    }
}
