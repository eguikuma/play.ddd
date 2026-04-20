<?php

namespace App\Infrastructure\Persistence\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\ReadableArticleId;

class InMemoryReadableArticleRepository implements ReadableArticleRepository
{
    /** @var array<string, ReadableArticle> */
    private array $articles = [];

    public function save(ReadableArticle $article): void
    {
        $this->articles[$article->id()->value()] = $article;
    }

    public function findById(ReadableArticleId $id): ?ReadableArticle
    {
        return $this->articles[$id->value()] ?? null;
    }

    public function findUnread(?Label $labelFilter = null): array
    {
        $unread = array_filter($this->articles, fn (ReadableArticle $readableArticle) => $readableArticle->isUnread());

        if ($labelFilter !== null) {
            $unread = array_filter($unread, function (ReadableArticle $readableArticle) use ($labelFilter) {
                foreach ($readableArticle->labels() as $label) {
                    if ($label->equals($labelFilter)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return array_values($unread);
    }
}
