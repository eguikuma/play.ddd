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
    public function execute(?string $rawLabel = null): array
    {
        $label = $rawLabel !== null ? new Label($rawLabel) : null;

        return $this->readableArticleRepository->findUnread($label);
    }
}
