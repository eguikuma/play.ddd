<?php

namespace App\Console\Tui;

/**
 * トースト通知のメッセージと種別を保持する
 */
class Notice
{
    /**
     * トーストの最低表示時間（秒）
     */
    private const MINIMUM_DISPLAY = 2.0;

    private function __construct(
        public readonly string $message,
        public readonly bool $success,
        private readonly float $createdAt = 0,
    ) {}

    /**
     * 最低表示時間を経過したかどうかを判定する
     */
    public function expired(): bool
    {
        return (microtime(true) - $this->createdAt) >= self::MINIMUM_DISPLAY;
    }

    /**
     * 成功通知を生成する
     */
    public static function success(string $message): self
    {
        return new self($message, true, microtime(true));
    }

    /**
     * 失敗通知を生成する
     */
    public static function failure(string $message): self
    {
        return new self($message, false, microtime(true));
    }
}
