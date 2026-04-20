<?php

namespace App\Domain\Curation\Aggregates;

use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\ReadableArticleId;
use App\Domain\Curation\ValueObjects\ReadingStatus;

/**
 * 閲覧可能記事の集約ルート
 *
 * 収集された記事をユーザーが閲覧・管理するための表現
 * 既読/未読状態、ブックマーク、ラベルを管理する
 */
class ReadableArticle
{
    private ReadingStatus $readingStatus;

    private bool $bookmarked;

    private ?\DateTimeImmutable $readAt;

    /** @var Label[] */
    private array $labels;

    /**
     * @param  Label[]  $labels
     */
    private function __construct(
        private readonly ReadableArticleId $id,
        private readonly string $sourceId,
        private readonly string $title,
        private readonly string $url,
        private readonly string $body,
        private readonly ?\DateTimeImmutable $publishedAt,
        ReadingStatus $readingStatus,
        bool $bookmarked,
        ?\DateTimeImmutable $readAt,
        array $labels,
    ) {
        $this->readingStatus = $readingStatus;
        $this->bookmarked = $bookmarked;
        $this->readAt = $readAt;
        $this->labels = $labels;
    }

    /**
     * 収集された記事から閲覧可能記事を生成する
     *
     * Collection コンテキストの記事IDをそのまま使用する
     */
    public static function fromCollected(
        string $articleId,
        string $sourceId,
        string $title,
        string $url,
        string $body,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self(
            id: new ReadableArticleId($articleId),
            sourceId: $sourceId,
            title: $title,
            url: $url,
            body: $body,
            publishedAt: $publishedAt,
            readingStatus: ReadingStatus::Unread,
            bookmarked: false,
            readAt: null,
            labels: [],
        );
    }

    /**
     * 永続化されたデータから閲覧可能記事を復元する
     *
     * @param  Label[]  $labels
     */
    public static function reconstruct(
        ReadableArticleId $id,
        string $sourceId,
        string $title,
        string $url,
        string $body,
        ?\DateTimeImmutable $publishedAt,
        ReadingStatus $readingStatus,
        bool $bookmarked,
        ?\DateTimeImmutable $readAt,
        array $labels,
    ): self {
        return new self(
            id: $id,
            sourceId: $sourceId,
            title: $title,
            url: $url,
            body: $body,
            publishedAt: $publishedAt,
            readingStatus: $readingStatus,
            bookmarked: $bookmarked,
            readAt: $readAt,
            labels: $labels,
        );
    }

    /**
     * 記事を既読にする
     */
    public function markAsRead(): void
    {
        if ($this->readingStatus === ReadingStatus::Read) {
            return;
        }

        $this->readingStatus = ReadingStatus::Read;
        $this->readAt = new \DateTimeImmutable;
    }

    /**
     * 記事をブックマークする
     */
    public function bookmark(): void
    {
        $this->bookmarked = true;
    }

    /**
     * ブックマークを解除する
     */
    public function unbookmark(): void
    {
        $this->bookmarked = false;
    }

    /**
     * ラベルを追加する
     */
    public function addLabel(Label $label): void
    {
        foreach ($this->labels as $existingLabel) {
            if ($existingLabel->equals($label)) {
                return;
            }
        }

        $this->labels[] = $label;
    }

    /**
     * ラベルを削除する
     */
    public function removeLabel(Label $label): void
    {
        $this->labels = array_values(
            array_filter($this->labels, fn (Label $existingLabel) => ! $existingLabel->equals($label)),
        );
    }

    public function id(): ReadableArticleId
    {
        return $this->id;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function readingStatus(): ReadingStatus
    {
        return $this->readingStatus;
    }

    public function isUnread(): bool
    {
        return $this->readingStatus === ReadingStatus::Unread;
    }

    public function bookmarked(): bool
    {
        return $this->bookmarked;
    }

    public function readAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    /**
     * @return Label[]
     */
    public function labels(): array
    {
        return $this->labels;
    }
}
