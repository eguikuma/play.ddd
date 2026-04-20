<?php

namespace App\Domain\Collection\Events;

use App\Domain\Collection\ValueObjects\CollectionMethod;
use App\Domain\Shared\Events\DomainEvent;

/**
 * 記事が収集されたことを表すドメインイベント
 */
class ArticleCollected implements DomainEvent
{
    public function __construct(
        public readonly string $articleId,
        public readonly string $sourceReference,
        public readonly CollectionMethod $collectionMethod,
        public readonly string $title,
        public readonly string $url,
        public readonly string $body,
        public readonly ?\DateTimeImmutable $publishedAt,
        public readonly \DateTimeImmutable $collectedAt,
    ) {}
}
