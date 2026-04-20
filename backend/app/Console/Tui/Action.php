<?php

namespace App\Console\Tui;

/**
 * TUI のキー操作から導出されるアクション
 *
 * Bindings がキーイベントを Action に変換し、EventHandler が実行する
 */
enum Action
{
    /** TUI を終了する */
    case Exit;

    /** カーソルを上に移動する / プレビューを上にスクロールする */
    case Up;

    /** カーソルを下に移動する / プレビューを下にスクロールする */
    case Down;

    /** プレビューのフォーカスを解除する */
    case Left;

    /** プレビューにフォーカスする */
    case Right;

    /** 現在のモードから離脱する */
    case Escape;

    /** 記事検索モードに遷移する */
    case Search;

    /** ラベルフィルタモードに遷移する */
    case Filter;

    /** ソース一覧モードに切り替える */
    case Sources;

    /** ソース追加フローを開始する */
    case Add;

    /** 選択中の記事を既読にする */
    case MarkRead;

    /** 選択中の記事のブックマークを切り替える */
    case Bookmark;

    /** 全アクティブソースから記事を収集する */
    case Fetch;

    /** 選択中のソースを削除する */
    case Remove;

    /** 選択中のソースの追跡状態を切り替える */
    case TogglePause;

    /** ヘルプモーダルの表示を切り替える */
    case Help;

    /** プロンプト入力を確定する */
    case Submit;

    /** プロンプト入力をキャンセルする */
    case Cancel;

    /** プロンプトの末尾1文字を削除する */
    case Delete;
}
