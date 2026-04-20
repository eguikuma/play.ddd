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
        $id1 = new SourceId('abc-123');
        $id2 = new SourceId('abc-123');

        $this->assertTrue($id1->equals($id2));
    }

    #[Test]
    public function 異なる値のソースIDは等しくない(): void
    {
        $id1 = new SourceId('abc-123');
        $id2 = new SourceId('def-456');

        $this->assertFalse($id1->equals($id2));
    }

    #[Test]
    public function 空のソースIDは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SourceId('');
    }
}
