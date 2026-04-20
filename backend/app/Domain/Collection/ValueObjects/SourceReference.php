<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 別コンテキスト（Tracking）のソースIDへの参照
 */
class SourceReference
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('ソース参照は空にできません');
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
