<?php

namespace Tests\Unit\Domain\Tracking\Aggregates;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceId;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    #[Test]
    public function ソースを追加できる(): void
    {
        $source = Source::add(
            new SourceName('Laravel News'),
            new SourceUrl('https://laravel-news.com/feed'),
            SourceKind::Rss,
            new FetchInterval(60),
        );

        $this->assertSame('Laravel News', $source->name()->value());
        $this->assertSame('https://laravel-news.com/feed', $source->url()->value());
        $this->assertSame(SourceKind::Rss, $source->kind());
        $this->assertSame(SourceStatus::Active, $source->status());
        $this->assertSame(60, $source->fetchInterval()->minutes());
        $this->assertNull($source->lastFetchedAt());
        $this->assertTrue($source->isActive());
    }

    #[Test]
    public function アクティブなソースを一時停止できる(): void
    {
        $source = $this->createSource();

        $source->pause();

        $this->assertSame(SourceStatus::Paused, $source->status());
        $this->assertFalse($source->isActive());
    }

    #[Test]
    public function 一時停止中のソースは一時停止できない(): void
    {
        $source = $this->createSource();
        $source->pause();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('既に一時停止中のソースです');

        $source->pause();
    }

    #[Test]
    public function 一時停止中のソースを再開できる(): void
    {
        $source = $this->createSource();
        $source->pause();

        $source->resume();

        $this->assertSame(SourceStatus::Active, $source->status());
        $this->assertTrue($source->isActive());
    }

    #[Test]
    public function アクティブなソースは再開できない(): void
    {
        $source = $this->createSource();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('既にアクティブなソースです');

        $source->resume();
    }

    #[Test]
    public function 最終取得日時を記録できる(): void
    {
        $source = $this->createSource();
        $fetchedAt = new \DateTimeImmutable('2026-04-11 12:00:00');

        $source->markFetched($fetchedAt);

        $this->assertSame($fetchedAt, $source->lastFetchedAt());
    }

    #[Test]
    public function 永続化データからソースを復元できる(): void
    {
        $source = Source::reconstruct(
            new SourceId('test-id'),
            new SourceName('Test Source'),
            new SourceUrl('https://example.com/rss'),
            SourceKind::Rss,
            SourceStatus::Paused,
            new FetchInterval(120),
            new \DateTimeImmutable('2026-04-01 00:00:00'),
            new \DateTimeImmutable('2026-04-10 12:00:00'),
        );

        $this->assertSame('test-id', $source->id()->value());
        $this->assertSame(SourceStatus::Paused, $source->status());
        $this->assertNotNull($source->lastFetchedAt());
    }

    private function createSource(): Source
    {
        return Source::add(
            new SourceName('Test Source'),
            new SourceUrl('https://example.com/rss'),
            SourceKind::Rss,
            new FetchInterval(60),
        );
    }
}
