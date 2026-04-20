<?php

namespace App\UseCases\Curation;

use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Curation\ValueObjects\ReadableArticleId;

class BookmarkArticle
{
    public function __construct(
        private readonly ReadableArticleRepository $readableArticleRepository,
    ) {}

    public function execute(string $articleId): void
    {
        $article = $this->readableArticleRepository->findById(
            new ReadableArticleId($articleId),
        );

        if ($article === null) {
            throw new \DomainException('指定された記事が見つかりません');
        }

        $article->bookmark();

        $this->readableArticleRepository->save($article);
    }
}
