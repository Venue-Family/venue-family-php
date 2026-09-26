<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\Data\MediaData;

class MediaDataTest extends TestCase
{
    public function test_it_generates_transformed_cdn_urls_and_srcset(): void
    {
        $media = MediaData::fromArray([
            'id' => 12,
            'name' => 'flyer.jpg',
            'url' => 'https://cdn.example.test/images/flyer.jpg',
            'width' => 1920,
            'height' => 1080,
            'aspect_ratio' => 1.78,
            'slot' => 'banner',
        ]);

        // Default url returns raw url
        $this->assertSame('https://cdn.example.test/images/flyer.jpg', $media->url);
        $this->assertSame('https://cdn.example.test/images/flyer.jpg', $media->url());

        // Transformed url with params
        $transformed = $media->url(['w' => 800, 'format' => 'webp']);
        $this->assertSame('https://cdn.example.test/images/flyer.jpg?w=800&format=webp', $transformed);

        // transformedUrl alias
        $alias = $media->transformedUrl(['w' => 1200]);
        $this->assertSame('https://cdn.example.test/images/flyer.jpg?w=1200', $alias);

        // Responsive srcset
        $srcset = $media->srcSet([400, 800, 1200], 'webp');
        $this->assertStringContainsString('https://cdn.example.test/images/flyer.jpg?w=400&format=webp 400w', $srcset);
        $this->assertStringContainsString('https://cdn.example.test/images/flyer.jpg?w=800&format=webp 800w', $srcset);
        $this->assertStringContainsString('https://cdn.example.test/images/flyer.jpg?w=1200&format=webp 1200w', $srcset);
    }
}
