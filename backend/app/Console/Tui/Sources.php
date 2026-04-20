<?php

namespace App\Console\Tui;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\ValueObjects\SourceKind;
use App\UseCases\Tracking\AddSource;
use App\UseCases\Tracking\ListSources;
use App\UseCases\Tracking\PauseSource;
use App\UseCases\Tracking\RemoveSource;
use App\UseCases\Tracking\ResumeSource;
use PhpTui\Tui\Extension\Core\Widget\List\ListState;

/**
 * ソース一覧パネルのデータとカーソル状態を管理する
 */
class Sources
{
    /** @var Source[] */
    public array $items = [];

    public int $cursor = 0;

    public string $pending = '';

    private ListState $widget;

    public function __construct(
        private readonly AddSource $addSource,
        private readonly ListSources $listSources,
        private readonly RemoveSource $removeSource,
        private readonly PauseSource $pauseSource,
        private readonly ResumeSource $resumeSource,
    ) {
        $this->widget = new ListState(0, null);
    }

    /**
     * カーソルを上下に移動する
     */
    public function move(int $delta): void
    {
        $count = count($this->items);

        if ($count === 0) {
            return;
        }

        $this->cursor = max(0, min($count - 1, $this->cursor + $delta));
        $this->widget->selected = $this->cursor;
    }

    /**
     * ソース一覧を再読み込みし、カーソル位置を補正する
     */
    public function load(): void
    {
        $this->items = $this->listSources->execute();
        $count = count($this->items);
        $this->cursor = $count > 0 ? min($this->cursor, $count - 1) : 0;
        $this->widget->selected = $count > 0 ? $this->cursor : null;
    }

    /**
     * カーソル位置のソースを返す
     */
    public function selection(): ?Source
    {
        return $this->items[$this->cursor] ?? null;
    }

    /**
     * 選択中のソースを削除する
     */
    public function remove(): Source
    {
        $source = $this->selection();
        $this->removeSource->execute($source->id()->value());

        return $source;
    }

    /**
     * 選択中のソースの追跡状態を切り替える
     */
    public function pause(): Source
    {
        $source = $this->selection();

        if ($source->isActive()) {
            $this->pauseSource->execute($source->id()->value());
        } else {
            $this->resumeSource->execute($source->id()->value());
        }

        return $source;
    }

    /**
     * 新しいソースを追加する
     */
    public function add(string $url, ?string $name): Source
    {
        return $this->addSource->execute(
            url: $url,
            name: $name,
            kind: SourceKind::Rss,
            fetchIntervalMinutes: 60,
        );
    }

    /**
     * ListWidget に渡すための描画状態を返す
     */
    public function widget(): ListState
    {
        return $this->widget;
    }
}
