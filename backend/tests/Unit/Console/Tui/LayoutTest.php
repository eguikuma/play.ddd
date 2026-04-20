<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Layout;
use App\Console\Tui\Mode;
use App\Console\Tui\State;
use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Collection\CollectAll;
use App\UseCases\Curation\BookmarkArticle;
use App\UseCases\Curation\ListUnreadArticles;
use App\UseCases\Curation\MarkAsRead;
use App\UseCases\Curation\UnbookmarkArticle;
use App\UseCases\Tracking\AddSource;
use App\UseCases\Tracking\ListSources;
use App\UseCases\Tracking\PauseSource;
use App\UseCases\Tracking\RemoveSource;
use App\UseCases\Tracking\ResumeSource;
use PhpTui\Tui\Widget\Widget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PhpTui の Widget オブジェクトへの依存があるため描画結果の検証は行わない
 * State に応じた Widget が返ること（型・非 null）のみ確認する
 */
class LayoutTest extends TestCase
{
    private State $state;

    private Layout $layout;

    protected function setUp(): void
    {
        $repository = new InMemoryReadableArticleRepository;
        $sourceRepository = new InMemorySourceRepository;

        $article = ReadableArticle::fromCollected(
            articleId: 'a-1',
            sourceId: 's-1',
            title: 'Test Article',
            url: 'https://example.com/article',
            body: 'Test content',
            publishedAt: new \DateTimeImmutable,
        );
        $repository->save($article);

        $collectAll = $this->createStub(CollectAll::class);
        $collectAll->method('execute')->willReturn([]);

        $this->state = new State(
            listUnreadArticles: new ListUnreadArticles($repository),
            markAsRead: new MarkAsRead($repository),
            bookmarkArticle: new BookmarkArticle($repository),
            unbookmarkArticle: new UnbookmarkArticle($repository),
            addSource: new AddSource($sourceRepository),
            listSources: new ListSources($sourceRepository),
            removeSource: new RemoveSource($sourceRepository),
            pauseSource: new PauseSource($sourceRepository),
            resumeSource: new ResumeSource($sourceRepository),
            collectAll: $collectAll,
        );
        $this->state->articles->load(null);

        $this->layout = new Layout($this->state, 120, 40);
    }

    #[Test]
    public function 記事一覧モードで描画できる(): void
    {
        $this->state->mode = Mode::Articles;

        $widget = $this->layout->build();

        $this->assertInstanceOf(Widget::class, $widget);
    }

    #[Test]
    public function ソース一覧モードで描画できる(): void
    {
        $this->state->mode = Mode::Sources;

        $widget = $this->layout->build();

        $this->assertInstanceOf(Widget::class, $widget);
    }

    #[Test]
    public function 検索入力モードで描画できる(): void
    {
        $this->state->mode = Mode::ArticleSearch;

        $widget = $this->layout->build();

        $this->assertInstanceOf(Widget::class, $widget);
    }

    #[Test]
    public function プレビューフォーカス中に描画できる(): void
    {
        $this->state->preview->focus();

        $widget = $this->layout->build();

        $this->assertInstanceOf(Widget::class, $widget);
    }

    #[Test]
    public function ヘルプモードで描画できる(): void
    {
        $this->state->mode = Mode::Help;

        $widget = $this->layout->build();

        $this->assertInstanceOf(Widget::class, $widget);
    }
}
