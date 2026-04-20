<?php

namespace App\UseCases\Curation;

use App\Domain\Collection\Events\ArticleCollected;
use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Curation\Services\ArticleClassifier;
use App\Domain\Curation\ValueObjects\ReadableArticleId;

/**
 * 記事が収集された際にラベルを付与するリスナー
 */
class ClassifyArticleOnCollected
{
    public function __construct(
        private readonly ReadableArticleRepository $readableArticleRepository,
        private readonly ArticleClassifier $articleClassifier,
    ) {}

    public function handle(ArticleCollected $event): void
    {
        $existing = $this->readableArticleRepository->findById(
            new ReadableArticleId($event->articleId),
        );

        if ($existing !== null) {
            return;
        }

        $readableArticle = ReadableArticle::fromCollected(
            articleId: $event->articleId,
            sourceId: $event->sourceReference,
            title: $event->title,
            url: $event->url,
            body: $event->body,
            publishedAt: $event->publishedAt,
        );

        $this->articleClassifier->classify($readableArticle);

        $this->readableArticleRepository->save($readableArticle);
    }
}
