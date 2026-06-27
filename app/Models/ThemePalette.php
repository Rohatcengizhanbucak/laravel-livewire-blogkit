<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['theme_id', 'key', 'name', 'colors', 'is_default'])]
class ThemePalette extends Model
{
    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /** @return BelongsTo<Theme, $this> */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
