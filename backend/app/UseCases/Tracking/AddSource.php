<?php

namespace App\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceUrl;

class AddSource
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
    ) {}

    public function execute(string $url, ?string $name, SourceKind $kind, int $fetchIntervalMinutes): Source
    {
        $sourceUrl = new SourceUrl($url);

        if ($this->sourceRepository->existsByUrl($sourceUrl)) {
            throw new \DomainException('このURLのソースは既に登録されています');
        }

        $sourceName = new SourceName($name ?? parse_url($url, PHP_URL_HOST) ?? $url);

        $source = Source::add(
            $sourceName,
            $sourceUrl,
            $kind,
            new FetchInterval($fetchIntervalMinutes),
        );

        $this->sourceRepository->save($source);

        return $source;
    }
}
