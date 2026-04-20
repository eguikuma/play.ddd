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
        $sourceReference = new SourceReference('source-123');

        $this->assertSame('source-123', $sourceReference->value());
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
        $sourceReference = new SourceReference('source-123');
        $sameReference = new SourceReference('source-123');

        $this->assertTrue($sourceReference->equals($sameReference));
    }

    #[Test]
    public function 異なる値のソース参照は等しくない(): void
    {
        $sourceReference = new SourceReference('source-123');
        $otherReference = new SourceReference('source-456');

        $this->assertFalse($sourceReference->equals($otherReference));
    }
}
