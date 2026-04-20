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
        $record = EloquentReadableArticle::with('labels')->find($id->value());

        return $record ? $this->toDomain($record) : null;
    }

    public function findUnread(?Label $labelFilter = null): array
    {
        $query = EloquentReadableArticle::with('labels')
            ->where('reading_status', ReadingStatus::Unread->value);

        if ($labelFilter !== null) {
            $query->whereHas('labels', fn ($query) => $query->where('value', $labelFilter->value()));
        }

        return $query->get()
            ->map(fn (EloquentReadableArticle $record) => $this->toDomain($record))
            ->all();
    }

    private function toDomain(EloquentReadableArticle $record): ReadableArticle
    {
        $labels = $record->labels->map(fn ($labelRecord) => new Label($labelRecord->value))->all();

        return ReadableArticle::reconstruct(
            new ReadableArticleId($record->id),
            $record->source_id,
            $record->title,
            $record->url,
            $record->body ?? '',
            $record->published_at ? new \DateTimeImmutable($record->published_at) : null,
            ReadingStatus::from($record->reading_status),
            (bool) $record->bookmarked,
            $record->read_at ? new \DateTimeImmutable($record->read_at) : null,
            $labels,
        );
    }
}
