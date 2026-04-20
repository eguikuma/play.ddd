<?php

namespace App\Console\Tui;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;

/**
 * キー入力をアクションに変換する一元管理クラス
 *
 * キー変更や新規操作の追加はこのクラスのみを修正する
 * モードやフォーカスの判定は EventHandler が担当する
 */
class Bindings
{
    /**
     * 通常モードのキーイベントをアクションに解決する
     */
    public static function resolve(object $event): ?Action
    {
        if ($event instanceof CharKeyEvent
            && $event->modifiers === KeyModifiers::CONTROL
            && $event->char === 'c') {
            return Action::Exit;
        }

        if ($event instanceof CodedKeyEvent) {
            return match ($event->code) {
                KeyCode::Up => Action::Up,
                KeyCode::Down => Action::Down,
                KeyCode::Left => Action::Left,
                KeyCode::Right, KeyCode::Enter => Action::Right,
                KeyCode::Esc => Action::Escape,
                default => null,
            };
        }

        if ($event instanceof CharKeyEvent) {
            return match ($event->char) {
                'q' => Action::Exit,
                'j' => Action::Down,
                'k' => Action::Up,
                '/' => Action::Search,
                'l' => Action::Filter,
                's' => Action::Sources,
                'a' => Action::Add,
                'm' => Action::MarkRead,
                'b' => Action::Bookmark,
                'r' => Action::Fetch,
                'd' => Action::Remove,
                't' => Action::TogglePause,
                '?' => Action::Help,
                default => null,
            };
        }

        return null;
    }

    /**
     * 入力モードのキーイベントをアクションに解決する
     *
     * 文字入力（バッファへの追記）は EventHandler が直接処理するため含めない
     */
    public static function prompt(object $event): ?Action
    {
        if ($event instanceof CharKeyEvent
            && $event->modifiers === KeyModifiers::CONTROL
            && $event->char === 'c') {
            return Action::Exit;
        }

        if ($event instanceof CodedKeyEvent) {
            return match ($event->code) {
                KeyCode::Enter => Action::Submit,
                KeyCode::Esc => Action::Cancel,
                KeyCode::Backspace => Action::Delete,
                default => null,
            };
        }

        return null;
    }
}
