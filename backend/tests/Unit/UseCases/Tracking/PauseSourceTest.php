<?php

namespace Tests\Unit\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\PauseSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PauseSourceTest extends TestCase
{
    #[Test]
    public function アクティブなソースを一時停止できる(): void
    {
        $repository = new InMemorySourceRepository;
        $source = Source::add(new SourceName('Test'), new SourceUrl('https://example.com/rss'), SourceKind::Rss, new FetchInterval(60));
        $repository->save($source);

        $useCase = new PauseSource($repository);
        $useCase->execute($source->id()->value());

        $updated = $repository->findById($source->id());
        $this->assertSame(SourceStatus::Paused, $updated->status());
    }

    #[Test]
    public function 存在しないソースを一時停止すると例外が発生する(): void
    {
        $repository = new InMemorySourceRepository;
        $useCase = new PauseSource($repository);

        $this->expectException(\DomainException::class);

        $useCase->execute('non-existent-id');
    }
}
