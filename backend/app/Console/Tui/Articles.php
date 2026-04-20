<?php

namespace App\Console\Tui;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\UseCases\Curation\BookmarkArticle;
use App\UseCases\Curation\ListUnreadArticles;
use App\UseCases\Curation\MarkAsRead;
use App\UseCases\Curation\UnbookmarkArticle;
use PhpTui\Tui\Extension\Core\Widget\List\ListState;

/**
 * 記事一覧パネルのデータとカーソル状態を管理する
 */
class Articles
{
    /** @var ReadableArticle[] */
    public array $items = [];

    public int $cursor = 0;

    private ListState $widget;

    public function __construct(
        private readonly ListUnreadArticles $listUnread,
        private readonly MarkAsRead $markAsRead,
        private readonly BookmarkArticle $bookmarkArticle,
        private readonly UnbookmarkArticle $unbookmarkArticle,
    ) {
        $this->widget = new ListState(0, null);
    }

    /**
     * カーソル位置の記事を返す
     */
    public function selection(): ?ReadableArticle
    {
        return $this->items[$this->cursor] ?? null;
    }

    /**
     * カーソルを上下に移動する
     */
    public function move(int $delta): void
    {
        $count = count($this->items);

        if ($count === 0) {
            return;
        }

        $this->cursor = max(0, min($count - 1, $this->cursor + $delta));
        $this->widget->selected = $this->cursor;
    }

    /**
     * 選択中の記事を既読にする
     */
    public function mark(): ReadableArticle
    {
        $article = $this->selection();
        $this->markAsRead->execute($article->id()->value());

        return $article;
    }

    /**
     * 選択中の記事のブックマーク状態を切り替える
     */
    public function bookmark(): ReadableArticle
    {
        $article = $this->selection();

        if ($article->bookmarked()) {
            $this->unbookmarkArticle->execute($article->id()->value());
        } else {
            $this->bookmarkArticle->execute($article->id()->value());
        }

        return $article;
    }

    /**
     * 未読記事を再読み込みし、カーソル位置を補正する
     */
    public function load(?string $filtering): void
    {
        $this->items = $this->listUnread->execute($filtering);
        $count = count($this->items);
        $this->cursor = $count > 0 ? min($this->cursor, $count - 1) : 0;
        $this->widget->selected = $count > 0 ? $this->cursor : null;
    }

    /**
     * タイトルの部分一致で記事を絞り込む
     *
     * $query は小文字化済みの検索文字列を受け取る
     */
    public function search(string $query, ?string $filtering): void
    {
        if ($query === '') {
            $this->load($filtering);

            return;
        }

        $all = $this->listUnread->execute($filtering);
        $this->items = array_values(array_filter(
            $all,
            fn (ReadableArticle $article) => str_contains(mb_strtolower($article->title()), $query),
        ));

        $this->cursor = 0;
        $this->widget->selected = count($this->items) > 0 ? 0 : null;
    }

    /**
     * ListWidget に渡すための描画状態を返す
     */
    public function widget(): ListState
    {
        return $this->widget;
    }
}
