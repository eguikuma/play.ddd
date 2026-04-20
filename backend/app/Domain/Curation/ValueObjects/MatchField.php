<?php

namespace App\Domain\Curation\ValueObjects;

/**
 * 分類ルールの照合フィールド
 */
enum MatchField: string
{
    case Title = 'title';
    case Url = 'url';
    case Content = 'content';
}
