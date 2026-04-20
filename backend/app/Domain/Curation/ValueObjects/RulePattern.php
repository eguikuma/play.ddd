<?php

namespace App\Domain\Curation\ValueObjects;

/**
 * 分類ルールのパターン文字列
 */
class RulePattern
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('ルールパターンは空にできません');
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
