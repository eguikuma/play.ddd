<?php

namespace App\Domain\Tracking\ValueObjects;

/**
 * ソースの追跡状態
 */
enum SourceStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
}
