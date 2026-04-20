<?php

namespace App\Domain\Curation\ValueObjects;

/**
 * 記事に付与されるラベル
 */
class Label
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('ラベルは空にできません');
        }

        if (mb_strlen($trimmed) > 50) {
            throw new \InvalidArgumentException('ラベルは50文字以内にしてください');
        }

        $this->value = mb_strtolower($trimmed);
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
