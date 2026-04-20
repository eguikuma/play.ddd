<?php

namespace Tests\Unit\Domain\Curation\ValueObjects;

use App\Domain\Curation\ValueObjects\Label;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    #[Test]
    public function ラベルを作成できる(): void
    {
        $label = new Label('laravel');

        $this->assertSame('laravel', $label->value());
    }

    #[Test]
    public function ラベルは小文字に正規化される(): void
    {
        $label = new Label('Laravel');

        $this->assertSame('laravel', $label->value());
    }

    #[Test]
    public function 空のラベルは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Label('');
    }

    #[Test]
    public function 同じ値のラベルは等しい(): void
    {
        $label1 = new Label('php');
        $label2 = new Label('PHP');

        $this->assertTrue($label1->equals($label2));
    }

    #[Test]
    public function 異なる値のラベルは等しくない(): void
    {
        $label1 = new Label('php');
        $label2 = new Label('laravel');

        $this->assertFalse($label1->equals($label2));
    }

    #[Test]
    public function スペースを含むラベルを作成できる(): void
    {
        $label = new Label('good first issue');

        $this->assertSame('good first issue', $label->value());
    }

    #[Test]
    public function ラベルは50文字を超えると作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('50文字');

        new Label(str_repeat('a', 51));
    }
}
