<?php

namespace App\UseCases\Tracking;

use App\Domain\Tracking\Aggregates\Source;
use App\Domain\Tracking\Repositories\SourceRepository;

class ListSources
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
    ) {}

    /**
     * @return Source[]
     */
    public function execute(): array
    {
        return $this->sourceRepository->findAll();
    }
}
