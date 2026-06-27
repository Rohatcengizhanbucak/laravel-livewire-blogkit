<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    /** @param  Builder<Post>  $query */
    public function scopePublished(Builder $query): void
    {
        $query
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /** @param  Builder<Post>  $query */
    public function scopeForLocale(Builder $query, Locale $locale): void
    {
        $query->whereHas('translations', function (Builder $query) use ($locale): void {
            $query->where('locale_id', $locale->id);
        });
    }

    /** @param  Builder<Post>  $query */
    public function scopeWithPublicRelations(Builder $query, Locale $locale): void
    {
        $query->with([
            'author',
            'category',
            'tags',
            'translations' => fn ($query) => $query->where('locale_id', $locale->id),
            'seoMetas' => fn ($query) => $query->where(function (Builder $query) use ($locale): void {
                $query->where('locale_id', $locale->id)->orWhereNull('locale_id');
            }),
        ]);
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
