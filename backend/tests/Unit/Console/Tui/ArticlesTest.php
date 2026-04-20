<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Articles;
use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\BookmarkArticle;
use App\UseCases\Curation\ListUnreadArticles;
use App\UseCases\Curation\MarkAsRead;
use App\UseCases\Curation\UnbookmarkArticle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticlesTest extends TestCase
{
    private InMemoryReadableArticleRepository $repository;

    private Articles $articles;

    protected function setUp(): void
    {
        $this->repository = new InMemoryReadableArticleRepository;
        $this->articles = new Articles(
            new ListUnreadArticles($this->repository),
            new MarkAsRead($this->repository),
            new BookmarkArticle($this->repository),
            new UnbookmarkArticle($this->repository),
        );
    }

    #[Test]
    public function カーソルは先頭より前に移動しない(): void
    {
        $this->save($this->create('a'), $this->create('b'));
        $this->articles->load(null);

        $this->articles->move(-5);

        $this->assertSame(0, $this->articles->cursor);
    }

    #[Test]
    public function カーソルは末尾より後に移動しない(): void
    {
        $this->save($this->create('a'), $this->create('b'));
        $this->articles->load(null);

        $this->articles->move(100);

        $this->assertSame(1, $this->articles->cursor);
    }

    #[Test]
    public function 記事が0件のとき移動操作は無視される(): void
    {
        $this->articles->load(null);

        $this->articles->move(1);

        $this->assertSame(0, $this->articles->cursor);
    }

    #[Test]
    public function カーソル位置の記事が選択中になる(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->articles->load(null);
        $this->articles->move(1);

        $this->assertSame('PHP基礎', $this->articles->selection()->title());
    }

    #[Test]
    public function 記事が0件のとき選択中の記事はない(): void
    {
        $this->articles->load(null);

        $this->assertNull($this->articles->selection());
    }

    #[Test]
    public function 空の検索クエリでは全記事が残る(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->articles->load(null);

        $this->articles->search('', null);

        $this->assertCount(2, $this->articles->items);
    }

    #[Test]
    public function 検索クエリに一致するタイトルの記事のみ残る(): void
    {
        $this->save($this->create('Laravel入門'), $this->create('PHP基礎'));
        $this->articles->load(null);

        $this->articles->search('laravel', null);

        $this->assertCount(1, $this->articles->items);
        $this->assertSame('Laravel入門', $this->articles->items[0]->title());
    }

    #[Test]
    public function 既読にすると対象記事が返される(): void
    {
        $this->save($this->create('Laravel入門'));
        $this->articles->load(null);

        $article = $this->articles->mark();

        $this->assertSame('Laravel入門', $article->title());
    }

    #[Test]
    public function ブックマークすると対象記事が返される(): void
    {
        $this->save($this->create('Laravel入門'));
        $this->articles->load(null);

        $article = $this->articles->bookmark();

        $this->assertSame('Laravel入門', $article->title());
    }

    #[Test]
    public function ブックマーク済みの記事を再度切り替えるとブックマークが解除される(): void
    {
        $this->save($this->create('Laravel入門'));
        $this->articles->load(null);

        $this->articles->bookmark();
        $this->articles->load(null);
        $article = $this->articles->bookmark();

        $this->articles->load(null);
        $this->assertFalse($this->articles->selection()->bookmarked());
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
