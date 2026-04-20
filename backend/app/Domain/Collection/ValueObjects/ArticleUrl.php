<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 記事のURL
 */
class ArticleUrl
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('記事URLは空にできません');
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('記事URLの形式が正しくありません');
        }

        $scheme = parse_url($trimmed, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('記事URLはhttp/httpsのみ有効です');
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
