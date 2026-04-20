<?php

namespace App\Console\Tui;

/**
 * テキスト入力プロンプトのバッファ状態を管理する
 */
class Prompt
{
    public string $value = '';

    public function type(string $char): void
    {
        $this->value .= $char;
    }

    public function delete(): void
    {
        if ($this->value === '') {
            return;
        }

        $this->value = mb_substr($this->value, 0, mb_strlen($this->value) - 1);
    }

    public function clear(): void
    {
        $this->value = '';
    }
}
