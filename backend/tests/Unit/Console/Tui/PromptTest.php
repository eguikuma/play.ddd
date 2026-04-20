<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Prompt;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PromptTest extends TestCase
{
    #[Test]
    public function 文字入力がバッファに追記される(): void
    {
        $prompt = new Prompt;

        $prompt->type('a');
        $prompt->type('b');

        $this->assertSame('ab', $prompt->value);
    }

    #[Test]
    public function 末尾の1文字が削除される(): void
    {
        $prompt = new Prompt;
        $prompt->value = 'abc';

        $prompt->delete();

        $this->assertSame('ab', $prompt->value);
    }

    #[Test]
    public function バッファが空のとき削除操作は無視される(): void
    {
        $prompt = new Prompt;

        $prompt->delete();

        $this->assertSame('', $prompt->value);
    }

    #[Test]
    public function クリアでバッファが空になる(): void
    {
        $prompt = new Prompt;
        $prompt->value = 'test';

        $prompt->clear();

        $this->assertSame('', $prompt->value);
    }
}
