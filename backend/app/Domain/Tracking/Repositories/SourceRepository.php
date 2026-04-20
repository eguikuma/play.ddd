<?php

namespace App\Domain\Tracking\Repositories;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\SourceId;
use App\Domain\Tracking\ValueObjects\SourceUrl;

/**
 * ソースリポジトリのインターフェース
 */
interface SourceRepository
{
    public function save(Source $source): void;

    public function findById(SourceId $id): ?Source;

    /**
     * @return Source[]
     */
    public function findAll(): array;

    /**
     * @return Source[]
     */
    public function findAllActive(): array;

    public function remove(SourceId $id): void;

    public function existsByUrl(SourceUrl $url): bool;
}
