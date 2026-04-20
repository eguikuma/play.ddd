<?php

namespace App\Domain\Shared\Events;

/**
 * ドメインイベントのマーカーインターフェース
 *
 * ドメイン内で発生した出来事を表すクラスに実装する
 * EventDispatcher はこのインターフェースを実装したオブジェクトのみを受け付ける
 */
interface DomainEvent {}
