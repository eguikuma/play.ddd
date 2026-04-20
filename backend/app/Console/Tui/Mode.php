<?php

namespace App\Console\Tui;

/**
 * TUI の操作モード
 *
 * キー操作のコンテキストと描画内容を決定する状態機械の状態を表す
 */
enum Mode
{
    /**
     * 記事一覧の通常ナビゲーション
     */
    case Articles;

    /**
     * 記事タイトルのインクリメンタル検索
     */
    case ArticleSearch;

    /**
     * ラベルフィルタのテキスト入力
     */
    case ArticleLabelFilter;

    /**
     * ソース一覧のナビゲーション
     */
    case Sources;

    /**
     * ソース追加（URL）
     */
    case SourceAddUrl;

    /**
     * ソース追加（名前）
     */
    case SourceAddName;

    /**
     * キーバインドのヘルプ表示
     */
    case Help;
}
