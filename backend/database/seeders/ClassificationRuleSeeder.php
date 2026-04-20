<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationRuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            DB::table('classification_rules')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'pattern' => $row['pattern'],
                    'is_regex' => $row['is_regex'] === 'true',
                    'label' => $row['label'],
                    'match_field' => $row['match_field'],
                    'enabled' => true,
                ],
            );
        }
    }

    private function rows(): array
    {
        return CsvReader::read(__DIR__.'/data/classification_rules.csv');
    }
}
