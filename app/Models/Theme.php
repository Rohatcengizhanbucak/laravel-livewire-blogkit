<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'view_path', 'config', 'is_default', 'is_active'])]
class Theme extends Model
{
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @param  Builder<Theme>  $query */
    public function scopeActiveDefault(Builder $query): void
    {
        $query->where('is_active', true)->where('is_default', true);
    }

    /** @return HasMany<ThemePalette, $this> */
    public function palettes(): HasMany
    {
        return $this->hasMany(ThemePalette::class);
    }
}
