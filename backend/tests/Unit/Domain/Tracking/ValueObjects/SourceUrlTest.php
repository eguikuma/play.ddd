<?php

namespace Tests\Unit\Domain\Tracking\ValueObjects;

use App\Domain\Tracking\ValueObjects\SourceUrl;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourceUrlTest extends TestCase
{
    #[Test]
    public function 有効なURLでソースURLを作成できる(): void
    {
        $url = new SourceUrl('https://example.com/rss');

        $this->assertSame('https://example.com/rss', $url->value());
    }

    #[Test]
    public function 空のURLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceUrl('');
    }

    #[Test]
    public function 不正な形式のURLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceUrl('not-a-url');
    }

    #[Test]
    public function 同じURLは等しい(): void
    {
        $url1 = new SourceUrl('https://example.com/rss');
        $url2 = new SourceUrl('https://example.com/rss');

        $this->assertTrue($url1->equals($url2));
    }

    #[Test]
    public function 異なるURLは等しくない(): void
    {
        $url1 = new SourceUrl('https://example.com/rss');
        $url2 = new SourceUrl('https://other.com/rss');

        $this->assertFalse($url1->equals($url2));
    }

    #[Test]
    public function httpスキーム以外のURLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('http/https');

        new SourceUrl('ftp://example.com/rss');
    }
}
