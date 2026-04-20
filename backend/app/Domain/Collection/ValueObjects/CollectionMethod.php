<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 記事の収集方法
 *
 * どのような手段でソースから記事を取得したかを表す
 */
enum CollectionMethod: string
{
    case Rss = 'rss';
}
