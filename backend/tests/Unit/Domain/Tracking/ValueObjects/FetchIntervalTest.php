<?php

namespace Tests\Unit\Domain\Tracking\ValueObjects;

use App\Domain\Tracking\ValueObjects\FetchInterval;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FetchIntervalTest extends TestCase
{
    #[Test]
    public function 有効な取得間隔を作成できる(): void
    {
        $interval = new FetchInterval(60);

        $this->assertSame(60, $interval->minutes());
    }

    #[Test]
    public function 最小値の1分で取得間隔を作成できる(): void
    {
        $interval = new FetchInterval(1);

        $this->assertSame(1, $interval->minutes());
    }

    #[Test]
    public function 最大値の10080分で取得間隔を作成できる(): void
    {
        $interval = new FetchInterval(10080);

        $this->assertSame(10080, $interval->minutes());
    }

    #[Test]
    public function 取得間隔は0分以下だと作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FetchInterval(0);
    }

    #[Test]
    public function 取得間隔は10080分を超えると作成できない(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FetchInterval(10081);
    }
}
