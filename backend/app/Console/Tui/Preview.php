<?php

namespace App\Console\Tui;

/**
 * 記事プレビューパネルの表示状態を管理する
 *
 * スクロール位置・フォーカス状態を保持し、State から分離する
 */
class Preview
{
    public int $scroll = 0;

    public int $limit = 0;

    public bool $focused = false;

    public function scroll(int $delta): void
    {
        $this->scroll = max(0, min($this->limit, $this->scroll + $delta));
    }

    public function focus(): void
    {
        $this->focused = true;
        $this->scroll = 0;
    }

    public function unfocus(): void
    {
        $this->focused = false;
    }

    public function reset(): void
    {
        $this->scroll = 0;
        $this->limit = 0;
        $this->focused = false;
    }
}
