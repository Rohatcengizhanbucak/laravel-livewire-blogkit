<?php

namespace App\Support\Search;

use App\Models\Locale;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicSearch
{
    public const STORIES = 'stories';

    public const MEMBERS = 'members';

    public const TAGS = 'tags';

    /** @var array<int, string> */
    public const TYPES = [
        self::STORIES,
        self::MEMBERS,
        self::TAGS,
    ];

    public function normalizeQuery(string $query): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $query));
    }

    public function normalizeType(string $type): string
    {
        return in_array($type, self::TYPES, true) ? $type : self::STORIES;
    }

    public function isSearchable(string $query): bool
    {
        return mb_strlen($query) >= 2;
    }

    /** @return array{stories: int, members: int, tags: int} */
    public function counts(Locale $locale, string $query): array
    {
        if (! $this->isSearchable($query)) {
            return [
                self::STORIES => 0,
                self::MEMBERS => 0,
                self::TAGS => 0,
            ];
        }

        return [
            self::STORIES => $this->storyQuery($locale, $query)->count('posts.id'),
            self::MEMBERS => $this->memberQuery($locale, $query)->count(),
            self::TAGS => $this->tagQuery($locale, $query)->count(),
        ];
    }

    /** @return LengthAwarePaginator<int, Post> */
    public function stories(Locale $locale, string $query, int $page): LengthAwarePaginator
    {
        if (! $this->isSearchable($query)) {
            return $this->emptyPostPaginator($page);
        }

        $like = $this->like($query);

        return $this->storyQuery($locale, $query)
            ->withPublicRelations($locale)
            ->orderByRaw(
                'case when search_translations.title like ? then 0 when search_translations.excerpt like ? then 1 else 2 end',
                [$like, $like],
            )
            ->orderByDesc('pinned_at')
            ->orderByDesc('published_at')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
    }

    /** @return LengthAwarePaginator<int, User> */
    public function members(Locale $locale, string $query, int $page): LengthAwarePaginator
    {
        if (! $this->isSearchable($query)) {
            return $this->emptyUserPaginator($page);
        }

        return $this->memberQuery($locale, $query)
            ->select(['id', 'name'])
            ->withCount([
                'posts as public_posts_count' => fn ($builder) => $this->publicPostConstraint($builder, $locale),
            ])
            ->with([
                'posts' => fn ($builder) => $this->publicPostConstraint($builder, $locale)
                    ->withPublicRelations($locale)
                    ->orderByDesc('published_at'),
            ])
            ->orderByDesc('public_posts_count')
            ->orderBy('name')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
    }

    /** @return LengthAwarePaginator<int, Tag> */
    public function tags(Locale $locale, string $query, int $page): LengthAwarePaginator
    {
        if (! $this->isSearchable($query)) {
            return $this->emptyTagPaginator($page);
        }

        return $this->tagQuery($locale, $query)
            ->withCount([
                'posts as public_posts_count' => fn ($builder) => $this->publicPostConstraint($builder, $locale),
            ])
            ->orderByDesc('public_posts_count')
            ->orderBy('name')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
    }

    /** @return Builder<Post> */
    private function storyQuery(Locale $locale, string $query): Builder
    {
        $like = $this->like($query);

        return Post::query()
            ->published()
            ->join('post_translations as search_translations', function ($join) use ($locale): void {
                $join
                    ->on('posts.id', '=', 'search_translations.post_id')
                    ->where('search_translations.locale_id', '=', $locale->id);
            })
            ->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('search_translations.title', 'like', $like)
                    ->orWhere('search_translations.excerpt', 'like', $like)
                    ->orWhere('search_translations.content', 'like', $like);
            })
            ->select('posts.*');
    }

    /** @return Builder<User> */
    private function memberQuery(Locale $locale, string $query): Builder
    {
        $like = $this->like($query);

        return User::query()
            ->where('name', 'like', $like)
            ->whereHas('posts', fn ($builder) => $this->publicPostConstraint($builder, $locale));
    }

    /** @return Builder<Tag> */
    private function tagQuery(Locale $locale, string $query): Builder
    {
        $like = $this->like($query);

        return Tag::query()
            ->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->whereHas('posts', fn ($builder) => $this->publicPostConstraint($builder, $locale));
    }

    private function publicPostConstraint(mixed $builder, Locale $locale): mixed
    {
        return $builder
            ->published()
            ->forLocale($locale);
    }

    /** @return LengthAwarePaginator<int, Post> */
    private function emptyPostPaginator(int $page): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Post> $paginator */
        $paginator = $this->emptyPaginator($page);

        return $paginator;
    }

    /** @return LengthAwarePaginator<int, User> */
    private function emptyUserPaginator(int $page): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = $this->emptyPaginator($page);

        return $paginator;
    }

    /** @return LengthAwarePaginator<int, Tag> */
    private function emptyTagPaginator(int $page): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Tag> $paginator */
        $paginator = $this->emptyPaginator($page);

        return $paginator;
    }

    /** @return LengthAwarePaginator<int, mixed> */
    private function emptyPaginator(int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 10, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    private function like(string $query): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query).'%';
    }
}
