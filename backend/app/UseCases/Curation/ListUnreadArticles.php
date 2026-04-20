<?php

namespace App\UseCases\Curation;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Curation\ValueObjects\Label;

class ListUnreadArticles
{
    public function __construct(
        private readonly ReadableArticleRepository $readableArticleRepository,
    ) {}

    /**
     * @return ReadableArticle[]
     */
    public function execute(?string $label = null): array
    {
        $labelFilter = $label !== null ? new Label($label) : null;

        return $this->readableArticleRepository->findUnread($labelFilter);
    }
}
