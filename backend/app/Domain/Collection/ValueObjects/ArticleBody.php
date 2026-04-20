<?php

namespace App\Domain\Collection\ValueObjects;

/**
 * 記事の本文
 *
 * フィードによっては本文がない場合があるため空文字を許容する
 */
class ArticleBody
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * 本文の抜粋を返す
     *
     * 本文が $maxLength 文字以下の場合はそのまま返す
     * 超過する場合は $maxLength 文字で切り詰め、末尾に「…」を付加する
     */
    public function excerpt(int $maxLength = 500): string
    {
        if (mb_strlen($this->value) <= $maxLength) {
            return $this->value;
        }

        return mb_substr($this->value, 0, $maxLength).'…';
    }
}
