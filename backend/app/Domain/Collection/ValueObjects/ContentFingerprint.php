<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 記事の重複検出用フィンガープリント
 *
 * 記事URLのSHA-256ハッシュ値
 * 同一URLの記事を重複保存しないための判定に使用する
 */
class ContentFingerprint
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('フィンガープリントは空にできません');
        }

        $this->value = $trimmed;
    }

    public static function fromUrl(string $url): self
    {
        return new self(hash('sha256', $url));
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
