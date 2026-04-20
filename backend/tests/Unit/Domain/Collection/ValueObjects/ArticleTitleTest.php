<?php

namespace Tests\Unit\Domain\Collection\ValueObjects;

use App\Domain\Collection\ValueObjects\ArticleTitle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleTitleTest extends TestCase
{
    #[Test]
    public function 記事タイトルを作成できる(): void
    {
        $title = new ArticleTitle('Laravel 12 Released');

        $this->assertSame('Laravel 12 Released', $title->value());
    }

    #[Test]
    public function 空の記事タイトルは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArticleTitle('');
    }

    #[Test]
    public function 空白のみの記事タイトルは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArticleTitle('   ');
    }

    #[Test]
    public function 前後の空白は除去される(): void
    {
        $title = new ArticleTitle('  Laravel News  ');

        $this->assertSame('Laravel News', $title->value());
    }
}
