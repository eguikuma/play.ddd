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
use PhpTui\Term\Actions;
use PhpTui\Term\Terminal as PhpTermTerminal;
use PhpTui\Term\TerminalInformation\Size;
use PhpTui\Tui\Bridge\PhpTerm\PhpTermBackend;
use PhpTui\Tui\DisplayBuilder;

/**
 * TUI のメインイベントループとターミナルライフサイクルを管理する
 *
 * rawMode/alternateScreen の有効化・無効化は finally で保証される
 */
class Screen
{
    private readonly PhpTermTerminal $terminal;

    private readonly State $state;

    private readonly Layout $layout;

    private readonly EventHandler $handler;

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
        CollectAll $collectAll,
    ) {
        $this->terminal = PhpTermTerminal::new();
        $size = $this->terminal->info(Size::class);
        $terminalWidth = max(40, $size?->cols ?? 120);
        $terminalHeight = max(10, $size?->lines ?? 40);

        $this->state = new State(
            $listUnreadArticles,
            $markAsRead,
            $bookmarkArticle,
            $unbookmarkArticle,
            $addSource,
            $listSources,
            $removeSource,
            $pauseSource,
            $resumeSource,
            $collectAll,
        );
        $this->layout = new Layout($this->state, $terminalWidth, $terminalHeight);
        $this->handler = new EventHandler($this->state);
    }

    public function run(): int
    {
        $backend = PhpTermBackend::new($this->terminal);
        $display = DisplayBuilder::default($backend)->build();

        $this->terminal->enableRawMode();
        $this->terminal->queue(Actions::alternateScreenEnable());
        $this->terminal->queue(Actions::cursorHide());
        $this->terminal->flush();

        $this->state->articles->load($this->state->filtering);

        try {
            while (true) {
                $display->draw($this->layout->build());

                $event = $this->terminal->events()->next();

                if ($event === null) {
                    continue;
                }

                if (! $this->handler->handle($event)) {
                    break;
                }
            }
        } finally {
            $this->terminal->disableRawMode();
            $this->terminal->queue(Actions::alternateScreenDisable());
            $this->terminal->queue(Actions::cursorShow());
            $this->terminal->flush();
        }

        return 0;
    }
}
