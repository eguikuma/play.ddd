<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 取得実行の状態
 */
enum FetchExecutionStatus: string
{
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
