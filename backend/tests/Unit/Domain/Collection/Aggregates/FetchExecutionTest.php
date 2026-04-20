<?php

namespace Tests\Unit\Domain\Collection\Aggregates;

use App\Domain\Collection\Aggregates\FetchExecution;
use App\Domain\Collection\ValueObjects\FetchExecutionId;
use App\Domain\Collection\ValueObjects\FetchExecutionStatus;
use App\Domain\Collection\ValueObjects\SourceReference;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FetchExecutionTest extends TestCase
{
    #[Test]
    public function 取得実行を開始できる(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));

        $this->assertSame(FetchExecutionStatus::Running, $execution->status());
        $this->assertSame('source-123', $execution->sourceReference()->value());
        $this->assertSame(0, $execution->newArticleCount());
        $this->assertSame(0, $execution->skippedArticleCount());
        $this->assertNull($execution->finishedAt());
        $this->assertNull($execution->failureReason());
    }

    #[Test]
    public function 取得実行を成功として完了できる(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));

        $execution->succeed(5, 3);

        $this->assertSame(FetchExecutionStatus::Succeeded, $execution->status());
        $this->assertSame(5, $execution->newArticleCount());
        $this->assertSame(3, $execution->skippedArticleCount());
        $this->assertNotNull($execution->finishedAt());
    }

    #[Test]
    public function 取得実行を失敗として記録できる(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));

        $execution->fail('接続タイムアウト');

        $this->assertSame(FetchExecutionStatus::Failed, $execution->status());
        $this->assertSame('接続タイムアウト', $execution->failureReason());
        $this->assertNotNull($execution->finishedAt());
    }

    #[Test]
    public function 完了済みの取得実行は再度完了できない(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));
        $execution->succeed(5, 3);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('実行中でない取得実行は完了できません');

        $execution->succeed(1, 0);
    }

    #[Test]
    public function 完了済みの取得実行は失敗にできない(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));
        $execution->succeed(5, 3);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('実行中でない取得実行は失敗にできません');

        $execution->fail('エラー');
    }

    #[Test]
    public function 失敗済みの取得実行は再度失敗にできない(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));
        $execution->fail('最初のエラー');

        $this->expectException(\DomainException::class);

        $execution->fail('2番目のエラー');
    }

    #[Test]
    public function 負の記事数で完了することはできない(): void
    {
        $execution = FetchExecution::start(new SourceReference('source-123'));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('記事数に負の値は指定できません');

        $execution->succeed(-1, 0);
    }

    #[Test]
    public function 永続化データから取得実行を復元できる(): void
    {
        $startedAt = new \DateTimeImmutable('2026-04-11 10:00:00');
        $finishedAt = new \DateTimeImmutable('2026-04-11 10:01:00');

        $execution = FetchExecution::reconstruct(
            new FetchExecutionId('exec-id-1'),
            new SourceReference('source-123'),
            FetchExecutionStatus::Succeeded,
            10,
            3,
            $startedAt,
            $finishedAt,
            null,
        );

        $this->assertSame('exec-id-1', $execution->id()->value());
        $this->assertSame('source-123', $execution->sourceReference()->value());
        $this->assertSame(FetchExecutionStatus::Succeeded, $execution->status());
        $this->assertSame(10, $execution->newArticleCount());
        $this->assertSame(3, $execution->skippedArticleCount());
        $this->assertSame($startedAt, $execution->startedAt());
        $this->assertSame($finishedAt, $execution->finishedAt());
        $this->assertNull($execution->failureReason());
    }
}
