<?php

namespace Tests\Unit\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\RemoveSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RemoveSourceTest extends TestCase
{
    #[Test]
    public function ソースを削除できる(): void
    {
        $repository = new InMemorySourceRepository;
        $source = Source::add(new SourceName('Test'), new SourceUrl('https://example.com/rss'), SourceKind::Rss, new FetchInterval(60));
        $repository->save($source);

        $useCase = new RemoveSource($repository);
        $useCase->execute($source->id()->value());

        $this->assertNull($repository->findById($source->id()));
    }

    #[Test]
    public function 存在しないソースを削除すると例外が発生する(): void
    {
        $repository = new InMemorySourceRepository;
        $useCase = new RemoveSource($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('指定されたソースが見つかりません');

        $useCase->execute('non-existent-id');
    }
}
