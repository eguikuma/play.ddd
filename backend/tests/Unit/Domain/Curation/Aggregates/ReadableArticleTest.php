<?php

namespace Tests\Unit\Domain\Curation\Aggregates;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\ReadingStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReadableArticleTest extends TestCase
{
    #[Test]
    public function 収集記事から閲覧可能記事を生成できる(): void
    {
        $article = $this->createArticle();

        $this->assertNotEmpty($article->id()->value());
        $this->assertSame('article-123', $article->id()->value());
        $this->assertSame('source-456', $article->sourceId());
        $this->assertSame('Test Article', $article->title());
        $this->assertSame(ReadingStatus::Unread, $article->readingStatus());
        $this->assertTrue($article->isUnread());
        $this->assertFalse($article->bookmarked());
        $this->assertNull($article->readAt());
        $this->assertEmpty($article->labels());
    }

    #[Test]
    public function 記事を既読にできる(): void
    {
        $article = $this->createArticle();

        $article->markAsRead();

        $this->assertSame(ReadingStatus::Read, $article->readingStatus());
        $this->assertFalse($article->isUnread());
        $this->assertNotNull($article->readAt());
    }

    #[Test]
    public function 既読の記事を再度既読にしても状態は変わらない(): void
    {
        $article = $this->createArticle();
        $article->markAsRead();
        $firstReadAt = $article->readAt();

        $article->markAsRead();

        $this->assertSame($firstReadAt, $article->readAt());
    }

    #[Test]
    public function 記事をブックマークできる(): void
    {
        $article = $this->createArticle();

        $article->bookmark();

        $this->assertTrue($article->bookmarked());
    }

    #[Test]
    public function ブックマークを解除できる(): void
    {
        $article = $this->createArticle();
        $article->bookmark();

        $article->unbookmark();

        $this->assertFalse($article->bookmarked());
    }

    #[Test]
    public function ラベルを追加できる(): void
    {
        $article = $this->createArticle();

        $article->addLabel(new Label('php'));

        $this->assertCount(1, $article->labels());
        $this->assertSame('php', $article->labels()[0]->value());
    }

    #[Test]
    public function 同じラベルは重複して追加されない(): void
    {
        $article = $this->createArticle();

        $article->addLabel(new Label('php'));
        $article->addLabel(new Label('php'));

        $this->assertCount(1, $article->labels());
    }

    #[Test]
    public function ラベルを削除できる(): void
    {
        $article = $this->createArticle();
        $article->addLabel(new Label('php'));
        $article->addLabel(new Label('laravel'));

        $article->removeLabel(new Label('php'));

        $this->assertCount(1, $article->labels());
        $this->assertSame('laravel', $article->labels()[0]->value());
    }

    private function createArticle(): ReadableArticle
    {
        return ReadableArticle::fromCollected(
            articleId: 'article-123',
            sourceId: 'source-456',
            title: 'Test Article',
            url: 'https://example.com/article',
            body: 'This is a test article.',
            publishedAt: new \DateTimeImmutable('2026-04-01 10:00:00'),
        );
    }
}
