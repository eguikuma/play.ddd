<?php

namespace App\Domain\Collection\Repositories;

use App\Domain\Collection\Aggregates\FetchExecution;

/**
 * 取得実行リポジトリのインターフェース
 */
interface FetchExecutionRepository
{
    public function save(FetchExecution $execution): void;
}
