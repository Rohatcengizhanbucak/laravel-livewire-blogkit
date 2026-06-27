<?php

namespace App\Support\Theming;

use App\Models\Theme;
use App\Models\ThemePalette;
use Illuminate\Support\Facades\View;

class ThemeManager
{
    /** @var array<string, string> */
    private array $allowedTokens = [
        'background' => '--blog-color-background',
        'surface' => '--blog-color-surface',
        'primary' => '--blog-color-primary',
        'accent' => '--blog-color-accent',
        'text' => '--blog-color-text',
        'muted' => '--blog-color-muted',
        'border' => '--blog-color-border',
    ];

    public function default(): ThemeContext
    {
        $theme = Theme::query()->activeDefault()->with('palettes')->first()
            ?? Theme::query()->where('is_active', true)->with('palettes')->first();

        abort_if($theme === null, 404);

        $viewPath = View::exists($theme->view_path.'.layout')
            ? $theme->view_path
            : 'themes.default';

        $palette = $theme->palettes->firstWhere('is_default', true)
            ?? $theme->palettes->first();

        return new ThemeContext(
            theme: $theme,
            palette: $palette instanceof ThemePalette ? $palette : null,
            viewPath: $viewPath,
            cssVariables: $this->cssVariables($palette instanceof ThemePalette ? $palette : null),
        );
    }

    private function cssVariables(?ThemePalette $palette): string
    {
        if ($palette === null) {
            return '';
        }

        $colors = $palette->getAttribute('colors');
        $variables = [];

        if (! is_array($colors)) {
            return '';
        }

        foreach ($this->allowedTokens as $token => $variable) {
            $value = $colors[$token] ?? null;

            if (is_string($value) && preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value) === 1) {
                $variables[] = $variable.': '.$value;
            }
        }

        return implode('; ', $variables);
    }
}
