<?php

namespace App\Domain\Curation\Repositories;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\ReadableArticleId;

/**
 * 閲覧可能記事リポジトリのインターフェース
 */
interface ReadableArticleRepository
{
    public function save(ReadableArticle $article): void;

    public function findById(ReadableArticleId $id): ?ReadableArticle;

    /**
     * @return ReadableArticle[]
     */
    public function findUnread(?Label $label = null): array;
}
