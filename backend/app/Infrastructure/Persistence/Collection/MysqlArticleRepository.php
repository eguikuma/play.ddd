<?php

namespace App\Infrastructure\Persistence\Collection;

use App\Domain\Collection\Aggregates\Article;
use App\Domain\Collection\Repositories\ArticleRepository;
use App\Domain\Collection\ValueObjects\ArticleBody;
use App\Domain\Collection\ValueObjects\ArticleId;
use App\Domain\Collection\ValueObjects\ArticleTitle;
use App\Domain\Collection\ValueObjects\ArticleUrl;
use App\Domain\Collection\ValueObjects\CollectionMethod;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;

class MysqlArticleRepository implements ArticleRepository
{
    public function save(Article $article): void
    {
        EloquentArticle::updateOrCreate(
            ['id' => $article->id()->value()],
            [
                'source_id' => $article->sourceReference()->value(),
                'source_kind' => $article->collectionMethod()->value,
                'title' => $article->title()->value(),
                'url' => $article->url()->value(),
                'body' => $article->body()->value(),
                'fingerprint' => $article->fingerprint()->value(),
                'published_at' => $article->publishedAt(),
                'collected_at' => $article->collectedAt(),
            ],
        );
    }

    public function existsByFingerprint(ContentFingerprint $fingerprint): bool
    {
        return EloquentArticle::where('fingerprint', $fingerprint->value())->exists();
    }

    public function findBySourceReference(SourceReference $sourceReference): array
    {
        return EloquentArticle::where('source_id', $sourceReference->value())
            ->get()
            ->map(fn (EloquentArticle $article) => $this->toDomain($article))
            ->all();
    }

    private function toDomain(EloquentArticle $article): Article
    {
        return Article::reconstruct(
            new ArticleId($article->id),
            new SourceReference($article->source_id),
            CollectionMethod::from($article->source_kind),
            new ArticleTitle($article->title),
            new ArticleUrl($article->url),
            new ArticleBody($article->body ?? ''),
            new ContentFingerprint($article->fingerprint),
            $article->published_at ? new \DateTimeImmutable($article->published_at) : null,
            new \DateTimeImmutable($article->collected_at),
        );
    }
}
