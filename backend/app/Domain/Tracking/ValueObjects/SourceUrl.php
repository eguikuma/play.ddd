<?php

namespace App\Domain\Tracking\ValueObjects;

/**
 * ソースのURL
 */
class SourceUrl
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('ソースURLは空にできません');
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('ソースURLの形式が正しくありません');
        }

        $scheme = parse_url($trimmed, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('ソースURLはhttp/httpsのみ有効です');
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
