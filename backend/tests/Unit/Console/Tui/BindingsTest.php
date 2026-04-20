<?php

namespace Tests\Unit\Console\Tui;

use App\Console\Tui\Action;
use App\Console\Tui\Bindings;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BindingsTest extends TestCase
{
    #[Test]
    public function qキーで終了する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('q'));

        $this->assertSame(Action::Exit, $action);
    }

    #[Test]
    public function CtrlCで終了する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('c', KeyModifiers::CONTROL));

        $this->assertSame(Action::Exit, $action);
    }

    #[Test]
    public function jキーで下に移動する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('j'));

        $this->assertSame(Action::Down, $action);
    }

    #[Test]
    public function kキーで上に移動する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('k'));

        $this->assertSame(Action::Up, $action);
    }

    #[Test]
    public function スラッシュキーで検索を開始する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('/'));

        $this->assertSame(Action::Search, $action);
    }

    #[Test]
    public function lキーでフィルタを開始する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('l'));

        $this->assertSame(Action::Filter, $action);
    }

    #[Test]
    public function sキーでソース一覧に切り替える(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('s'));

        $this->assertSame(Action::Sources, $action);
    }

    #[Test]
    public function aキーでソース追加を開始する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('a'));

        $this->assertSame(Action::Add, $action);
    }

    #[Test]
    public function 上矢印キーで上に移動する(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Up));

        $this->assertSame(Action::Up, $action);
    }

    #[Test]
    public function 下矢印キーで下に移動する(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Down));

        $this->assertSame(Action::Down, $action);
    }

    #[Test]
    public function Enterキーでプレビューにフォーカスする(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Enter));

        $this->assertSame(Action::Right, $action);
    }

    #[Test]
    public function 右矢印キーで右に移動する(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Right));

        $this->assertSame(Action::Right, $action);
    }

    #[Test]
    public function 左矢印キーで左に移動する(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Left));

        $this->assertSame(Action::Left, $action);
    }

    #[Test]
    public function mキーで既読にする(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('m'));

        $this->assertSame(Action::MarkRead, $action);
    }

    #[Test]
    public function bキーでブックマーク切替する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('b'));

        $this->assertSame(Action::Bookmark, $action);
    }

    #[Test]
    public function rキーでフェッチを実行する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('r'));

        $this->assertSame(Action::Fetch, $action);
    }

    #[Test]
    public function dキーでソースを削除する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('d'));

        $this->assertSame(Action::Remove, $action);
    }

    #[Test]
    public function tキーで追跡を切り替える(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('t'));

        $this->assertSame(Action::TogglePause, $action);
    }

    #[Test]
    public function クエスチョンキーでヘルプを表示する(): void
    {
        $action = Bindings::resolve(CharKeyEvent::new('?'));

        $this->assertSame(Action::Help, $action);
    }

    #[Test]
    public function Escキーでエスケープする(): void
    {
        $action = Bindings::resolve(CodedKeyEvent::new(KeyCode::Esc));

        $this->assertSame(Action::Escape, $action);
    }

    #[Test]
    public function プロンプトモードでCtrlCは終了する(): void
    {
        $action = Bindings::prompt(CharKeyEvent::new('c', KeyModifiers::CONTROL));

        $this->assertSame(Action::Exit, $action);
    }

    #[Test]
    public function プロンプトモードでEnterは入力を確定する(): void
    {
        $action = Bindings::prompt(CodedKeyEvent::new(KeyCode::Enter));

        $this->assertSame(Action::Submit, $action);
    }

    #[Test]
    public function プロンプトモードでEscは入力をキャンセルする(): void
    {
        $action = Bindings::prompt(CodedKeyEvent::new(KeyCode::Esc));

        $this->assertSame(Action::Cancel, $action);
    }

    #[Test]
    public function プロンプトモードでBackspaceは1文字削除する(): void
    {
        $action = Bindings::prompt(CodedKeyEvent::new(KeyCode::Backspace));

        $this->assertSame(Action::Delete, $action);
    }
}
