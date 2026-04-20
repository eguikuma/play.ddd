<?php

namespace App\Domain\Collection\Repositories;

use App\Domain\Collection\Aggregates\Article;
use App\Domain\Collection\ValueObjects\ContentFingerprint;
use App\Domain\Collection\ValueObjects\SourceReference;

/**
 * 記事リポジトリのインターフェース
 */
interface ArticleRepository
{
    public function save(Article $article): void;

    public function existsByFingerprint(ContentFingerprint $fingerprint): bool;

    /**
     * @return Article[]
     */
    public function findBySourceReference(SourceReference $sourceReference): array;
}
