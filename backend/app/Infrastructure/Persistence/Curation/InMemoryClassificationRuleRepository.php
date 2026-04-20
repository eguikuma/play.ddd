<?php

namespace App\Infrastructure\Persistence\Curation;

use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\Repositories\ClassificationRuleRepository;
use App\Domain\Curation\ValueObjects\ClassificationRuleId;

class InMemoryClassificationRuleRepository implements ClassificationRuleRepository
{
    /** @var array<string, ClassificationRule> */
    private array $rules = [];

    public function save(ClassificationRule $rule): void
    {
        $this->rules[$rule->id()->value()] = $rule;
    }

    public function findAllEnabled(): array
    {
        return array_values(
            array_filter($this->rules, fn (ClassificationRule $rule) => $rule->enabled()),
        );
    }

    public function findAll(): array
    {
        return array_values($this->rules);
    }

    public function remove(ClassificationRuleId $id): void
    {
        unset($this->rules[$id->value()]);
    }
}
