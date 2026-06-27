<?php

namespace App\Support\Theming;

use Illuminate\Contracts\View\View;
use Livewire\Features\SupportPageComponents\PageComponentConfig;

class ThemedPageViewFactory
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $layoutData
     */
    public function make(ThemeContext $theme, string $view, array $data, array $layoutData): View
    {
        $factory = app('view');
        $blade = $factory->make($theme->view($view), $data);

        $blade->with('layoutConfig', new PageComponentConfig(
            type: 'component',
            view: $theme->layout(),
            slotOrSection: 'slot',
            params: $layoutData,
        ));

        return $blade;
    }
}
