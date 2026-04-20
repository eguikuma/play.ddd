<?php

namespace App\Console\Tui;

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
        ListUnreadArticles $listUnreadArticles,
        MarkAsRead $markAsRead,
        BookmarkArticle $bookmarkArticle,
        UnbookmarkArticle $unbookmarkArticle,
        AddSource $addSource,
        ListSources $listSources,
        RemoveSource $removeSource,
        PauseSource $pauseSource,
        ResumeSource $resumeSource,
        private readonly CollectAll $collectAll,
    ) {
        $this->articles = new Articles($listUnreadArticles, $markAsRead, $bookmarkArticle, $unbookmarkArticle);
        $this->sources = new Sources($addSource, $listSources, $removeSource, $pauseSource, $resumeSource);
        $this->preview = new Preview;
        $this->prompt = new Prompt;
    }

    public function prompting(): bool
    {
        return in_array($this->mode, [
            Mode::ArticleSearch,
            Mode::ArticleLabelFilter,
            Mode::SourceAddUrl,
            Mode::SourceAddName,
        ], true);
    }

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

    public function confirm(): void
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

    public function search(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::ArticleSearch;
        $this->prompt->value = $this->query;
    }

    public function filter(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::ArticleLabelFilter;
        $this->prompt->value = $this->filtering ?? '';
    }

    public function add(): void
    {
        $this->mode = Mode::SourceAddUrl;
        $this->prompt->clear();
        $this->sources->pending = '';
    }

    public function browse(): void
    {
        $this->preview->unfocus();
        $this->mode = Mode::Sources;
        $this->notice = '';
        $this->sources->load();
    }

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

    public function help(): void
    {
        if ($this->mode === Mode::Help) {
            $this->mode = $this->origin;
        } else {
            $this->origin = $this->mode;
            $this->mode = Mode::Help;
        }
    }

    public function origin(): Mode
    {
        return $this->origin;
    }
}
