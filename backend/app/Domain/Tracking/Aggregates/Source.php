<?php

namespace App\Domain\Tracking\Aggregates;

use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceId;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;

/**
 * 情報ソースの集約ルート
 *
 * 追跡対象の情報源を表す
 * ソースの登録・一時停止・再開・取得日時の記録を管理する
 */
class Source
{
    private SourceStatus $status;

    private ?\DateTimeImmutable $lastFetchedAt;

    private function __construct(
        private readonly SourceId $id,
        private readonly SourceName $name,
        private readonly SourceUrl $url,
        private readonly SourceKind $kind,
        SourceStatus $status,
        private readonly FetchInterval $fetchInterval,
        private readonly \DateTimeImmutable $registeredAt,
        ?\DateTimeImmutable $lastFetchedAt,
    ) {
        $this->status = $status;
        $this->lastFetchedAt = $lastFetchedAt;
    }

    /**
     * 新しいソースを追加する
     */
    public static function add(
        SourceName $name,
        SourceUrl $url,
        SourceKind $kind,
        FetchInterval $fetchInterval,
    ): self {
        return new self(
            id: SourceId::generate(),
            name: $name,
            url: $url,
            kind: $kind,
            status: SourceStatus::Active,
            fetchInterval: $fetchInterval,
            registeredAt: new \DateTimeImmutable,
            lastFetchedAt: null,
        );
    }

    /**
     * 永続化されたデータからソースを復元する
     */
    public static function reconstruct(
        SourceId $id,
        SourceName $name,
        SourceUrl $url,
        SourceKind $kind,
        SourceStatus $status,
        FetchInterval $fetchInterval,
        \DateTimeImmutable $registeredAt,
        ?\DateTimeImmutable $lastFetchedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            url: $url,
            kind: $kind,
            status: $status,
            fetchInterval: $fetchInterval,
            registeredAt: $registeredAt,
            lastFetchedAt: $lastFetchedAt,
        );
    }

    /**
     * ソースの追跡を一時停止する
     */
    public function pause(): void
    {
        if ($this->status === SourceStatus::Paused) {
            throw new \DomainException('既に一時停止中のソースです');
        }

        $this->status = SourceStatus::Paused;
    }

    /**
     * ソースの追跡を再開する
     */
    public function resume(): void
    {
        if ($this->status === SourceStatus::Active) {
            throw new \DomainException('既にアクティブなソースです');
        }

        $this->status = SourceStatus::Active;
    }

    /**
     * 最終取得日時を記録する
     */
    public function markFetched(\DateTimeImmutable $fetchedAt): void
    {
        $this->lastFetchedAt = $fetchedAt;
    }

    public function id(): SourceId
    {
        return $this->id;
    }

    public function name(): SourceName
    {
        return $this->name;
    }

    public function url(): SourceUrl
    {
        return $this->url;
    }

    public function kind(): SourceKind
    {
        return $this->kind;
    }

    public function status(): SourceStatus
    {
        return $this->status;
    }

    public function fetchInterval(): FetchInterval
    {
        return $this->fetchInterval;
    }

    public function registeredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function lastFetchedAt(): ?\DateTimeImmutable
    {
        return $this->lastFetchedAt;
    }

    public function isActive(): bool
    {
        return $this->status === SourceStatus::Active;
    }
}
