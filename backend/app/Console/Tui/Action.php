<?php

namespace App\Console\Tui;

/**
 * TUI のキー操作から導出されるアクション
 *
 * Bindings がキーイベントを Action に変換し、EventHandler が実行する
 */
enum Action
{
    case Exit;

    case Up;

    case Down;

    case Left;

    case Right;

    case Escape;

    case Search;

    case Filter;

    case Sources;

    case Add;

    case MarkRead;

    case Bookmark;

    case Fetch;

    case Remove;

    case TogglePause;

    case Help;

    case Confirm;

    case Cancel;

    case Delete;
}
