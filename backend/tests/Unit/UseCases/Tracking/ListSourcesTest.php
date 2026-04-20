<?php

namespace Tests\Unit\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\ListSources;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ListSourcesTest extends TestCase
{
    #[Test]
    public function 全ソースを一覧取得できる(): void
    {
        $repository = new InMemorySourceRepository;
        $source1 = Source::add(new SourceName('Source 1'), new SourceUrl('https://example.com/rss1'), SourceKind::Rss, new FetchInterval(60));
        $source2 = Source::add(new SourceName('Source 2'), new SourceUrl('https://example.com/rss2'), SourceKind::Rss, new FetchInterval(120));
        $repository->save($source1);
        $repository->save($source2);

        $useCase = new ListSources($repository);
        $sources = $useCase->execute();

        $this->assertCount(2, $sources);
    }

    #[Test]
    public function ソースが未登録の場合は空配列を返す(): void
    {
        $repository = new InMemorySourceRepository;
        $useCase = new ListSources($repository);

        $sources = $useCase->execute();

        $this->assertEmpty($sources);
    }
}
