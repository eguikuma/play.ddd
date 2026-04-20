<?php

namespace App\Domain\Curation\Aggregates;

use App\Domain\Curation\ValueObjects\ClassificationRuleId;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\MatchField;
use App\Domain\Curation\ValueObjects\RulePattern;

/**
 * 分類ルール
 *
 * 記事のフィールド（タイトル・URL・本文）に対してパターンマッチを行い、一致した記事にラベルを付与するルールを表す
 */
class ClassificationRule
{
    private function __construct(
        private readonly ClassificationRuleId $id,
        private readonly RulePattern $pattern,
        private readonly bool $isRegex,
        private readonly Label $label,
        private readonly MatchField $matchField,
        private bool $enabled,
    ) {}

    /**
     * 新しい分類ルールを作成する
     */
    public static function create(
        RulePattern $pattern,
        bool $isRegex,
        Label $label,
        MatchField $matchField,
    ): self {
        if ($isRegex && @preg_match($pattern->value(), '') === false) {
            throw new \DomainException('正規表現パターンの構文が正しくありません');
        }

        return new self(
            id: ClassificationRuleId::generate(),
            pattern: $pattern,
            isRegex: $isRegex,
            label: $label,
            matchField: $matchField,
            enabled: true,
        );
    }

    /**
     * 永続化されたデータから分類ルールを復元する
     */
    public static function reconstruct(
        ClassificationRuleId $id,
        RulePattern $pattern,
        bool $isRegex,
        Label $label,
        MatchField $matchField,
        bool $enabled,
    ): self {
        return new self(
            id: $id,
            pattern: $pattern,
            isRegex: $isRegex,
            label: $label,
            matchField: $matchField,
            enabled: $enabled,
        );
    }

    /**
     * 対象テキストがこのルールに一致するかを判定する
     *
     * isRegex が true の場合は正規表現マッチ（preg_match）を使用する
     * false の場合はキーワードの部分一致（大文字小文字を区別しない）で判定する
     */
    public function matches(string $text): bool
    {
        if ($this->isRegex) {
            return (bool) preg_match($this->pattern->value(), $text);
        }

        return mb_stripos($text, $this->pattern->value()) !== false;
    }

    public function id(): ClassificationRuleId
    {
        return $this->id;
    }

    public function pattern(): RulePattern
    {
        return $this->pattern;
    }

    public function isRegex(): bool
    {
        return $this->isRegex;
    }

    public function label(): Label
    {
        return $this->label;
    }

    public function matchField(): MatchField
    {
        return $this->matchField;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
