<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Articles;
use App\Console\Tui\EventHandler;
use App\Console\Tui\Mode;
use App\Console\Tui\Sources;
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
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase
{
    private InMemoryReadableArticleRepository $repository;

    private State $state;

    private EventHandler $handler;

    protected function setUp(): void
    {
        $this->repository = new InMemoryReadableArticleRepository;
        $sourceRepository = new InMemorySourceRepository;

        $collectAll = $this->createStub(CollectAll::class);
        $collectAll->method('execute')->willReturn([]);

        $articles = new Articles(
            new ListUnreadArticles($this->repository),
            new MarkAsRead($this->repository),
            new BookmarkArticle($this->repository),
            new UnbookmarkArticle($this->repository),
        );

        $sources = new Sources(
            new AddSource($sourceRepository),
            new ListSources($sourceRepository),
            new RemoveSource($sourceRepository),
            new PauseSource($sourceRepository),
            new ResumeSource($sourceRepository),
        );

        $this->state = new State($articles, $sources, $collectAll);

        $this->handler = new EventHandler($this->state);
    }

    #[Test]
    public function qキーで終了する(): void
    {
        $result = $this->handler->handle(CharKeyEvent::new('q'));

        $this->assertFalse($result);
    }

    #[Test]
    public function CtrlCで終了する(): void
    {
        $result = $this->handler->handle(CharKeyEvent::new('c', KeyModifiers::CONTROL));

        $this->assertFalse($result);
    }

    #[Test]
    public function jキーで記事カーソルが下に移動する(): void
    {
        $this->save($this->create('第1記事'), $this->create('第2記事'));
        $this->state->articles->load(null);

        $this->handler->handle(CharKeyEvent::new('j'));

        $this->assertSame(1, $this->state->articles->cursor);
    }

    #[Test]
    public function kキーで記事カーソルが上に移動する(): void
    {
        $this->save($this->create('第1記事'), $this->create('第2記事'));
        $this->state->articles->load(null);
        $this->state->articles->move(1);

        $this->handler->handle(CharKeyEvent::new('k'));

        $this->assertSame(0, $this->state->articles->cursor);
    }

    #[Test]
    public function プレビューフォーカス中に上矢印キーで上にスクロールする(): void
    {
        $this->save($this->create('記事'));
        $this->state->articles->load(null);
        $this->state->preview->focus();
        $this->state->preview->limit = 10;
        $this->state->preview->scroll = 5;

        $this->handler->handle(CodedKeyEvent::new(KeyCode::Up));

        $this->assertSame(4, $this->state->preview->scroll);
    }

    #[Test]
    public function スラッシュキーで検索モードに遷移する(): void
    {
        $this->handler->handle(CharKeyEvent::new('/'));

        $this->assertSame(Mode::ArticleSearch, $this->state->mode);
    }

    #[Test]
    public function sキーでソース一覧モードに遷移する(): void
    {
        $this->handler->handle(CharKeyEvent::new('s'));

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モード中にjキーでソースカーソルが下に移動する(): void
    {
        $this->state->sources->add('https://example.com/a', 'A');
        $this->state->sources->add('https://example.com/b', 'B');
        $this->state->mode = Mode::Sources;
        $this->state->sources->load();

        $this->handler->handle(CharKeyEvent::new('j'));

        $this->assertSame(1, $this->state->sources->cursor);
    }

    #[Test]
    public function プロンプトモード中にEnterキーで入力が確定される(): void
    {
        $this->state->mode = Mode::ArticleSearch;
        $this->state->prompt->value = 'test';

        $result = $this->handler->handle(CodedKeyEvent::new(KeyCode::Enter));

        $this->assertTrue($result);
        $this->assertSame(Mode::Articles, $this->state->mode);
        $this->assertSame('test', $this->state->query);
    }

    #[Test]
    public function プロンプトモード中にEscキーで入力がキャンセルされる(): void
    {
        $this->state->mode = Mode::ArticleSearch;
        $this->state->prompt->value = 'test';

        $result = $this->handler->handle(CodedKeyEvent::new(KeyCode::Esc));

        $this->assertTrue($result);
        $this->assertSame(Mode::Articles, $this->state->mode);
        $this->assertSame('', $this->state->prompt->value);
    }

    #[Test]
    public function 入力モード中に文字キーでバッファに追記される(): void
    {
        $this->state->mode = Mode::ArticleLabelFilter;

        $this->handler->handle(CharKeyEvent::new('p'));
        $this->handler->handle(CharKeyEvent::new('h'));
        $this->handler->handle(CharKeyEvent::new('p'));

        $this->assertSame('php', $this->state->prompt->value);
    }

    #[Test]
    public function mキーで選択中の記事が既読になる(): void
    {
        $this->save($this->create('記事'));
        $this->state->articles->load(null);

        $this->handler->handle(CharKeyEvent::new('m'));

        $this->assertCount(0, $this->state->articles->items);
    }

    #[Test]
    public function bキーで選択中の記事がブックマークされる(): void
    {
        $this->save($this->create('記事'));
        $this->state->articles->load(null);

        $this->handler->handle(CharKeyEvent::new('b'));

        $this->state->articles->load(null);
        $this->assertTrue($this->state->articles->selection()->bookmarked());
    }

    #[Test]
    public function 記事一覧モードでrキーを押すとフェッチが実行される(): void
    {
        $this->state->articles->load(null);

        $result = $this->handler->handle(CharKeyEvent::new('r'));

        $this->assertTrue($result);
    }

    #[Test]
    public function ソース一覧モードでdキーを押すと選択中のソースが削除される(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->sources->add('https://example.com/feed', 'Example');
        $this->state->sources->load();

        $this->handler->handle(CharKeyEvent::new('d'));

        $this->assertCount(0, $this->state->sources->items);
    }

    #[Test]
    public function ソース一覧モードでtキーを押すと追跡が切り替わる(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->sources->add('https://example.com/feed', 'Example');
        $this->state->sources->load();

        $this->handler->handle(CharKeyEvent::new('t'));

        $this->state->sources->load();
        $this->assertFalse($this->state->sources->selection()->isActive());
    }

    #[Test]
    public function 記事一覧モードでdキーは無視される(): void
    {
        $result = $this->handler->handle(CharKeyEvent::new('d'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでmキーは無視される(): void
    {
        $this->state->mode = Mode::Sources;

        $result = $this->handler->handle(CharKeyEvent::new('m'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function クエスチョンキーでヘルプモードに遷移する(): void
    {
        $this->handler->handle(CharKeyEvent::new('?'));

        $this->assertSame(Mode::Help, $this->state->mode);
    }

    #[Test]
    public function 記事一覧モードでtキーは無視される(): void
    {
        $result = $this->handler->handle(CharKeyEvent::new('t'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function 記事一覧モードでaキーは無視される(): void
    {
        $result = $this->handler->handle(CharKeyEvent::new('a'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでbキーは無視される(): void
    {
        $this->state->mode = Mode::Sources;

        $result = $this->handler->handle(CharKeyEvent::new('b'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでスラッシュキーは無視される(): void
    {
        $this->state->mode = Mode::Sources;

        $result = $this->handler->handle(CharKeyEvent::new('/'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでlキーは無視される(): void
    {
        $this->state->mode = Mode::Sources;

        $result = $this->handler->handle(CharKeyEvent::new('l'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでsキーは無視される(): void
    {
        $this->state->mode = Mode::Sources;

        $result = $this->handler->handle(CharKeyEvent::new('s'));

        $this->assertTrue($result);
        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ソース一覧モードでrキーを押すとフェッチが実行される(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->articles->load(null);

        $result = $this->handler->handle(CharKeyEvent::new('r'));

        $this->assertTrue($result);
    }

    #[Test]
    public function ヘルプモードでEscキーを押すと元のモードに戻る(): void
    {
        $this->state->help();

        $this->handler->handle(CodedKeyEvent::new(KeyCode::Esc));

        $this->assertSame(Mode::Articles, $this->state->mode);
    }

    #[Test]
    public function ソース一覧からヘルプを開いてEscで戻るとソース一覧になる(): void
    {
        $this->state->mode = Mode::Sources;
        $this->state->help();

        $this->handler->handle(CodedKeyEvent::new(KeyCode::Esc));

        $this->assertSame(Mode::Sources, $this->state->mode);
    }

    #[Test]
    public function ヘルプモードでjキーは無視される(): void
    {
        $this->save($this->create('記事'));
        $this->state->articles->load(null);
        $this->state->help();

        $this->handler->handle(CharKeyEvent::new('j'));

        $this->assertSame(Mode::Help, $this->state->mode);
        $this->assertSame(0, $this->state->articles->cursor);
    }

    #[Test]
    public function ヘルプモードでmキーは無視される(): void
    {
        $this->save($this->create('記事'));
        $this->state->articles->load(null);
        $this->state->help();

        $this->handler->handle(CharKeyEvent::new('m'));

        $this->assertSame(Mode::Help, $this->state->mode);
        $this->assertCount(1, $this->state->articles->items);
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
            $this->repository->save($article);
        }
    }
}
