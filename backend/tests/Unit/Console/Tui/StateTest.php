<?php

namespace Tests\Unit\Console\Tui;

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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    private InMemoryReadableArticleRepository $articleRepository;

    private InMemorySourceRepository $sourceRepository;

    private State $state;

    protected function setUp(): void
    {
        $this->articleRepository = new InMemoryReadableArticleRepository;
        $this->sourceRepository = new InMemorySourceRepository;

        $collectAll = $this->createStub(CollectAll::class);
        $collectAll->method('execute')->willReturn([]);

        $this->state = new State(
            listUnreadArticles: new ListUnreadArticles($this->articleRepository),
            markAsRead: new MarkAsRead($this->articleRepository),
            bookmarkArticle: new BookmarkArticle($this->articleRepository),
            unbookmarkArticle: new UnbookmarkArticle($this->articleRepository),
            addSource: new AddSource($this->sourceRepository),
            listSources: new ListSources($this->sourceRepository),
            removeSource: new RemoveSource($this->sourceRepository),
            pauseSource: new PauseSource($this->sourceRepository),
            resumeSource: new ResumeSource($this->sourceRepository),
            collectAll: $collectAll,
        );
    }

    #[Test]
    public function 検索モード中は入力中と判定される(): void
    {
        $this->state->mode = Mode::ArticleSearch;

        $this->assertTrue($this->state->prompting());
    }

    #[Test]
    public function ラベルフィルタモード中は入力中と判定される(): void
    {
        $this->state->mode = Mode::ArticleLabelFilter;

        $this->assertTrue($this->state->prompting());
    }

    #[Test]
    public function ソースURL入力モード中は入力中と判定される(): void
    {
        $this->state->mode = Mode::SourceAddUrl;

        $this->assertTrue($this->state->prompting());
    }

    #[Test]
    public function ソース名入力モード中は入力中と判定される(): void
    {
        $this->state->mode = Mode::SourceAddName;

        $this->assertTrue($this->state->prompting());
    }

    #[Test]
    public function 記事一覧モードでは入力中ではない(): void
    {
        $this->state->mode = Mode::Articles;

        $this->assertFalse($this->state->prompting());
    }

    #[Test]
    public function ソース一覧モードでは入力中ではない(): void
    {
        $this->state->mode = Mode::Sources;

        $this->assertFalse($this->state->prompting());
    }

    #[Test]
    public function 記事検索のキャンセルで記事一覧に戻る(): void
    {
        $this->state->mode = Mode::ArticleSearch;

        $this->state->cancel();

        $this->assertSame(Mode::Articles, $this->state->mode);
        $this->assertSame('', $this->state->prompt->value);
    }

    #[Test]
    public function ソース名入力のキャンセルでソース一覧に戻る(): void
    {
        $this->state->mode = Mode::SourceAddName;
        $this->state->prompt->value = 'My Feed';

        $this->state->cancel();

        $this->assertSame(Mode::Sources, $this->state->mode);
        $this->assertSame('', $this->state->prompt->value);
    }

    #[Test]
    public function 検索確定でクエリが記事一覧に適用される(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->state->articles->load(null);
        $this->state->mode = Mode::ArticleSearch;
        $this->state->prompt->value = 'laravel';

        $this->state->confirm();

        $this->assertSame(Mode::Articles, $this->state->mode);
        $this->assertSame('laravel', $this->state->query);
        $this->assertCount(1, $this->state->articles->items);
    }

    #[Test]
    public function 検索開始で前回のクエリがプロンプトに復元される(): void
    {
        $this->state->query = 'laravel';

        $this->state->search();

        $this->assertSame(Mode::ArticleSearch, $this->state->mode);
        $this->assertSame('laravel', $this->state->prompt->value);
    }

    #[Test]
    public function フィルタ操作でラベル入力モードに切り替わる(): void
    {
        $this->state->filter();

        $this->assertSame(Mode::ArticleLabelFilter, $this->state->mode);
    }

    #[Test]
    public function ブラウズ操作でソース一覧に切り替わる(): void
    {
        $this->state->browse();

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function 空文字の検索確定で絞り込みが解除される(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->state->articles->load(null);

        $this->state->mode = Mode::ArticleSearch;
        $this->state->prompt->value = 'laravel';
        $this->state->confirm();
        $this->assertCount(1, $this->state->articles->items);

        $this->state->mode = Mode::ArticleSearch;
        $this->state->prompt->value = '';
        $this->state->confirm();

        $this->assertSame(Mode::Articles, $this->state->mode);
        $this->assertSame('', $this->state->query);
        $this->assertCount(2, $this->state->articles->items);
    }

    #[Test]
    public function 既読操作で記事が未読一覧から消える(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->state->articles->load(null);

        $this->state->mark();

        $this->assertCount(1, $this->state->articles->items);
    }

    #[Test]
    public function 記事が0件のとき既読操作は無視される(): void
    {
        $this->state->articles->load(null);

        $this->state->mark();

        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ブックマーク操作で記事のブックマーク状態が切り替わる(): void
    {
        $this->save($this->create('Laravel入門'));
        $this->state->articles->load(null);

        $this->state->bookmark();

        $this->state->articles->load(null);
        $this->assertTrue($this->state->articles->selection()->bookmarked());
    }

    #[Test]
    public function ブックマーク済みの記事を再度操作すると解除される(): void
    {
        $this->save($this->create('Laravel入門'));
        $this->state->articles->load(null);
        $this->state->bookmark();

        $this->state->articles->load(null);
        $this->state->bookmark();

        $this->state->articles->load(null);
        $this->assertFalse($this->state->articles->selection()->bookmarked());
    }

    #[Test]
    public function フェッチ操作で記事一覧が再読み込みされる(): void
    {
        $this->state->articles->load(null);

        $this->state->fetch();

        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース削除で一覧から消える(): void
    {
        $this->state->browse();
        $this->state->sources->add('https://example.com/feed', 'Example');
        $this->state->sources->load();

        $this->state->remove();

        $this->assertCount(0, $this->state->sources->items);
    }

    #[Test]
    public function ソースが0件のとき削除操作は無視される(): void
    {
        $this->state->browse();
        $this->state->sources->load();

        $this->state->remove();

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一時停止でアクティブ状態が切り替わる(): void
    {
        $this->state->browse();
        $this->state->sources->add('https://example.com/feed', 'Example');
        $this->state->sources->load();

        $this->state->pause();

        $this->state->sources->load();
        $this->assertFalse($this->state->sources->selection()->isActive());
    }

    #[Test]
    public function 一時停止中のソースを再開するとアクティブに戻る(): void
    {
        $this->state->browse();
        $this->state->sources->add('https://example.com/feed', 'Example');
        $this->state->sources->load();
        $this->state->pause();

        $this->state->sources->load();
        $this->state->pause();

        $this->state->sources->load();
        $this->assertTrue($this->state->sources->selection()->isActive());
    }

    #[Test]
    public function ヘルプ操作でヘルプモードに切り替わる(): void
    {
        $this->state->help();

        $this->assertSame(Mode::Help, $this->state->mode);
    }

    #[Test]
    public function ヘルプモードで再度操作すると記事一覧に戻る(): void
    {
        $this->state->help();
        $this->state->help();

        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース一覧からヘルプを開いて戻るとソース一覧に復帰する(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->help();
        $this->state->help();

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ヘルプモードでEscを押すと元のモードに戻る(): void
    {
        $this->state->help();
        $this->state->escape();

        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース一覧からヘルプを開いてEscで戻るとソース一覧に復帰する(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->help();
        $this->state->escape();

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    private function create(string $title): ReadableArticle
    {
        return ReadableArticle::fromCollected(
            articleId: uniqid(),
            sourceId: 'source-1',
            title: $title,
            url: 'https://example.com/'.urlencode($title),
            body: 'テスト本文',
            publishedAt: new \DateTimeImmutable,
        );
    }

    private function save(ReadableArticle ...$articles): void
    {
        foreach ($articles as $article) {
            $this->articleRepository->save($article);
        }
    }
}
