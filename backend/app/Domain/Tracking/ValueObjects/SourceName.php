<?php

namespace App\Domain\Tracking\ValueObjects;

/**
 * ソースの表示名
 */
class SourceName
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('ソース名は空にできません');
        }

        if (mb_strlen($trimmed) > 255) {
            throw new \InvalidArgumentException('ソース名は255文字以内にしてください');
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
