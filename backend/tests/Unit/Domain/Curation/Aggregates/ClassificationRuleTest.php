<?php

namespace Tests\Unit\Domain\Curation\Aggregates;

use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\ValueObjects\ClassificationRuleId;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\MatchField;
use App\Domain\Curation\ValueObjects\RulePattern;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClassificationRuleTest extends TestCase
{
    #[Test]
    public function 分類ルールを作成できる(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('Laravel'),
            false,
            new Label('laravel'),
            MatchField::Title,
        );

        $this->assertNotEmpty($rule->id()->value());
        $this->assertSame('Laravel', $rule->pattern()->value());
        $this->assertFalse($rule->isRegex());
        $this->assertSame('laravel', $rule->label()->value());
        $this->assertSame(MatchField::Title, $rule->matchField());
        $this->assertTrue($rule->enabled());
    }

    #[Test]
    public function キーワードパターンで記事タイトルに一致する(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('Laravel'),
            false,
            new Label('laravel'),
            MatchField::Title,
        );

        $this->assertTrue($rule->matches('Laravel 12 Released'));
        $this->assertTrue($rule->matches('New laravel features'));
        $this->assertFalse($rule->matches('PHP 8.4 Released'));
    }

    #[Test]
    public function 正規表現パターンで記事タイトルに一致する(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('/^Laravel\s+\d+/i'),
            true,
            new Label('laravel-release'),
            MatchField::Title,
        );

        $this->assertTrue($rule->matches('Laravel 12 Released'));
        $this->assertFalse($rule->matches('New Laravel features'));
    }

    #[Test]
    public function 永続化データから分類ルールを復元できる(): void
    {
        $rule = ClassificationRule::reconstruct(
            new ClassificationRuleId('rule-id-1'),
            new RulePattern('PHP'),
            false,
            new Label('php'),
            MatchField::Title,
            true,
        );

        $this->assertSame('rule-id-1', $rule->id()->value());
        $this->assertSame('PHP', $rule->pattern()->value());
        $this->assertFalse($rule->isRegex());
        $this->assertSame('php', $rule->label()->value());
        $this->assertSame(MatchField::Title, $rule->matchField());
        $this->assertTrue($rule->enabled());
    }

    #[Test]
    public function 不正な正規表現パターンで分類ルールは作成できない(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('正規表現パターンの構文が正しくありません');

        ClassificationRule::create(
            new RulePattern('[invalid regex'),
            true,
            new Label('test'),
            MatchField::Title,
        );
    }

    #[Test]
    public function 無効状態で復元した分類ルールは無効のままである(): void
    {
        $rule = ClassificationRule::reconstruct(
            new ClassificationRuleId('rule-id-2'),
            new RulePattern('Vue'),
            false,
            new Label('vue'),
            MatchField::Title,
            false,
        );

        $this->assertFalse($rule->enabled());
    }
}
