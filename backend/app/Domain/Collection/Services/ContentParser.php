<?php

namespace App\Domain\Collection\Services;

use App\Domain\Collection\Dto\ParsedEntry;

/**
 * ソースの生コンテンツをパースするインターフェース
 */
interface ContentParser
{
    /**
     * @return ParsedEntry[]
     */
    public function parse(string $rawContent): array;
}
