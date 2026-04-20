<?php

namespace App\Domain\Shared\Events;

/**
 * ドメインイベントのディスパッチャー
 */
interface EventDispatcher
{
    public function dispatch(DomainEvent $event): void;
}
