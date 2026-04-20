<?php

namespace Tests\Unit\Domain\Curation\ValueObjects;

use App\Domain\Curation\ValueObjects\RulePattern;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RulePatternTest extends TestCase
{
    #[Test]
    public function ルールパターンを作成できる(): void
    {
        $pattern = new RulePattern('Laravel');

        $this->assertSame('Laravel', $pattern->value());
    }

    #[Test]
    public function 空のルールパターンは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RulePattern('');
    }

    #[Test]
    public function 前後の空白は除去される(): void
    {
        $pattern = new RulePattern('  Laravel  ');

        $this->assertSame('Laravel', $pattern->value());
    }
}
