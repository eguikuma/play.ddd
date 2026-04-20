<?php

namespace App\Infrastructure\Parser;

use App\Domain\Collection\Dto\ParsedEntry;
use App\Domain\Collection\Services\ContentParser;
use Laminas\Feed\Reader\Reader;

class RssContentParser implements ContentParser
{
    public function parse(string $rawContent): array
    {
        try {
            $feed = Reader::importString($rawContent);
        } catch (\Throwable) {
            throw new \RuntimeException('フィードの解析に失敗しました');
        }
        $entries = [];

        foreach ($feed as $entry) {
            $link = $entry->getLink();

            if ($link === null || $link === '') {
                continue;
            }

            $entries[] = new ParsedEntry(
                title: $entry->getTitle() ?? '',
                url: $link,
                body: strip_tags($entry->getContent() ?? $entry->getDescription() ?? ''),
                publishedAt: $entry->getDateModified()
                    ? \DateTimeImmutable::createFromInterface($entry->getDateModified())
                    : null,
            );
        }

        return $entries;
    }
}
