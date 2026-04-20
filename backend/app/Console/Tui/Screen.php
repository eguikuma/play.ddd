<?php

namespace App\Console\Tui;

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

    public function __construct(State $state)
    {
        $this->terminal = PhpTermTerminal::new();
        $size = $this->terminal->info(Size::class);

        $this->state = $state;
        $this->layout = new Layout($this->state, max(40, $size?->cols ?? 120), max(10, $size?->lines ?? 40));
        $this->handler = new EventHandler($this->state);
    }

    /**
     * メインイベントループを開始し、TUI を終了するまでブロックする
     *
     * 描画 → イベント取得 → ハンドラ委譲 のサイクルを繰り返す
     * EventHandler が false を返すとループを抜けて正常終了する
     */
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
