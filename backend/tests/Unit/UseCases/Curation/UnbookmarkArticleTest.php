<?php

namespace Tests\Unit\UseCases\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\UnbookmarkArticle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UnbookmarkArticleTest extends TestCase
{
    #[Test]
    public function ブックマーク済みの記事を解除できる(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $article = ReadableArticle::fromCollected('a-1', 's-1', 'Title', 'https://example.com/1', 'Body', new \DateTimeImmutable);
        $article->bookmark();
        $repository->save($article);

        $useCase = new UnbookmarkArticle($repository);
        $useCase->execute($article->id()->value());

        $updated = $repository->findById($article->id());
        $this->assertFalse($updated->bookmarked());
    }

    #[Test]
    public function 存在しない記事のブックマークを解除すると例外が発生する(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $useCase = new UnbookmarkArticle($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('指定された記事が見つかりません');

        $useCase->execute('non-existent-id');
    }
}
