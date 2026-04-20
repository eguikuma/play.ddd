<?php

namespace Tests\Unit\UseCases\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\ValueObjects\ReadingStatus;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\MarkAsRead;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MarkAsReadTest extends TestCase
{
    #[Test]
    public function 記事を既読にできる(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $article = ReadableArticle::fromCollected('a-1', 's-1', 'Title', 'https://example.com/1', 'Excerpt', new \DateTimeImmutable);
        $repository->save($article);

        $useCase = new MarkAsRead($repository);
        $useCase->execute($article->id()->value());

        $updated = $repository->findById($article->id());
        $this->assertSame(ReadingStatus::Read, $updated->readingStatus());
    }

    #[Test]
    public function 存在しない記事を既読にすると例外が発生する(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $useCase = new MarkAsRead($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('指定された記事が見つかりません');

        $useCase->execute('non-existent-id');
    }
}
