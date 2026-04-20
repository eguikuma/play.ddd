<?php

namespace App\Domain\Shared\ValueObjects;

use Ramsey\Uuid\Uuid;

/**
 * 全エンティティの識別子の基底クラス
 */
abstract class Identifier
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('識別子は空にできません');
        }

        $this->value = $trimmed;
    }

    public static function generate(): static
    {
        return new static((string) Uuid::uuid7());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return static::class === $other::class && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
