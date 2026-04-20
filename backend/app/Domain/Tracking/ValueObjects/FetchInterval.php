<?php

namespace App\Domain\Tracking\ValueObjects;

/**
 * ソースの取得間隔（分単位）
 */
class FetchInterval
{
    private readonly int $minutes;

    public function __construct(int $minutes)
    {
        if ($minutes < 1 || $minutes > 10080) {
            throw new \InvalidArgumentException('取得間隔は1分〜10080分（1週間）の範囲で指定してください');
        }

        $this->minutes = $minutes;
    }

    public function minutes(): int
    {
        return $this->minutes;
    }
}
