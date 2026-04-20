<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\LabelFormatter;
use App\Domain\Curation\ValueObjects\Label;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LabelFormatterTest extends TestCase
{
    #[Test]
    public function ラベルなしなら空文字を返す(): void
    {
        $this->assertSame('', LabelFormatter::badges([], 40));
    }

    #[Test]
    public function ラベルが行幅に収まる(): void
    {
        $labels = [new Label('laravel'), new Label('php')];

        $result = LabelFormatter::badges($labels, 40);

        $this->assertSame('[laravel] [php]', $result);
    }

    #[Test]
    public function 行幅を超えるラベルは省略される(): void
    {
        $labels = [new Label('laravel'), new Label('php'), new Label('docker')];

        $result = LabelFormatter::badges($labels, 20);

        $this->assertSame('[laravel] [php] …', $result);
    }

    #[Test]
    public function 最初のラベルすら収まらない場合は省略記号のみ(): void
    {
        $labels = [new Label('very-long-label')];

        $result = LabelFormatter::badges($labels, 5);

        $this->assertSame('…', $result);
    }
}
