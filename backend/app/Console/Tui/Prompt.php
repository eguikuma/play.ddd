<?php

namespace App\Console\Tui;

/**
 * テキスト入力プロンプトのバッファ状態を管理する
 */
class Prompt
{
    public string $value = '';

    /**
     * 1文字をバッファ末尾に追記する
     */
    public function type(string $char): void
    {
        $this->value .= $char;
    }

    /**
     * バッファ末尾の1文字を削除する
     */
    public function delete(): void
    {
        if ($this->value === '') {
            return;
        }

        $this->value = mb_substr($this->value, 0, mb_strlen($this->value) - 1);
    }

    /**
     * バッファを空にする
     */
    public function clear(): void
    {
        $this->value = '';
    }
}
