<?php

namespace Tests\Unit\Support;

use App\Support\MediaUrl;
use PHPUnit\Framework\TestCase;

final class MediaUrlTest extends TestCase
{
    public function test_public_media_url_contract(): void
    {
        self::assertNull(MediaUrl::public(null));
        self::assertNull(MediaUrl::public(''));
        self::assertSame('http://cdn.example.test/a.jpg', MediaUrl::public('http://cdn.example.test/a.jpg'));
        self::assertSame('https://cdn.example.test/a.jpg', MediaUrl::public('https://cdn.example.test/a.jpg'));
        self::assertSame('/storage/categories/a.jpg', MediaUrl::public('categories/a.jpg'));
    }
}
