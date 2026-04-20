<?php

namespace App\UseCases\Tracking;

use App\Domain\Tracking\Repositories\SourceRepository;
use App\Domain\Tracking\ValueObjects\SourceId;

class ResumeSource
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
    ) {}

    public function execute(string $id): void
    {
        $source = $this->sourceRepository->findById(new SourceId($id));

        if ($source === null) {
            throw new \DomainException('指定されたソースが見つかりません');
        }

        $source->resume();

        $this->sourceRepository->save($source);
    }
}
