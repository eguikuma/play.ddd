<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 記事のタイトル
 */
class ArticleTitle
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('記事タイトルは空にできません');
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
