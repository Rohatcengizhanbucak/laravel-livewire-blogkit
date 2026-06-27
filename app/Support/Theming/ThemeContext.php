<?php

namespace App\Support\Theming;

use App\Models\Theme;
use App\Models\ThemePalette;

class ThemeContext
{
    public function __construct(
        public readonly Theme $theme,
        public readonly ?ThemePalette $palette,
        public readonly string $viewPath,
        public readonly string $cssVariables,
    ) {}

    public function view(string $name): string
    {
        return $this->viewPath.'.'.$name;
    }

    public function layout(): string
    {
        return $this->view('layout');
    }
}
