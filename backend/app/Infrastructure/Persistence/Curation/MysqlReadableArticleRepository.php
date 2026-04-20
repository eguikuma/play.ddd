<?php

namespace App\Infrastructure\Persistence\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\ReadableArticleId;
use App\Domain\Curation\ValueObjects\ReadingStatus;

class MysqlReadableArticleRepository implements ReadableArticleRepository
{
    public function save(ReadableArticle $article): void
    {
        EloquentReadableArticle::where('id', $article->id()->value())
            ->update([
                'reading_status' => $article->readingStatus()->value,
                'bookmarked' => $article->bookmarked(),
                'read_at' => $article->readAt(),
            ]);

        EloquentArticleLabel::where('article_id', $article->id()->value())->delete();

        foreach ($article->labels() as $label) {
            EloquentArticleLabel::create([
                'article_id' => $article->id()->value(),
                'value' => $label->value(),
            ]);
        }
    }

    public function findById(ReadableArticleId $id): ?ReadableArticle
    {
        $article = EloquentReadableArticle::with('labels')->find($id->value());

        return $article ? $this->toDomain($article) : null;
    }

    public function findUnread(?Label $label = null): array
    {
        $query = EloquentReadableArticle::with('labels')
            ->where('reading_status', ReadingStatus::Unread->value);

        if ($label !== null) {
            $query->whereHas('labels', fn ($query) => $query->where('value', $label->value()));
        }

        return $query->get()
            ->map(fn (EloquentReadableArticle $article) => $this->toDomain($article))
            ->all();
    }

    private function toDomain(EloquentReadableArticle $article): ReadableArticle
    {
        $labels = $article->labels->map(fn ($label) => new Label($label->value))->all();

        return ReadableArticle::reconstruct(
            new ReadableArticleId($article->id),
            $article->source_id,
            $article->title,
            $article->url,
            $article->body ?? '',
            $article->published_at ? new \DateTimeImmutable($article->published_at) : null,
            ReadingStatus::from($article->reading_status),
            (bool) $article->bookmarked,
            $article->read_at ? new \DateTimeImmutable($article->read_at) : null,
            $labels,
        );
    }
}
