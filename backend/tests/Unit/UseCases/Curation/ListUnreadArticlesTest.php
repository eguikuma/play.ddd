<?php

namespace Tests\Unit\UseCases\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\ValueObjects\Label;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\ListUnreadArticles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ListUnreadArticlesTest extends TestCase
{
    #[Test]
    public function 未読記事を一覧取得できる(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $article = ReadableArticle::fromCollected('a-1', 's-1', 'Title', 'https://example.com/1', 'Excerpt', new \DateTimeImmutable);
        $repository->save($article);

        $useCase = new ListUnreadArticles($repository);
        $articles = $useCase->execute();

        $this->assertCount(1, $articles);
    }

    #[Test]
    public function 既読記事は含まれない(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $article = ReadableArticle::fromCollected('a-1', 's-1', 'Title', 'https://example.com/1', 'Excerpt', new \DateTimeImmutable);
        $article->markAsRead();
        $repository->save($article);

        $useCase = new ListUnreadArticles($repository);
        $articles = $useCase->execute();

        $this->assertEmpty($articles);
    }

    #[Test]
    public function ラベルでフィルタして未読記事を取得できる(): void
    {
        $repository = new InMemoryReadableArticleRepository;

        $labeled = ReadableArticle::fromCollected('a-1', 's-1', 'Laravel Article', 'https://example.com/1', 'Excerpt', new \DateTimeImmutable);
        $labeled->addLabel(new Label('laravel'));
        $repository->save($labeled);

        $unlabeled = ReadableArticle::fromCollected('a-2', 's-1', 'PHP Article', 'https://example.com/2', 'Excerpt', new \DateTimeImmutable);
        $repository->save($unlabeled);

        $useCase = new ListUnreadArticles($repository);
        $articles = $useCase->execute('laravel');

        $this->assertCount(1, $articles);
    }
}
