<?php

namespace App\Console\Tui;

use PhpTui\Term\Event\CharKeyEvent;

/**
 * キーイベントを受け取り、Bindings でアクションに変換して State に委譲する
 *
 * 「何のキーか」は Bindings が判断し、「何をするか」は State が実行する
 * モードごとのハンドラで受け付けるアクションを制限する
 */
class EventHandler
{
    public function __construct(private readonly State $state) {}

    /**
     * @return bool false で TUI を終了する
     */
    public function handle(object $event): bool
    {
        if ($this->state->prompting()) {
            return $this->prompt($event);
        }

        $action = Bindings::resolve($event);

        if ($action === Action::Exit) {
            return false;
        }

        return match ($this->state->mode) {
            Mode::Articles => $this->articles($action),
            Mode::Sources => $this->sources($action),
            Mode::Help => $this->help($action),
            default => true,
        };
    }

    private function articles(?Action $action): bool
    {
        return match ($action) {
            Action::Up, Action::Down => $this->dispatch(function () use ($action) {
                $delta = $action === Action::Down ? 1 : -1;

                if ($this->state->preview->focused) {
                    $this->state->preview->scroll($delta);

                    return;
                }

                $this->state->articles->move($delta);
                $this->state->notice = '';
                $this->state->preview->reset();
            }),
            Action::Left => $this->dispatch(fn () => $this->state->preview->unfocus()),
            Action::Right => $this->dispatch(function () {
                if ($this->state->articles->selection() !== null) {
                    $this->state->preview->focus();
                }
            }),
            Action::Escape => $this->dispatch(fn () => $this->state->escape()),
            Action::Search => $this->dispatch(fn () => $this->state->search()),
            Action::Filter => $this->dispatch(fn () => $this->state->filter()),
            Action::Sources => $this->dispatch(fn () => $this->state->browse()),
            Action::MarkRead => $this->dispatch(fn () => $this->state->mark()),
            Action::Bookmark => $this->dispatch(fn () => $this->state->bookmark()),
            Action::Fetch => $this->dispatch(fn () => $this->state->fetch()),
            Action::Help => $this->dispatch(fn () => $this->state->help()),
            default => true,
        };
    }

    private function sources(?Action $action): bool
    {
        return match ($action) {
            Action::Up, Action::Down => $this->dispatch(
                fn () => $this->state->sources->move($action === Action::Down ? 1 : -1),
            ),
            Action::Escape => $this->dispatch(fn () => $this->state->escape()),
            Action::Add => $this->dispatch(fn () => $this->state->add()),
            Action::Remove => $this->dispatch(fn () => $this->state->remove()),
            Action::TogglePause => $this->dispatch(fn () => $this->state->pause()),
            Action::Fetch => $this->dispatch(fn () => $this->state->fetch()),
            Action::Help => $this->dispatch(fn () => $this->state->help()),
            default => true,
        };
    }

    private function help(?Action $action): bool
    {
        return match ($action) {
            Action::Help => $this->dispatch(fn () => $this->state->help()),
            Action::Escape => $this->dispatch(fn () => $this->state->escape()),
            default => true,
        };
    }

    /**
     * @return bool false で TUI を終了する
     */
    private function prompt(object $event): bool
    {
        $action = Bindings::prompt($event);

        if ($action === Action::Exit) {
            return false;
        }

        if ($action !== null) {
            match ($action) {
                Action::Submit => $this->state->submit(),
                Action::Cancel => $this->state->cancel(),
                Action::Delete => $this->type(fn () => $this->state->prompt->delete()),
                default => null,
            };

            return true;
        }

        if ($event instanceof CharKeyEvent) {
            $this->type(fn () => $this->state->prompt->type($event->char));
        }

        return true;
    }

    /**
     * プロンプト入力操作の実行後にインクリメンタル検索を更新する
     */
    private function type(\Closure $action): void
    {
        $action();

        if ($this->state->mode === Mode::ArticleSearch) {
            $query = mb_strtolower(
                $this->state->prompt->value !== '' ? $this->state->prompt->value : $this->state->query,
            );
            $this->state->articles->search($query, $this->state->filtering);
        }
    }

    private function dispatch(\Closure $action): bool
    {
        $action();

        return true;
    }
}
