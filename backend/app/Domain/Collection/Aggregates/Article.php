<?php

namespace App\Domain\Collection\Aggregates;

use App\Domain\Collection\ValueObjects\ArticleBody;
use App\Domain\Collection\ValueObjects\ArticleId;
use App\Domain\Collection\ValueObjects\ArticleTitle;
use App\Domain\Collection\ValueObjects\ArticleUrl;
use App\Domain\Collection\ValueObjects\CollectionMethod;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;

/**
 * 収集された記事の集約ルート
 *
 * ソースから取得・パースされた記事を表す
 * フィンガープリントによる重複排除の単位となる
 */
class Article
{
    private function __construct(
        private readonly ArticleId $id,
        private readonly SourceReference $sourceReference,
        private readonly CollectionMethod $collectionMethod,
        private readonly ArticleTitle $title,
        private readonly ArticleUrl $url,
        private readonly ArticleBody $body,
        private readonly ContentFingerprint $fingerprint,
        private readonly ?\DateTimeImmutable $publishedAt,
        private readonly \DateTimeImmutable $collectedAt,
    ) {}

    /**
     * 新しい記事を収集する
     */
    public static function collect(
        SourceReference $sourceReference,
        CollectionMethod $collectionMethod,
        ArticleTitle $title,
        ArticleUrl $url,
        ArticleBody $body,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self(
            id: ArticleId::generate(),
            sourceReference: $sourceReference,
            collectionMethod: $collectionMethod,
            title: $title,
            url: $url,
            body: $body,
            fingerprint: ContentFingerprint::fromUrl($url->value()),
            publishedAt: $publishedAt,
            collectedAt: new \DateTimeImmutable,
        );
    }

    /**
     * 永続化されたデータから記事を復元する
     */
    public static function reconstruct(
        ArticleId $id,
        SourceReference $sourceReference,
        CollectionMethod $collectionMethod,
        ArticleTitle $title,
        ArticleUrl $url,
        ArticleBody $body,
        ContentFingerprint $fingerprint,
        ?\DateTimeImmutable $publishedAt,
        \DateTimeImmutable $collectedAt,
    ): self {
        return new self(
            id: $id,
            sourceReference: $sourceReference,
            collectionMethod: $collectionMethod,
            title: $title,
            url: $url,
            body: $body,
            fingerprint: $fingerprint,
            publishedAt: $publishedAt,
            collectedAt: $collectedAt,
        );
    }

    public function id(): ArticleId
    {
        return $this->id;
    }

    public function sourceReference(): SourceReference
    {
        return $this->sourceReference;
    }

    public function collectionMethod(): CollectionMethod
    {
        return $this->collectionMethod;
    }

    public function title(): ArticleTitle
    {
        return $this->title;
    }

    public function url(): ArticleUrl
    {
        return $this->url;
    }

    public function body(): ArticleBody
    {
        return $this->body;
    }

    public function fingerprint(): ContentFingerprint
    {
        return $this->fingerprint;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function collectedAt(): \DateTimeImmutable
    {
        return $this->collectedAt;
    }
}
