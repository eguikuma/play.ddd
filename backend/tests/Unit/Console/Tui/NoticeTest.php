<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Notice;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NoticeTest extends TestCase
{
    #[Test]
    public function 成功通知が生成される(): void
    {
        $notice = Notice::success('完了しました');

        $this->assertSame('完了しました', $notice->message);
        $this->assertTrue($notice->success);
    }

    #[Test]
    public function 失敗通知が生成される(): void
    {
        $notice = Notice::failure('失敗しました');

        $this->assertSame('失敗しました', $notice->message);
        $this->assertFalse($notice->success);
    }

    #[Test]
    public function 生成直後は期限切れではない(): void
    {
        $notice = Notice::success('テスト');

        $this->assertFalse($notice->expired());
    }

    #[Test]
    public function 最低表示時間を経過すると期限切れになる(): void
    {
        $notice = new \ReflectionClass(Notice::class);
        $instance = $notice->newInstanceWithoutConstructor();

        $message = $notice->getProperty('message');
        $message->setValue($instance, 'テスト');

        $success = $notice->getProperty('success');
        $success->setValue($instance, true);

        $createdAt = $notice->getProperty('createdAt');
        $createdAt->setValue($instance, microtime(true) - 3.0);

        $this->assertTrue($instance->expired());
    }
}
