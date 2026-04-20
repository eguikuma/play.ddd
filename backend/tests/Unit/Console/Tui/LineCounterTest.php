<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\LineCounter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LineCounterTest extends TestCase
{
    #[Test]
    public function 空文字列はゼロ行になる(): void
    {
        $this->assertSame(0, LineCounter::count('', 80));
    }

    #[Test]
    public function 幅以内の文字列は一行に収まる(): void
    {
        $this->assertSame(1, LineCounter::count('hello', 10));
    }

    #[Test]
    public function 幅ちょうどの文字列は一行に収まる(): void
    {
        $this->assertSame(1, LineCounter::count('hello', 5));
    }

    #[Test]
    public function 幅を超える文字列は複数行に折り返される(): void
    {
        /**
         * 10文字 / 幅3 = ceil(10/3) = 4行
         */
        $this->assertSame(4, LineCounter::count('1234567890', 3));
    }

    #[Test]
    public function スペースを含む文字列も文字単位で折り返される(): void
    {
        /**
         * 'hello world' = 表示幅11 / 幅5 = ceil(11/5) = 3行
         */
        $this->assertSame(3, LineCounter::count('hello world', 5));
    }

    #[Test]
    public function 全角文字は表示幅二として計算される(): void
    {
        /**
         * '日本語' = 表示幅6 / 幅4 = ceil(6/4) = 2行
         */
        $this->assertSame(2, LineCounter::count('日本語', 4));
    }

    #[Test]
    public function 日英混在の文字列を正しく計算できる(): void
    {
        /**
         * 'PHP入門' = 1+1+1+2+2 = 表示幅7 / 幅5 = ceil(7/5) = 2行
         */
        $this->assertSame(2, LineCounter::count('PHP入門', 5));
    }
}
