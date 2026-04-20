<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Preview;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PreviewTest extends TestCase
{
    #[Test]
    public function スクロールで表示位置が進む(): void
    {
        $preview = new Preview;
        $preview->limit = 10;

        $preview->scroll(3);

        $this->assertSame(3, $preview->scroll);
    }

    #[Test]
    public function スクロールは先頭より前に戻らない(): void
    {
        $preview = new Preview;
        $preview->limit = 10;

        $preview->scroll(-5);

        $this->assertSame(0, $preview->scroll);
    }

    #[Test]
    public function スクロールは末尾より先に進まない(): void
    {
        $preview = new Preview;
        $preview->limit = 5;

        $preview->scroll(10);

        $this->assertSame(5, $preview->scroll);
    }

    #[Test]
    public function フォーカスするとスクロール位置がリセットされる(): void
    {
        $preview = new Preview;
        $preview->scroll = 5;

        $preview->focus();

        $this->assertTrue($preview->focused);
        $this->assertSame(0, $preview->scroll);
    }

    #[Test]
    public function フォーカスを解除できる(): void
    {
        $preview = new Preview;
        $preview->focused = true;

        $preview->unfocus();

        $this->assertFalse($preview->focused);
    }

    #[Test]
    public function リセットで全状態が初期値に戻る(): void
    {
        $preview = new Preview;
        $preview->scroll = 5;
        $preview->limit = 10;
        $preview->focused = true;

        $preview->reset();

        $this->assertSame(0, $preview->scroll);
        $this->assertSame(0, $preview->limit);
        $this->assertFalse($preview->focused);
    }
}
