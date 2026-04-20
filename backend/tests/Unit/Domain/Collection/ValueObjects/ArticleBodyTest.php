<?php

namespace Tests\Unit\Domain\Collection\ValueObjects;

use App\Domain\Collection\ValueObjects\ArticleBody;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleBodyTest extends TestCase
{
    #[Test]
    public function 記事本文を作成できる(): void
    {
        $body = new ArticleBody('This is the article content.');

        $this->assertSame('This is the article content.', $body->value());
    }

    #[Test]
    public function 空の本文を許容する(): void
    {
        $body = new ArticleBody('');

        $this->assertSame('', $body->value());
    }

    #[Test]
    public function 長い本文の抜粋を取得できる(): void
    {
        $longText = str_repeat('あ', 600);
        $body = new ArticleBody($longText);

        $excerpt = $body->excerpt(500);

        $this->assertSame(mb_strlen($excerpt), 501);
    }

    #[Test]
    public function 短い本文はそのまま抜粋として返る(): void
    {
        $body = new ArticleBody('Short text');

        $this->assertSame('Short text', $body->excerpt(500));
    }
}
