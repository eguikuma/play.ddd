<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Sources;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\AddSource;
use App\UseCases\Tracking\ListSources;
use App\UseCases\Tracking\PauseSource;
use App\UseCases\Tracking\RemoveSource;
use App\UseCases\Tracking\ResumeSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SourcesTest extends TestCase
{
    private Sources $sources;

    protected function setUp(): void
    {
        $repository = new InMemorySourceRepository;
        $this->sources = new Sources(
            new AddSource($repository),
            new ListSources($repository),
            new RemoveSource($repository),
            new PauseSource($repository),
            new ResumeSource($repository),
        );
    }

    #[Test]
    public function ソースが0件のとき移動操作は無視される(): void
    {
        $this->sources->load();

        $this->sources->move(1);

        $this->assertSame(0, $this->sources->cursor);
    }

    #[Test]
    public function ソースを追加すると一覧に反映される(): void
    {
        $source = $this->sources->add('https://example.com/feed', 'Example');
        $this->sources->load();

        $this->assertSame('Example', $source->name()->value());
        $this->assertCount(1, $this->sources->items);
    }

    #[Test]
    public function カーソルは末尾より後に移動しない(): void
    {
        $this->sources->add('https://example.com/a', 'A');
        $this->sources->add('https://example.com/b', 'B');
        $this->sources->load();

        $this->sources->move(100);

        $this->assertSame(1, $this->sources->cursor);
    }

    #[Test]
    public function カーソル位置のソースが選択中になる(): void
    {
        $this->sources->add('https://example.com/a', 'A');
        $this->sources->add('https://example.com/b', 'B');
        $this->sources->load();
        $this->sources->move(1);

        $this->assertSame('B', $this->sources->selection()->name()->value());
    }

    #[Test]
    public function ソースが0件のとき選択中のソースはない(): void
    {
        $this->sources->load();

        $this->assertNull($this->sources->selection());
    }

    #[Test]
    public function ソースを削除すると対象ソースが返される(): void
    {
        $this->sources->add('https://example.com/feed', 'Example');
        $this->sources->load();

        $source = $this->sources->remove();

        $this->assertSame('Example', $source->name()->value());
    }

    #[Test]
    public function ソースを削除すると一覧から消える(): void
    {
        $this->sources->add('https://example.com/feed', 'Example');
        $this->sources->load();

        $this->sources->remove();
        $this->sources->load();

        $this->assertCount(0, $this->sources->items);
    }

    #[Test]
    public function アクティブなソースを一時停止すると対象ソースが返される(): void
    {
        $this->sources->add('https://example.com/feed', 'Example');
        $this->sources->load();

        $source = $this->sources->pause();

        $this->assertSame('Example', $source->name()->value());
    }

    #[Test]
    public function 一時停止したソースを再開できる(): void
    {
        $this->sources->add('https://example.com/feed', 'Example');
        $this->sources->load();

        $this->sources->pause();
        $this->sources->load();
        $this->sources->pause();
        $this->sources->load();

        $this->assertTrue($this->sources->selection()->isActive());
    }
}
