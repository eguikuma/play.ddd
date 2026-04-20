<?php

namespace App\Domain\Tracking\ValueObjects;

/**
 * ソースの種別
 */
enum SourceKind: string
{
    case Rss = 'rss';
}
