<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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

    /** @return HasMany<ThemePalette, $this> */
    public function palettes(): HasMany
    {
        return $this->hasMany(ThemePalette::class);
    }
}
