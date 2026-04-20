<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Shared\Events\EventDispatcher;
use Illuminate\Contracts\Events\Dispatcher;

class LaravelEventDispatcher implements EventDispatcher
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
