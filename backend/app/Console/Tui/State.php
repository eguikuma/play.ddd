<?php

namespace App\Console\Tui;

use App\UseCases\Collection\CollectAll;

/**
 * TUI 画面の全ミュータブル状態と状態遷移メソッドを管理する
 */
class State
{
    public Mode $mode = Mode::Articles;

    public string $query = '';

    public ?string $filtering = null;

    public string $notice = '';

    public Articles $articles;

    public Sources $sources;

    public Preview $preview;

    public Prompt $prompt;

    private Mode $origin = Mode::Articles;

    public function __construct(
        Articles $articles,
        Sources $sources,
        private readonly CollectAll $collectAll,
    ) {
        $this->articles = $articles;
        $this->sources = $sources;
        $this->preview = new Preview;
        $this->prompt = new Prompt;
    }

    /**
     * テキスト入力を受け付けるモードかどうかを判定する
     */
    public function prompting(): bool
    {
        return in_array($this->mode, [
            Mode::ArticleSearch,
            Mode::ArticleLabelFilter,
            Mode::SourceAddUrl,
            Mode::SourceAddName,
        ], true);
    }

    /**
     * Esc キーに応じた状態遷移を行う
     *
     * ヘルプ → 元のモードへ復帰
     * プレビューフォーカス中 → フォーカス解除
     * ソース一覧 → 記事一覧へ復帰（検索クエリを再適用）
     */
    public function escape(): void
    {
        if ($this->mode === Mode::Help) {
            $this->mode = $this->origin;

            return;
        }

        if ($this->preview->focused) {
            $this->preview->unfocus();

            return;
        }

        if ($this->mode === Mode::Sources) {
            $this->mode = Mode::Articles;
            $this->notice = '';
            $this->articles->load($this->filtering);

            if ($this->query !== '') {
                $this->articles->search(mb_strtolower($this->query), $this->filtering);
            }
        }
    }

    /**
     * プロンプト入力を確定し、モードに応じた処理を実行する
     *
     * ArticleSearch → クエリを保存して記事を絞り込む
     * ArticleLabelFilter → ラベルフィルタを適用して記事を再読み込みする
     * SourceAddUrl → 入力URLを保持して名前入力へ進む
     * SourceAddName → ソースを追加してソース一覧を再読み込みする
     */
    public function submit(): void
    {
        if ($this->mode === Mode::ArticleSearch) {
            $this->query = $this->prompt->value;
            $this->prompt->clear();
            $this->mode = Mode::Articles;
            $this->articles->search(mb_strtolower($this->query), $this->filtering);

            return;
        }

        if ($this->mode === Mode::ArticleLabelFilter) {
            $this->filtering = $this->prompt->value !== '' ? $this->prompt->value : null;
            $this->prompt->clear();
            $this->query = '';
            $this->mode = Mode::Articles;
            $this->articles->load($this->filtering);

            return;
        }

        if ($this->mode === Mode::SourceAddUrl) {
            $this->sources->pending = $this->prompt->value;
            $this->prompt->clear();
            $this->mode = Mode::SourceAddName;

            return;
        }

        if ($this->mode === Mode::SourceAddName) {
            $name = $this->prompt->value !== '' ? $this->prompt->value : null;

            try {
                $this->sources->add($this->sources->pending, $name);
                $this->notice = '';
            } catch (\DomainException|\InvalidArgumentException $e) {
                $this->notice = $e->getMessage();
            }

            $this->prompt->clear();
            $this->sources->pending = '';
            $this->mode = Mode::Sources;
            $this->sources->load();
        }
    }

    /**
     * プロンプト入力をキャンセルし、直前のモードに戻る
     */
    public function cancel(): void
    {
        $this->prompt->clear();
        $this->sources->pending = '';

        $this->mode = match ($this->mode) {
            Mode::ArticleSearch, Mode::ArticleLabelFilter => Mode::Articles,
            Mode::SourceAddUrl, Mode::SourceAddName => Mode::Sources,
            default => $this->mode,
        };
    }

    /**
     * 記事検索モードに遷移し、前回のクエリをプロンプトに復元する
     */
    public function search(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::ArticleSearch;
        $this->prompt->value = $this->query;
    }

    /**
     * ラベルフィルタ入力モードに遷移し、現在のフィルタ値をプロンプトに復元する
     */
    public function filter(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::ArticleLabelFilter;
        $this->prompt->value = $this->filtering ?? '';
    }

    /**
     * ソース追加フローを開始し、URL入力モードに遷移する
     */
    public function add(): void
    {
        $this->mode = Mode::SourceAddUrl;
        $this->prompt->clear();
        $this->sources->pending = '';
    }

    /**
     * ソース一覧モードに切り替え、ソースを再読み込みする
     */
    public function browse(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::Sources;
        $this->notice = '';
        $this->sources->load();
    }

    /**
     * 選択中の記事を既読にし、記事一覧を再読み込みする
     */
    public function mark(): void
    {
        if ($this->articles->selection() === null) {
            return;
        }

        try {
            $this->articles->mark();
            $this->notice = '';
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->notice = $e->getMessage();

            return;
        }

        $this->articles->load($this->filtering);

        if ($this->query !== '') {
            $this->articles->search(mb_strtolower($this->query), $this->filtering);
        }

        $this->preview->reset();
    }

    /**
     * 選択中の記事のブックマーク状態を切り替え、記事一覧を再読み込みする
     */
    public function bookmark(): void
    {
        if ($this->articles->selection() === null) {
            return;
        }

        try {
            $this->articles->bookmark();
            $this->notice = '';
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->notice = $e->getMessage();

            return;
        }

        $this->articles->load($this->filtering);

        if ($this->query !== '') {
            $this->articles->search(mb_strtolower($this->query), $this->filtering);
        }
    }

    /**
     * 全アクティブソースから記事を収集し、記事一覧を再読み込みする
     */
    public function fetch(): void
    {
        $this->notice = '';
        $this->collectAll->execute();
        $this->articles->load($this->filtering);

        if ($this->query !== '') {
            $this->articles->search(mb_strtolower($this->query), $this->filtering);
        }

        $this->preview->reset();
    }

    /**
     * 選択中のソースを削除し、ソース一覧を再読み込みする
     */
    public function remove(): void
    {
        if ($this->sources->selection() === null) {
            return;
        }

        try {
            $this->sources->remove();
            $this->notice = '';
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->notice = $e->getMessage();

            return;
        }

        $this->sources->load();
    }

    /**
     * 選択中のソースの追跡状態を切り替え、ソース一覧を再読み込みする
     */
    public function pause(): void
    {
        if ($this->sources->selection() === null) {
            return;
        }

        try {
            $this->sources->pause();
            $this->notice = '';
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->notice = $e->getMessage();

            return;
        }

        $this->sources->load();
    }

    /**
     * ヘルプモードの表示を切り替える
     */
    public function help(): void
    {
        if ($this->mode === Mode::Help) {
            $this->mode = $this->origin;
        } else {
            $this->origin = $this->mode;
            $this->mode = Mode::Help;
        }
    }

    /**
     * ヘルプモードに入る前のモードを返す
     */
    public function origin(): Mode
    {
        return $this->origin;
    }
}
