<?php

namespace Tests\Unit\Domain\Tracking\ValueObjects;

use App\Domain\Tracking\ValueObjects\SourceId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourceIdTest extends TestCase
{
    #[Test]
    public function ソースIDを生成できる(): void
    {
        $id = SourceId::generate();

        $this->assertNotEmpty($id->value());
    }

    #[Test]
    public function 同じ値のソースIDは等しい(): void
    {
        $sourceId = new SourceId('abc-123');
        $sameSourceId = new SourceId('abc-123');

        $this->assertTrue($sourceId->equals($sameSourceId));
    }

    #[Test]
    public function 異なる値のソースIDは等しくない(): void
    {
        $sourceId = new SourceId('abc-123');
        $otherSourceId = new SourceId('def-456');

        $this->assertFalse($sourceId->equals($otherSourceId));
    }

    #[Test]
    public function 空のソースIDは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceId('');
    }
}
