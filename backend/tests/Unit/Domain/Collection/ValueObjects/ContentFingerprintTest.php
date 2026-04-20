<?php

namespace Tests\Unit\Domain\Collection\ValueObjects;

use App\Domain\Collection\ValueObjects\ContentFingerprint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContentFingerprintTest extends TestCase
{
    #[Test]
    public function URLからフィンガープリントを生成できる(): void
    {
        $fingerprint = ContentFingerprint::fromUrl('https://example.com/article/1');

        $this->assertNotEmpty($fingerprint->value());
    }

    #[Test]
    public function 同じURLからは同じフィンガープリントが生成される(): void
    {
        $fp1 = ContentFingerprint::fromUrl('https://example.com/article/1');
        $fp2 = ContentFingerprint::fromUrl('https://example.com/article/1');

        $this->assertTrue($fp1->equals($fp2));
    }

    #[Test]
    public function 異なるURLからは異なるフィンガープリントが生成される(): void
    {
        $fp1 = ContentFingerprint::fromUrl('https://example.com/article/1');
        $fp2 = ContentFingerprint::fromUrl('https://example.com/article/2');

        $this->assertFalse($fp1->equals($fp2));
    }

    #[Test]
    public function 空のフィンガープリントは作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ContentFingerprint('');
    }
}
