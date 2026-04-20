<?php

namespace App\Infrastructure\Persistence\Collection;

use App\Domain\Collection\Aggregates\FetchExecution;
use App\Domain\Collection\Repositories\FetchExecutionRepository;

class InMemoryFetchExecutionRepository implements FetchExecutionRepository
{
    /** @var array<string, FetchExecution> */
    private array $executions = [];

    public function save(FetchExecution $execution): void
    {
        $this->executions[$execution->id()->value()] = $execution;
    }

    /**
     * @return FetchExecution[]
     */
    public function all(): array
    {
        return array_values($this->executions);
    }
}
