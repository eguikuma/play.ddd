<?php

namespace App\Domain\Collection\Aggregates;

use App\Domain\Collection\ValueObjects\FetchExecutionId;
use App\Domain\Collection\ValueObjects\FetchExecutionStatus;
use App\Domain\Collection\ValueObjects\SourceReference;

/**
 * 取得実行の集約
 *
 * ソースからの取得処理の実行記録を表す
 * 開始・成功・失敗の状態遷移を管理する
 */
class FetchExecution
{
    private FetchExecutionStatus $status;

    private int $newArticleCount;

    private int $skippedArticleCount;

    private ?\DateTimeImmutable $finishedAt;

    private ?string $failureReason;

    private function __construct(
        private readonly FetchExecutionId $id,
        private readonly SourceReference $sourceReference,
        FetchExecutionStatus $status,
        int $newArticleCount,
        int $skippedArticleCount,
        private readonly \DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $finishedAt,
        ?string $failureReason,
    ) {
        $this->status = $status;
        $this->newArticleCount = $newArticleCount;
        $this->skippedArticleCount = $skippedArticleCount;
        $this->finishedAt = $finishedAt;
        $this->failureReason = $failureReason;
    }

    /**
     * 取得実行を開始する
     */
    public static function start(SourceReference $sourceReference): self
    {
        return new self(
            id: FetchExecutionId::generate(),
            sourceReference: $sourceReference,
            status: FetchExecutionStatus::Running,
            newArticleCount: 0,
            skippedArticleCount: 0,
            startedAt: new \DateTimeImmutable,
            finishedAt: null,
            failureReason: null,
        );
    }

    /**
     * 永続化されたデータから取得実行を復元する
     */
    public static function reconstruct(
        FetchExecutionId $id,
        SourceReference $sourceReference,
        FetchExecutionStatus $status,
        int $newArticleCount,
        int $skippedArticleCount,
        \DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $finishedAt,
        ?string $failureReason,
    ): self {
        return new self(
            id: $id,
            sourceReference: $sourceReference,
            status: $status,
            newArticleCount: $newArticleCount,
            skippedArticleCount: $skippedArticleCount,
            startedAt: $startedAt,
            finishedAt: $finishedAt,
            failureReason: $failureReason,
        );
    }

    /**
     * 取得実行を成功として完了する
     */
    public function succeed(int $newArticleCount, int $skippedArticleCount): void
    {
        if ($this->status !== FetchExecutionStatus::Running) {
            throw new \DomainException('実行中でない取得実行は完了できません');
        }

        if ($newArticleCount < 0 || $skippedArticleCount < 0) {
            throw new \DomainException('記事数に負の値は指定できません');
        }

        $this->status = FetchExecutionStatus::Succeeded;
        $this->newArticleCount = $newArticleCount;
        $this->skippedArticleCount = $skippedArticleCount;
        $this->finishedAt = new \DateTimeImmutable;
    }

    /**
     * 取得実行を失敗として記録する
     */
    public function fail(string $reason): void
    {
        if ($this->status !== FetchExecutionStatus::Running) {
            throw new \DomainException('実行中でない取得実行は失敗にできません');
        }

        $this->status = FetchExecutionStatus::Failed;
        $this->failureReason = $reason;
        $this->finishedAt = new \DateTimeImmutable;
    }

    public function id(): FetchExecutionId
    {
        return $this->id;
    }

    public function sourceReference(): SourceReference
    {
        return $this->sourceReference;
    }

    public function status(): FetchExecutionStatus
    {
        return $this->status;
    }

    public function newArticleCount(): int
    {
        return $this->newArticleCount;
    }

    public function skippedArticleCount(): int
    {
        return $this->skippedArticleCount;
    }

    public function startedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function finishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function failureReason(): ?string
    {
        return $this->failureReason;
    }
}
