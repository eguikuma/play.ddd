<?php

namespace App\Console\Tui;

/**
 * 表示幅と全角文字を考慮した文字単位折り返し後の行数を計算する
 */
class LineCounter
{
    /**
     * 折り返し後の表示行数を計算する
     *
     * 文字の表示幅を積算し、行幅を超えた時点で次の行に送る
     */
    public static function count(string $text, int $width): int
    {
        if ($text === '') {
            return 0;
        }

        return (int) ceil(mb_strwidth($text) / $width);
    }
}
