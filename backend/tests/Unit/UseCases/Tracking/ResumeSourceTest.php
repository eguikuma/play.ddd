<?php

namespace Tests\Unit\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\ResumeSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResumeSourceTest extends TestCase
{
    #[Test]
    public function 一時停止中のソースを再開できる(): void
    {
        $repository = new InMemorySourceRepository;
        $source = Source::add(new SourceName('Test'), new SourceUrl('https://example.com/rss'), SourceKind::Rss, new FetchInterval(60));
        $source->pause();
        $repository->save($source);

        $useCase = new ResumeSource($repository);
        $useCase->execute($source->id()->value());

        $updated = $repository->findById($source->id());
        $this->assertSame(SourceStatus::Active, $updated->status());
    }

    #[Test]
    public function 存在しないソースを再開すると例外が発生する(): void
    {
        $repository = new InMemorySourceRepository;
        $useCase = new ResumeSource($repository);

        $this->expectException(\DomainException::class);

        $useCase->execute('non-existent-id');
    }
}
