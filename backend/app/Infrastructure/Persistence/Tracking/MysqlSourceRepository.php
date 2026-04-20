<?php

namespace App\Infrastructure\Persistence\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceId;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;

class MysqlSourceRepository implements SourceRepository
{
    public function save(Source $source): void
    {
        EloquentSource::updateOrCreate(
            ['id' => $source->id()->value()],
            [
                'name' => $source->name()->value(),
                'url' => $source->url()->value(),
                'kind' => $source->kind()->value,
                'status' => $source->status()->value,
                'fetch_interval_minutes' => $source->fetchInterval()->minutes(),
                'registered_at' => $source->registeredAt(),
                'last_fetched_at' => $source->lastFetchedAt(),
            ],
        );
    }

    public function findById(SourceId $id): ?Source
    {
        $source = EloquentSource::find($id->value());

        return $source ? $this->toDomain($source) : null;
    }

    public function findAll(): array
    {
        return EloquentSource::all()
            ->map(fn (EloquentSource $source) => $this->toDomain($source))
            ->all();
    }

    public function findAllActive(): array
    {
        return EloquentSource::where('status', SourceStatus::Active->value)
            ->get()
            ->map(fn (EloquentSource $source) => $this->toDomain($source))
            ->all();
    }

    public function remove(SourceId $id): void
    {
        EloquentSource::destroy($id->value());
    }

    public function existsByUrl(SourceUrl $url): bool
    {
        return EloquentSource::where('url', $url->value())->exists();
    }

    private function toDomain(EloquentSource $source): Source
    {
        return Source::reconstruct(
            new SourceId($source->id),
            new SourceName($source->name),
            new SourceUrl($source->url),
            SourceKind::from($source->kind),
            SourceStatus::from($source->status),
            new FetchInterval($source->fetch_interval_minutes),
            new \DateTimeImmutable($source->registered_at),
            $source->last_fetched_at ? new \DateTimeImmutable($source->last_fetched_at) : null,
        );
    }
}
