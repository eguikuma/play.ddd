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

    public function findUnread(?Label $label = null): array
    {
        $unread = array_filter($this->articles, fn (ReadableArticle $article) => $article->isUnread());

        if ($label !== null) {
            $unread = array_filter($unread, function (ReadableArticle $article) use ($label) {
                foreach ($article->labels() as $attachedLabel) {
                    if ($attachedLabel->equals($label)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return array_values($unread);
    }
}
