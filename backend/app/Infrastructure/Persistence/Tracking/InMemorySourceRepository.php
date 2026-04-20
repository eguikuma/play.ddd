<?php

namespace App\Infrastructure\Persistence\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\SourceId;
use App\Domain\Tracking\ValueObjects\SourceStatus;
use App\Domain\Tracking\ValueObjects\SourceUrl;

class InMemorySourceRepository implements SourceRepository
{
    /** @var array<string, Source> */
    private array $sources = [];

    public function save(Source $source): void
    {
        $this->sources[$source->id()->value()] = $source;
    }

    public function findById(SourceId $id): ?Source
    {
        return $this->sources[$id->value()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->sources);
    }

    public function findAllActive(): array
    {
        return array_values(
            array_filter($this->sources, fn (Source $source) => $source->status() === SourceStatus::Active),
        );
    }

    public function remove(SourceId $id): void
    {
        unset($this->sources[$id->value()]);
    }

    public function existsByUrl(SourceUrl $url): bool
    {
        foreach ($this->sources as $source) {
            if ($source->url()->equals($url)) {
                return true;
            }
        }

        return false;
    }
}
