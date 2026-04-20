<?php

namespace App\Infrastructure\Persistence\Curation;

use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\Repositories\ClassificationRuleRepository;
use App\Domain\Curation\ValueObjects\ClassificationRuleId;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\MatchField;
use App\Domain\Curation\ValueObjects\RulePattern;

class MysqlClassificationRuleRepository implements ClassificationRuleRepository
{
    public function save(ClassificationRule $rule): void
    {
        EloquentClassificationRule::updateOrCreate(
            ['id' => $rule->id()->value()],
            [
                'pattern' => $rule->pattern()->value(),
                'is_regex' => $rule->isRegex(),
                'label' => $rule->label()->value(),
                'match_field' => $rule->matchField()->value,
                'enabled' => $rule->enabled(),
            ],
        );
    }

    public function findAllEnabled(): array
    {
        return EloquentClassificationRule::where('enabled', true)
            ->get()
            ->map(fn (EloquentClassificationRule $rule) => $this->toDomain($rule))
            ->all();
    }

    public function findAll(): array
    {
        return EloquentClassificationRule::all()
            ->map(fn (EloquentClassificationRule $rule) => $this->toDomain($rule))
            ->all();
    }

    public function remove(ClassificationRuleId $id): void
    {
        EloquentClassificationRule::destroy($id->value());
    }

    private function toDomain(EloquentClassificationRule $rule): ClassificationRule
    {
        return ClassificationRule::reconstruct(
            new ClassificationRuleId($rule->id),
            new RulePattern($rule->pattern),
            (bool) $rule->is_regex,
            new Label($rule->label),
            MatchField::from($rule->match_field),
            (bool) $rule->enabled,
        );
    }
}
