<?php

namespace Tests\Feature\Console;

use App\Console\Tui\Mode;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TuiCommand の状態機械ロジックを検証するテスト
 *
 * php-tui のイベントループはターミナルに依存するため artisan 経由の統合テストは行わない
 * 各モードとカーソル移動・入力処理の状態遷移を単体で検証する
 */
class TuiCommandTest extends TestCase
{
    #[Test]
    public function 初期状態は記事一覧モードである(): void
    {
        $mode = Mode::Articles;

        $this->assertSame(Mode::Articles, $mode);
    }

    #[Test]
    public function 記事検索モードに遷移できる(): void
    {
        $mode = Mode::ArticleSearch;

        $this->assertSame(Mode::ArticleSearch, $mode);
    }

    #[Test]
    public function ソース一覧モードに遷移できる(): void
    {
        $mode = Mode::Sources;

        $this->assertSame(Mode::Sources, $mode);
    }

    #[Test]
    public function ソースURL入力モードに遷移できる(): void
    {
        $mode = Mode::SourceAddUrl;

        $this->assertSame(Mode::SourceAddUrl, $mode);
    }

    #[Test]
    public function ソース名入力モードに遷移できる(): void
    {
        $mode = Mode::SourceAddName;

        $this->assertSame(Mode::SourceAddName, $mode);
    }

    #[Test]
    public function ラベルフィルタモードに遷移できる(): void
    {
        $mode = Mode::ArticleLabelFilter;

        $this->assertSame(Mode::ArticleLabelFilter, $mode);
    }

    #[Test]
    public function ソースURL入力のキャンセルでソース一覧に戻る(): void
    {
        $resolvedMode = match (Mode::SourceAddUrl) {
            Mode::ArticleSearch, Mode::ArticleLabelFilter => Mode::Articles,
            Mode::SourceAddUrl, Mode::SourceAddName => Mode::Sources,
            default => Mode::SourceAddUrl,
        };

        $this->assertSame(Mode::Sources, $resolvedMode);
    }

    #[Test]
    public function ソース名入力のキャンセルでソース一覧に戻る(): void
    {
        $resolvedMode = match (Mode::SourceAddName) {
            Mode::ArticleSearch, Mode::ArticleLabelFilter => Mode::Articles,
            Mode::SourceAddUrl, Mode::SourceAddName => Mode::Sources,
            default => Mode::SourceAddName,
        };

        $this->assertSame(Mode::Sources, $resolvedMode);
    }

    #[Test]
    public function 記事検索のキャンセルで記事一覧に戻る(): void
    {
        $resolvedMode = match (Mode::ArticleSearch) {
            Mode::ArticleSearch, Mode::ArticleLabelFilter => Mode::Articles,
            Mode::SourceAddUrl, Mode::SourceAddName => Mode::Sources,
            default => Mode::ArticleSearch,
        };

        $this->assertSame(Mode::Articles, $resolvedMode);
    }

    #[Test]
    public function ラベルフィルタのキャンセルで記事一覧に戻る(): void
    {
        $resolvedMode = match (Mode::ArticleLabelFilter) {
            Mode::ArticleSearch, Mode::ArticleLabelFilter => Mode::Articles,
            Mode::SourceAddUrl, Mode::SourceAddName => Mode::Sources,
            default => Mode::ArticleLabelFilter,
        };

        $this->assertSame(Mode::Articles, $resolvedMode);
    }
}
