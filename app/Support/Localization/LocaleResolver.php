<?php

namespace App\Support\Localization;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Collection;

class LocaleResolver
{
    public function resolve(string $code): Locale
    {
        $locale = Locale::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        abort_if($locale === null, 404);

        app()->setLocale($locale->code);

        return $locale;
    }

    public function default(): Locale
    {
        $locale = Locale::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('sort_order')
            ->first()
            ?? Locale::query()->where('is_active', true)->orderBy('sort_order')->first();

        abort_if($locale === null, 404);

        app()->setLocale($locale->code);

        return $locale;
    }

    /** @return Collection<int, Locale> */
    public function active(): Collection
    {
        return Locale::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
