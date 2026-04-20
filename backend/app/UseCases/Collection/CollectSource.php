<?php

namespace App\UseCases\Collection;

use App\Domain\Collection\Aggregates\Article;
use App\Domain\Collection\Aggregates\FetchExecution;
use App\Domain\Collection\Events\ArticleCollected;
use App\Domain\Collection\Repositories\ArticleRepository;
use App\Domain\Collection\Repositories\FetchExecutionRepository;
use App\Domain\Collection\Services\ContentFetcher;
use App\Domain\Collection\Services\ContentParser;
use App\Domain\Collection\ValueObjects\ArticleBody;
use App\Domain\Collection\ValueObjects\ArticleTitle;
use App\Domain\Collection\ValueObjects\ArticleUrl;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;
use App\Domain\Shared\Events\EventDispatcher;
use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\SourceId;

class CollectSource
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
        private readonly ArticleRepository $articleRepository,
        private readonly FetchExecutionRepository $fetchExecutionRepository,
        private readonly ContentFetcher $contentFetcher,
        private readonly ContentParser $contentParser,
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    /**
     * 指定ソースから記事を収集し、取得実行記録を返す
     *
     * フロー: ソース取得 → コンテンツ取得 → パース → フィンガープリントで重複排除 → 記事保存 → ArticleCollected イベント発行 → 実行成功記録
     * 途中で例外が発生した場合は実行を Failed として保存し、例外を再スローする
     */
    public function execute(string $sourceId): FetchExecution
    {
        $source = $this->sourceRepository->findById(new SourceId($sourceId));

        if ($source === null) {
            throw new \DomainException('指定されたソースが見つかりません');
        }

        if (! $source->isActive()) {
            throw new \DomainException('一時停止中のソースからは収集できません');
        }

        $sourceRef = new SourceReference($source->id()->value());
        $execution = FetchExecution::start($sourceRef);

        try {
            $rawContent = $this->contentFetcher->fetch($source->url()->value());
            $entries = $this->contentParser->parse($rawContent);

            $newCount = 0;
            $skippedCount = 0;

            foreach ($entries as $entry) {
                $fingerprint = ContentFingerprint::fromUrl($entry->url);

                if ($this->articleRepository->existsByFingerprint($fingerprint)) {
                    $skippedCount++;

                    continue;
                }

                $article = Article::collect(
                    $sourceRef,
                    $source->kind(),
                    new ArticleTitle($entry->title),
                    new ArticleUrl($entry->url),
                    new ArticleBody($entry->body),
                    $entry->publishedAt,
                );

                $this->articleRepository->save($article);
                $newCount++;

                $this->eventDispatcher->dispatch(new ArticleCollected(
                    articleId: $article->id()->value(),
                    sourceReference: $sourceRef->value(),
                    sourceKind: $source->kind(),
                    title: $entry->title,
                    url: $entry->url,
                    body: $article->body()->value(),
                    publishedAt: $entry->publishedAt,
                    collectedAt: $article->collectedAt(),
                ));
            }

            $execution->succeed($newCount, $skippedCount);
            $source->markFetched(new \DateTimeImmutable);
            $this->sourceRepository->save($source);
        } catch (\Throwable $e) {
            $execution->fail($e->getMessage());
            $this->fetchExecutionRepository->save($execution);

            throw $e;
        }

        $this->fetchExecutionRepository->save($execution);

        return $execution;
    }
}
