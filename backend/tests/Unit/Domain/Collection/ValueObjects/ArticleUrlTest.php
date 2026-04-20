<?php

namespace Tests\Unit\Domain\Collection\ValueObjects;

use App\Domain\Collection\ValueObjects\ArticleUrl;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleUrlTest extends TestCase
{
    #[Test]
    public function 有効なURLで記事URLを作成できる(): void
    {
        $url = new ArticleUrl('https://example.com/article');

        $this->assertSame('https://example.com/article', $url->value());
    }

    #[Test]
    public function 空の記事URLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArticleUrl('');
    }

    #[Test]
    public function 不正な形式の記事URLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArticleUrl('not-a-url');
    }

    #[Test]
    public function httpスキーム以外の記事URLは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('http/https');

        new ArticleUrl('ftp://example.com/article');
    }
}
