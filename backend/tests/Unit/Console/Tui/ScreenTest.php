<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Screen;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Screen はコンストラクタで PhpTermTerminal::new() を呼び出すため
 * ターミナル環境に依存し、CI など非ターミナル環境では実行できない
 *
 * イベントループ全体の動作検証は実際の端末で php artisan den を実行して確認する
 */
class ScreenTest extends TestCase
{
    #[Test]
    public function Screenクラスが存在する(): void
    {
        $this->assertTrue(class_exists(Screen::class));
    }
}
