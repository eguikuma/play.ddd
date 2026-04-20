<?php

namespace App\Console\Tui;

use App\Domain\Curation\ValueObjects\Label;

/**
 * ラベルを行幅に収まる範囲で [label1] [label2] 形式の文字列にする
 *
 * 行幅を超えるラベルは省略し、末尾に「…」を付加する
 */
class LabelFormatter
{
    /**
     * @param  Label[]  $labels
     */
    public static function badges(array $labels, int $width): string
    {
        if ($labels === []) {
            return '';
        }

        $parts = [];
        $used = 0;

        foreach ($labels as $label) {
            $tag = "[{$label->value()}]";
            $span = mb_strwidth($tag);
            $gap = $parts !== [] ? 1 : 0;

            if ($used + $gap + $span > $width) {
                $parts[] = '…';

                break;
            }

            $used += $gap + $span;
            $parts[] = $tag;
        }

        return implode(' ', $parts);
    }
}
