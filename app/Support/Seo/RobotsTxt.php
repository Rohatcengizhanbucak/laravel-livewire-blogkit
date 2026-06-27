<?php

namespace App\Support\Seo;

class RobotsTxt
{
    public function render(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.route('sitemap'),
            '',
        ]);
    }
}
