<?php

namespace App\Domain\Curation\Repositories;

use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\ValueObjects\ClassificationRuleId;

/**
 * 分類ルールリポジトリのインターフェース
 */
interface ClassificationRuleRepository
{
    public function save(ClassificationRule $rule): void;

    /**
     * @return ClassificationRule[]
     */
    public function findAllEnabled(): array;

    /**
     * @return ClassificationRule[]
     */
    public function findAll(): array;

    public function remove(ClassificationRuleId $id): void;
}
