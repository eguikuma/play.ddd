<?php

namespace Tests\Unit\Domain\Tracking\ValueObjects;

use App\Domain\Tracking\ValueObjects\SourceName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourceNameTest extends TestCase
{
    #[Test]
    public function ソース名を作成できる(): void
    {
        $name = new SourceName('Laravel News');

        $this->assertSame('Laravel News', $name->value());
    }

    #[Test]
    public function 空のソース名は作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceName('');
    }

    #[Test]
    public function 空白のみのソース名は作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceName('   ');
    }

    #[Test]
    public function ソース名は255文字を超えると作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceName(str_repeat('あ', 256));
    }

    #[Test]
    public function 前後の空白は除去される(): void
    {
        $name = new SourceName('  Laravel News  ');

        $this->assertSame('Laravel News', $name->value());
    }
}
