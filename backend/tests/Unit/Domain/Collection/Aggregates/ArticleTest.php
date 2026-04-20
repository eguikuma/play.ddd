<?php

namespace Tests\Unit\Domain\Collection\Aggregates;

use App\Domain\Collection\Aggregates\Article;
use App\Domain\Collection\ValueObjects\ArticleBody;
use App\Domain\Collection\ValueObjects\ArticleId;
use App\Domain\Collection\ValueObjects\ArticleTitle;
use App\Domain\Collection\ValueObjects\ArticleUrl;
use App\Domain\Collection\ValueObjects\CollectionMethod;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    #[Test]
    public function 記事を収集できる(): void
    {
        $article = Article::collect(
            new SourceReference('source-123'),
            CollectionMethod::Rss,
            new ArticleTitle('Laravel 12 Released'),
            new ArticleUrl('https://laravel-news.com/laravel-12'),
            new ArticleBody('Laravel 12 has been released with new features.'),
            new \DateTimeImmutable('2026-04-01 10:00:00'),
        );

        $this->assertNotEmpty($article->id()->value());
        $this->assertSame('source-123', $article->sourceReference()->value());
        $this->assertSame(CollectionMethod::Rss, $article->collectionMethod());
        $this->assertSame('Laravel 12 Released', $article->title()->value());
        $this->assertSame('https://laravel-news.com/laravel-12', $article->url()->value());
        $this->assertNotEmpty($article->fingerprint()->value());
        $this->assertNotNull($article->collectedAt());
    }

    #[Test]
    public function 同じURLの記事は同じフィンガープリントになる(): void
    {
        $article1 = Article::collect(
            new SourceReference('source-123'),
            CollectionMethod::Rss,
            new ArticleTitle('Title 1'),
            new ArticleUrl('https://example.com/same-article'),
            new ArticleBody('Body 1'),
            null,
        );

        $article2 = Article::collect(
            new SourceReference('source-456'),
            CollectionMethod::Rss,
            new ArticleTitle('Title 2'),
            new ArticleUrl('https://example.com/same-article'),
            new ArticleBody('Body 2'),
            null,
        );

        $this->assertTrue($article1->fingerprint()->equals($article2->fingerprint()));
    }

    #[Test]
    public function 永続化データから記事を復元できる(): void
    {
        $article = Article::reconstruct(
            new ArticleId('article-id'),
            new SourceReference('source-123'),
            CollectionMethod::Rss,
            new ArticleTitle('Test Article'),
            new ArticleUrl('https://example.com/test'),
            new ArticleBody('Test body'),
            new ContentFingerprint('abc123hash'),
            new \DateTimeImmutable('2026-04-01 10:00:00'),
            new \DateTimeImmutable('2026-04-10 12:00:00'),
        );

        $this->assertSame('article-id', $article->id()->value());
        $this->assertSame('abc123hash', $article->fingerprint()->value());
    }
}
