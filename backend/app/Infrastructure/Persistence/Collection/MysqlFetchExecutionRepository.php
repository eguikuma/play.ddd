<?php

namespace App\Infrastructure\Persistence\Collection;

use App\Domain\Collection\Aggregates\FetchExecution;
use App\Domain\Collection\Repositories\FetchExecutionRepository;

class MysqlFetchExecutionRepository implements FetchExecutionRepository
{
    public function save(FetchExecution $execution): void
    {
        EloquentFetchExecution::updateOrCreate(
            ['id' => $execution->id()->value()],
            [
                'source_id' => $execution->sourceReference()->value(),
                'status' => $execution->status()->value,
                'new_article_count' => $execution->newArticleCount(),
                'skipped_article_count' => $execution->skippedArticleCount(),
                'started_at' => $execution->startedAt(),
                'finished_at' => $execution->finishedAt(),
                'failure_reason' => $execution->failureReason(),
            ],
        );
    }
}
