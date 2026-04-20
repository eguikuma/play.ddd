<?php

namespace Tests\Unit\UseCases\Collection;

use App\Domain\Collection\Dto\ParsedEntry;
use App\Domain\Collection\Services\ContentFetcher;
use App\Domain\Collection\Services\ContentParser;
use App\Domain\Shared\Events\EventDispatcher;
use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Collection\InMemoryArticleRepository;
use App\Infrastructure\Persistence\Collection\InMemoryFetchExecutionRepository;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Collection\CollectAll;
use App\UseCases\Collection\CollectSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CollectAllTest extends TestCase
{
    #[Test]
    public function 全アクティブソースから記事を収集できる(): void
    {
        $sourceRepository = new InMemorySourceRepository;
        $source1 = Source::add(new SourceName('Source 1'), new SourceUrl('https://example.com/rss1'), SourceKind::Rss, new FetchInterval(60));
        $source2 = Source::add(new SourceName('Source 2'), new SourceUrl('https://example.com/rss2'), SourceKind::Rss, new FetchInterval(60));
        $sourceRepository->save($source1);
        $sourceRepository->save($source2);

        $fetcher = $this->createStub(ContentFetcher::class);
        $fetcher->method('fetch')->willReturn('<rss>dummy</rss>');

        $parser = $this->createStub(ContentParser::class);
        $parser->method('parse')->willReturn([
            new ParsedEntry('Article', 'https://example.com/article', 'Body', new \DateTimeImmutable),
        ]);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $collectSource = new CollectSource(
            $sourceRepository,
            new InMemoryArticleRepository,
            new InMemoryFetchExecutionRepository,
            $fetcher,
            $parser,
            $dispatcher,
        );

        $collectAll = new CollectAll($sourceRepository, $collectSource);
        $executions = $collectAll->execute();

        $this->assertCount(2, $executions);
    }

    #[Test]
    public function 一時停止中のソースはスキップされる(): void
    {
        $sourceRepository = new InMemorySourceRepository;
        $active = Source::add(new SourceName('Active'), new SourceUrl('https://example.com/rss1'), SourceKind::Rss, new FetchInterval(60));
        $paused = Source::add(new SourceName('Paused'), new SourceUrl('https://example.com/rss2'), SourceKind::Rss, new FetchInterval(60));
        $paused->pause();
        $sourceRepository->save($active);
        $sourceRepository->save($paused);

        $fetcher = $this->createStub(ContentFetcher::class);
        $fetcher->method('fetch')->willReturn('<rss>dummy</rss>');

        $parser = $this->createStub(ContentParser::class);
        $parser->method('parse')->willReturn([
            new ParsedEntry('Article', 'https://example.com/article', 'Body', new \DateTimeImmutable),
        ]);

        $dispatcher = $this->createStub(EventDispatcher::class);

        $collectSource = new CollectSource(
            $sourceRepository,
            new InMemoryArticleRepository,
            new InMemoryFetchExecutionRepository,
            $fetcher,
            $parser,
            $dispatcher,
        );

        $collectAll = new CollectAll($sourceRepository, $collectSource);
        $executions = $collectAll->execute();

        $this->assertCount(1, $executions);
    }
}
