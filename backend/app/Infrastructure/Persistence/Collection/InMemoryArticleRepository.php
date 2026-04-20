<?php

namespace App\Infrastructure\Persistence\Collection;

use App\Domain\Collection\Aggregates\Article;
use App\Domain\Collection\Repositories\ArticleRepository;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;

class InMemoryArticleRepository implements ArticleRepository
{
    /** @var array<string, Article> */
    private array $articles = [];

    public function save(Article $article): void
    {
        $this->articles[$article->id()->value()] = $article;
    }

    public function existsByFingerprint(ContentFingerprint $fingerprint): bool
    {
        foreach ($this->articles as $article) {
            if ($article->fingerprint()->equals($fingerprint)) {
                return true;
            }
        }

        return false;
    }

    public function findBySourceReference(SourceReference $sourceReference): array
    {
        return array_values(
            array_filter(
                $this->articles,
                fn (Article $article) => $article->sourceReference()->equals($sourceReference),
            ),
        );
    }
}
