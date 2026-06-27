<?php

namespace App\Support\Seo;

class SeoData
{
    /**
     * @param  array<int, array{locale: string, url: string}>  $alternates
     * @param  array<string, mixed>|null  $structuredData
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonicalUrl,
        public readonly string $robots,
        public readonly string $ogTitle,
        public readonly string $ogDescription,
        public readonly ?string $ogImage,
        public readonly array $alternates = [],
        public readonly ?string $xDefaultUrl = null,
        public readonly ?array $structuredData = null,
    ) {}
}
