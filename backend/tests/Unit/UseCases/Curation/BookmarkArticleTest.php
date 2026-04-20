<?php

namespace Tests\Unit\UseCases\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\BookmarkArticle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BookmarkArticleTest extends TestCase
{
    #[Test]
    public function 記事をブックマークできる(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $article = ReadableArticle::fromCollected('a-1', 's-1', 'Title', 'https://example.com/1', 'Body', new \DateTimeImmutable);
        $repository->save($article);

        $useCase = new BookmarkArticle($repository);
        $useCase->execute($article->id()->value());

        $updated = $repository->findById($article->id());
        $this->assertTrue($updated->bookmarked());
    }

    #[Test]
    public function 存在しない記事をブックマークすると例外が発生する(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $useCase = new BookmarkArticle($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('指定された記事が見つかりません');

        $useCase->execute('non-existent-id');
    }
}
