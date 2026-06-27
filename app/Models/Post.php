<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['author_id', 'category_id', 'status', 'cover_image', 'reading_time', 'is_featured', 'allow_index', 'published_at', 'pinned_at'])]
class Post extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'allow_index' => 'boolean',
            'published_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<PostTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /** @return MorphMany<SeoMeta, $this> */
    public function seoMetas(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'seoable');
    }
}
