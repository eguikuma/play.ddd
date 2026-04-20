<?php

namespace App\Domain\Collection\Dto;

/**
 * パーサーが返す1件分のエントリ
 */
class ParsedEntry
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $body,
        public readonly ?\DateTimeImmutable $publishedAt,
    ) {}
}
