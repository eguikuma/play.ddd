<?php

namespace App\Console\Tui;

use PhpTui\Tui\Color\AnsiColor;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\Buffer\BufferContext;
use PhpTui\Tui\Extension\Core\Widget\BufferWidget;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\List\ListItem;
use PhpTui\Tui\Extension\Core\Widget\ListWidget;
use PhpTui\Tui\Extension\Core\Widget\Paragraph\Wrap;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Constraint\LengthConstraint;
use PhpTui\Tui\Style\Modifier;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Text\Text;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\HorizontalAlignment;
use PhpTui\Tui\Widget\Widget;

/**
 * TUI 画面全体のレイアウトを構築するクラス
 *
 * State を読み取り専用で受け取り、描画に必要な Widget を組み立てる
 * Preview の limit はスクロール量の計算結果を書き戻す
 */
class Layout
{
    /**
     * プレビューパネルのボーダー上下
     */
    private const BORDER = 2;

    /**
     * ステータスバー（内容1行 + ボーダー上下）
     */
    private const STATUS = 3;

    public function __construct(
        private readonly State $state,
        private readonly int $width,
        private readonly int $height,
    ) {}

    /**
     * 現在の State に基づいてフレーム全体の Widget ツリーを構築する
     */
    public function build(): Widget
    {
        $base = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::min(0), Constraint::length(3))
            ->widgets($this->panels(), $this->status());

        if ($this->state->loading) {
            return CompositeWidget::fromWidgets($base, $this->loading());
        }

        if ($this->state->mode === Mode::Help) {
            return CompositeWidget::fromWidgets($base, $this->help());
        }

        if ($this->state->notice !== null) {
            return CompositeWidget::fromWidgets($base, $this->toast());
        }

        return $base;
    }

    /**
     * リストパネルとプレビューパネルを横並びで配置する
     */
    private function panels(): Widget
    {
        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(Constraint::percentage(40), Constraint::min(0))
            ->widgets($this->list(), $this->preview());
    }

    /**
     * 現在のモードに応じて記事一覧またはソース一覧を返す
     */
    private function list(): Widget
    {
        if ($this->state->mode === Mode::Sources
            || $this->state->mode === Mode::SourceAddUrl
            || $this->state->mode === Mode::SourceAddName) {
            return $this->sources();
        }

        return $this->articles();
    }

    /**
     * 記事一覧パネルの Widget を構築する
     */
    private function articles(): Widget
    {
        $badge = $this->state->filtering !== null ? " [{$this->state->filtering}]" : '';
        $count = count($this->state->articles->items);
        $title = $count > 0 ? "未読記事 {$count} 件{$badge}" : "未読記事{$badge}";

        if ($this->state->query !== '') {
            $title .= "  /{$this->state->query}";
        }

        $items = [];

        foreach ($this->state->articles->items as $index => $article) {
            $mark = $article->bookmarked() ? '★ ' : '  ';
            $line = $mark.mb_strimwidth($article->title(), 0, 34, '…');

            $style = $index === $this->state->articles->cursor
                ? Style::default()->addModifier(Modifier::REVERSED)
                : Style::default();

            $items[] = ListItem::new(Text::fromString($line))->style($style);
        }

        $border = (! $this->state->preview->focused && ! $this->state->prompting())
            ? Style::default()->fg(AnsiColor::Cyan)
            : Style::default()->fg(AnsiColor::DarkGray);

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Plain)
            ->borderStyle($border)
            ->titles(Title::fromString(" {$title} "))
            ->widget(
                ListWidget::default()
                    ->items(...$items)
                    ->state($this->state->articles->widget())
                    ->highlightSymbol('> ')
                    ->highlightStyle(Style::default()->addModifier(Modifier::REVERSED)),
            );
    }

    /**
     * ソース一覧パネルの Widget を構築する
     */
    private function sources(): Widget
    {
        $count = count($this->state->sources->items);
        $title = $count > 0 ? "ソース {$count} 件" : 'ソース';

        $items = [];

        foreach ($this->state->sources->items as $index => $source) {
            $active = $source->isActive() ? '' : ' [停止]';
            $name = mb_strimwidth($source->name()->value(), 0, 30, '…');
            $line = "{$name}{$active}";

            $style = $index === $this->state->sources->cursor
                ? Style::default()->addModifier(Modifier::REVERSED)
                : Style::default();

            $items[] = ListItem::new(Text::fromString($line))->style($style);
        }

        $border = $this->state->prompting()
            ? Style::default()->fg(AnsiColor::DarkGray)
            : Style::default()->fg(AnsiColor::Cyan);

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Plain)
            ->borderStyle($border)
            ->titles(Title::fromString(" {$title} "))
            ->widget(
                ListWidget::default()
                    ->items(...$items)
                    ->state($this->state->sources->widget())
                    ->highlightSymbol('> ')
                    ->highlightStyle(Style::default()->addModifier(Modifier::REVERSED)),
            );
    }

    /**
     * 記事プレビューパネルの Widget を構築する
     */
    private function preview(): Widget
    {
        $browsing = in_array($this->state->mode, [
            Mode::Sources,
            Mode::SourceAddUrl,
            Mode::SourceAddName,
        ], true);

        $widget = (! $browsing && $this->state->articles->selection() !== null)
            ? $this->content()
            : ParagraphWidget::fromString('');

        $border = ($this->state->preview->focused && ! $this->state->prompting())
            ? Style::default()->fg(AnsiColor::Cyan)
            : Style::default()->fg(AnsiColor::DarkGray);

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Plain)
            ->borderStyle($border)
            ->widget($widget);
    }

    /**
     * 記事プレビューをセクションに分割して描画する
     *
     * タイトルは全角文字を考慮した表示幅でセクション高さを動的に計算し折り返す
     * 本文セクションのみスクロール対象とし、タイトル・日付・ラベル・URLは常に表示される
     */
    private function content(): Widget
    {
        $article = $this->state->articles->selection();

        $date = $article->publishedAt()?->format('Y年m月d日') ?? '--';

        $sidebar = (int) floor($this->width * 40 / 100);
        $inner = max(1, $this->width - $sidebar - 2);

        $heading = [];
        $remaining = $article->title();
        while ($remaining !== '') {
            $line = mb_strimwidth($remaining, 0, $inner);
            if ($line === '') {
                $line = mb_substr($remaining, 0, 1);
            }
            $heading[] = Line::fromSpans(
                Span::fromString($line)->style(Style::default()->addModifier(Modifier::BOLD)),
            );
            $remaining = mb_substr($remaining, mb_strlen($line));
        }
        $rows = max(1, count($heading));
        $header = ParagraphWidget::fromText(Text::fromLines(...$heading));

        $meta = ParagraphWidget::fromText(Text::fromLines(
            new Line(
                [Span::fromString($date)->style(Style::default()->addModifier(Modifier::DIM))],
                HorizontalAlignment::Right,
            ),
        ));

        $badges = LabelFormatter::badges($article->labels(), $inner);

        $paragraphs = $article->body() !== ''
            ? array_map(fn (string $line) => $line !== '' ? $line : ' ', explode("\n", $article->body()))
            : [];

        $wrapped = 0;
        foreach ($paragraphs as $paragraph) {
            $wrapped += LineCounter::count($paragraph, $inner);
        }

        $body = ParagraphWidget::fromText(Text::fromLines(
            ...array_map(fn (string $line) => Line::fromString($line), $paragraphs),
        ))->wrap(Wrap::Character);

        $footer = ParagraphWidget::fromText(Text::fromLines(
            Line::fromSpans(
                Span::fromString($article->url())
                    ->style(Style::default()->addModifier(Modifier::DIM)->addModifier(Modifier::UNDERLINED)),
            ),
        ));

        $sections = [
            [Constraint::length($rows), $header],
            [Constraint::length(1), $meta],
        ];

        if ($badges !== '') {
            $label = ParagraphWidget::fromText(Text::fromLines(
                Line::fromSpans(
                    Span::fromString($badges)->style(Style::default()->addModifier(Modifier::DIM)),
                ),
            ));
            $sections[] = [Constraint::length(1), $label];
        }

        $sections[] = [Constraint::min(0), $body];
        $sections[] = [Constraint::length(1), $footer];

        $fixed = 0;
        foreach ($sections as [$constraint]) {
            if ($constraint instanceof LengthConstraint) {
                $fixed += $constraint->length;
            }
        }

        $visible = max(1, $this->height - $fixed - self::BORDER - self::STATUS);
        $this->state->preview->limit = max(0, $wrapped - $visible);
        $body->scroll[0] = $this->state->preview->scroll;

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(...array_map(fn (array $section) => $section[0], $sections))
            ->widgets(...array_map(fn (array $section) => $section[1], $sections));
    }

    /**
     * キーバインド一覧のヘルプモーダルを構築する
     */
    private function help(): Widget
    {
        $fromSources = $this->state->origin() === Mode::Sources;

        $lines = [Line::fromString('')];

        if ($fromSources) {
            $lines = array_merge($lines, [
                Line::fromString('    j/k ↑/↓  カーソルで移動する'),
                Line::fromString('    a        ソースを追加する'),
                Line::fromString('    d        ソースを削除する'),
                Line::fromString('    t        追跡を切り替える'),
                Line::fromString('    r        記事一覧を更新する'),
                Line::fromString('    Esc      記事一覧へ戻る'),
            ]);
        } else {
            $lines = array_merge($lines, [
                Line::fromString('    j/k ↑/↓  カーソルで移動する'),
                Line::fromString('    →/Enter  プレビューにフォーカスする'),
                Line::fromString('    /        タイトルで検索する'),
                Line::fromString('    l        ラベルで絞り込む'),
                Line::fromString('    m        既読にする'),
                Line::fromString('    b        ブックマークを切り替える'),
                Line::fromString('    r        記事一覧を更新する'),
                Line::fromString('    s        ソース一覧に切り替える'),
            ]);
        }

        $lines = array_merge($lines, [
            Line::fromString(''),
            Line::fromString('    ?        ヘルプを切り替える'),
            Line::fromString('    q        終了する'),
        ]);

        $content = ParagraphWidget::fromText(Text::fromLines(...$lines));

        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Rounded)
            ->borderStyle(Style::default()->fg(AnsiColor::Cyan))
            ->titles(Title::fromString(' キーバインド '))
            ->widget($content);

        return BufferWidget::new(function (BufferContext $context) use ($block): void {
            $area = $context->area;

            $context->buffer->setStyle(null, Style::default()->fg(AnsiColor::DarkGray)->removeModifier(Modifier::REVERSED));

            $marginX = (int) floor($area->width * 0.25);
            $marginTop = (int) floor($area->height * 0.15);
            $marginBottom = $marginTop + self::STATUS;

            $inner = Area::fromScalars(
                $area->position->x + $marginX,
                $area->position->y + $marginTop,
                max(1, $area->width - $marginX * 2),
                max(1, $area->height - $marginTop - $marginBottom),
            );

            $context->draw($block, $inner);
        });
    }

    /**
     * ローディングスピナーを画面中央にオーバーレイ表示する
     */
    private function loading(): Widget
    {
        $content = ParagraphWidget::fromText(Text::fromLines(
            new Line(
                [Span::fromString('⟳')->style(Style::default()->fg(AnsiColor::Cyan))],
                HorizontalAlignment::Center,
            ),
        ));

        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Rounded)
            ->borderStyle(Style::default()->fg(AnsiColor::Cyan))
            ->widget($content);

        return BufferWidget::new(function (BufferContext $context) use ($block): void {
            $area = $context->area;

            $context->buffer->setStyle(null, Style::default()->fg(AnsiColor::DarkGray)->removeModifier(Modifier::REVERSED));

            $spinnerWidth = 7;
            $spinnerHeight = 3;
            $centerX = $area->position->x + (int) floor(($area->width - $spinnerWidth) / 2);
            $centerY = $area->position->y + (int) floor(($area->height - self::STATUS - $spinnerHeight) / 2);

            $inner = Area::fromScalars($centerX, $centerY, $spinnerWidth, $spinnerHeight);

            $context->draw($block, $inner);
        });
    }

    /**
     * 通知メッセージを右下にオーバーレイ表示する
     */
    private function toast(): Widget
    {
        $notice = $this->state->notice;
        $color = $notice->success ? AnsiColor::Green : AnsiColor::Red;

        $content = ParagraphWidget::fromText(Text::fromLines(
            Line::fromSpans(
                Span::fromString($notice->message)->style(Style::default()->fg($color)),
            ),
        ));

        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderType(BorderType::Rounded)
            ->borderStyle(Style::default()->fg($color))
            ->widget($content);

        $toastWidth = min($this->width - 2, mb_strwidth($notice->message) + 4);
        $toastHeight = 3;

        return BufferWidget::new(function (BufferContext $context) use ($block, $toastWidth, $toastHeight): void {
            $area = $context->area;

            $inner = Area::fromScalars(
                max(0, $area->position->x + $area->width - $toastWidth - 1),
                max(0, $area->position->y + $area->height - $toastHeight),
                $toastWidth,
                $toastHeight,
            );

            $context->buffer->setStyle($inner, Style::default()->removeModifier(Modifier::REVERSED));

            $context->draw($block, $inner);
        });
    }

    /**
     * 画面下部のステータスバーを構築する
     *
     * プロンプト入力中はモード名とカーソルを表示する
     */
    private function status(): Widget
    {
        $prompting = $this->state->prompting();

        if ($prompting) {
            $label = match ($this->state->mode) {
                Mode::ArticleSearch => 'タイトル検索',
                Mode::ArticleLabelFilter => 'ラベル検索',
                Mode::SourceAddUrl => 'URL入力',
                Mode::SourceAddName => '名前入力',
                default => '',
            };
            $content = ParagraphWidget::fromString($this->state->prompt->value.'▌');
        } else {
            $label = '';
            $content = ParagraphWidget::fromString('');
        }

        $border = $prompting
            ? Style::default()->fg(AnsiColor::Cyan)
            : Style::default()->fg(AnsiColor::DarkGray);

        $block = BlockWidget::default()
            ->borders(Borders::ALL)
            ->borderStyle($border)
            ->widget($content);

        if ($label !== '') {
            $block = $block->titles(Title::fromString(" {$label} "));
        }

        return $block;
    }
}
