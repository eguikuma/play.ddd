<?php

namespace App\UseCases\Tracking;

use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\SourceId;

class RemoveSource
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
    ) {}

    public function execute(string $id): void
    {
        $sourceId = new SourceId($id);

        $source = $this->sourceRepository->findById($sourceId);

        if ($source === null) {
            throw new \DomainException('指定されたソースが見つかりません');
        }

        $this->sourceRepository->remove($sourceId);
    }
}
