<?php

namespace Tests\Unit\UseCases\Collection;

use App\Domain\Collection\Dto\ParsedEntry;
use App\Domain\Collection\Services\ContentFetcher;
use App\Domain\Collection\Services\ContentParser;
use App\Domain\Collection\ValueObjects\FetchExecutionStatus;
use App\Domain\Shared\Events\EventDispatcher;
use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\FetchInterval;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Domain\Tracking\ValueObjects\SourceName;
use App\Domain\Tracking\ValueObjects\SourceUrl;
use App\Infrastructure\Persistence\Collection\InMemoryArticleRepository;
use App\Infrastructure\Persistence\Collection\InMemoryFetchExecutionRepository;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Collection\CollectSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CollectSourceTest extends TestCase
{
    private InMemorySourceRepository $sourceRepository;

    private InMemoryArticleRepository $articleRepository;

    private InMemoryFetchExecutionRepository $executionRepository;

    private CollectSource $useCase;

    /** @var object[] */
    private array $dispatchedEvents = [];

    protected function setUp(): void
    {
        $this->sourceRepository = new InMemorySourceRepository;
        $this->articleRepository = new InMemoryArticleRepository;
        $this->executionRepository = new InMemoryFetchExecutionRepository;

        $fetcher = $this->createStub(ContentFetcher::class);
        $fetcher->method('fetch')->willReturn('<rss>dummy</rss>');

        $parser = $this->createStub(ContentParser::class);
        $parser->method('parse')->willReturn([
            new ParsedEntry('Article 1', 'https://example.com/1', 'Body 1', new \DateTimeImmutable),
            new ParsedEntry('Article 2', 'https://example.com/2', 'Body 2', new \DateTimeImmutable),
        ]);

        $dispatcher = $this->createStub(EventDispatcher::class);
        $dispatcher->method('dispatch')->willReturnCallback(function (object $event) {
            $this->dispatchedEvents[] = $event;
        });

        $this->useCase = new CollectSource(
            $this->sourceRepository,
            $this->articleRepository,
            $this->executionRepository,
            $fetcher,
            $parser,
            $dispatcher,
        );
    }

    #[Test]
    public function ソースから記事を収集できる(): void
    {
        $source = $this->createActiveSource();

        $execution = $this->useCase->execute($source->id()->value());

        $this->assertSame(FetchExecutionStatus::Succeeded, $execution->status());
        $this->assertSame(2, $execution->newArticleCount());
        $this->assertSame(0, $execution->skippedArticleCount());
    }

    #[Test]
    public function 収集後にイベントが発行される(): void
    {
        $source = $this->createActiveSource();

        $this->useCase->execute($source->id()->value());

        $this->assertCount(2, $this->dispatchedEvents);
    }

    #[Test]
    public function 収集後にソースの最終取得日時が更新される(): void
    {
        $source = $this->createActiveSource();
        $this->assertNull($source->lastFetchedAt());

        $this->useCase->execute($source->id()->value());

        $updated = $this->sourceRepository->findById($source->id());
        $this->assertNotNull($updated->lastFetchedAt());
    }

    #[Test]
    public function 重複する記事はスキップされる(): void
    {
        $source = $this->createActiveSource();

        $this->useCase->execute($source->id()->value());

        $parser = $this->createStub(ContentParser::class);
        $parser->method('parse')->willReturn([
            new ParsedEntry('Article 1', 'https://example.com/1', 'Body 1', new \DateTimeImmutable),
            new ParsedEntry('Article 3', 'https://example.com/3', 'Body 3', new \DateTimeImmutable),
        ]);

        $fetcher = $this->createStub(ContentFetcher::class);
        $fetcher->method('fetch')->willReturn('<rss>dummy</rss>');

        $dispatcher = $this->createStub(EventDispatcher::class);

        $useCase2 = new CollectSource(
            $this->sourceRepository,
            $this->articleRepository,
            $this->executionRepository,
            $fetcher,
            $parser,
            $dispatcher,
        );

        $execution = $useCase2->execute($source->id()->value());

        $this->assertSame(1, $execution->newArticleCount());
        $this->assertSame(1, $execution->skippedArticleCount());
    }

    #[Test]
    public function 存在しないソースからは収集できない(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('指定されたソースが見つかりません');

        $this->useCase->execute('non-existent-id');
    }

    #[Test]
    public function 一時停止中のソースからは収集できない(): void
    {
        $source = $this->createActiveSource();
        $source->pause();
        $this->sourceRepository->save($source);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('一時停止中のソースからは収集できません');

        $this->useCase->execute($source->id()->value());
    }

    #[Test]
    public function コンテンツ取得中に例外が発生した場合は実行が失敗として記録される(): void
    {
        $source = $this->createActiveSource();

        $fetcher = $this->createStub(ContentFetcher::class);
        $fetcher->method('fetch')->willThrowException(new \RuntimeException('接続タイムアウト'));

        $parser = $this->createStub(ContentParser::class);
        $dispatcher = $this->createStub(EventDispatcher::class);

        $useCase = new CollectSource(
            $this->sourceRepository,
            $this->articleRepository,
            $this->executionRepository,
            $fetcher,
            $parser,
            $dispatcher,
        );

        try {
            $useCase->execute($source->id()->value());
            $this->fail('例外が発生しませんでした');
        } catch (\RuntimeException $e) {
            $this->assertSame('接続タイムアウト', $e->getMessage());
        }

        $executions = $this->executionRepository->all();
        $this->assertCount(1, $executions);
        $this->assertSame(FetchExecutionStatus::Failed, $executions[0]->status());
        $this->assertSame('接続タイムアウト', $executions[0]->failureReason());
    }

    private function createActiveSource(): Source
    {
        $source = Source::add(
            new SourceName('Test'),
            new SourceUrl('https://example.com/rss'),
            SourceKind::Rss,
            new FetchInterval(60),
        );
        $this->sourceRepository->save($source);

        return $source;
    }
}
