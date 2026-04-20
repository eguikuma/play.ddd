<?php

namespace App\Domain\Curation\ValueObjects;

/**
 * 記事の閲覧状態
 */
enum ReadingStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
}
