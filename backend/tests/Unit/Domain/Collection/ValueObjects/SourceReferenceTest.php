<?php

namespace Tests\Unit\Domain\Collection\ValueObjects;

use App\Domain\Collection\ValueObjects\SourceReference;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourceReferenceTest extends TestCase
{
    #[Test]
    public function ソース参照を作成できる(): void
    {
        $ref = new SourceReference('source-123');

        $this->assertSame('source-123', $ref->value());
    }

    #[Test]
    public function 空のソース参照は作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceReference('');
    }

    #[Test]
    public function 同じ値のソース参照は等しい(): void
    {
        $ref1 = new SourceReference('source-123');
        $ref2 = new SourceReference('source-123');

        $this->assertTrue($ref1->equals($ref2));
    }

    #[Test]
    public function 異なる値のソース参照は等しくない(): void
    {
        $ref1 = new SourceReference('source-123');
        $ref2 = new SourceReference('source-456');

        $this->assertFalse($ref1->equals($ref2));
    }
}
