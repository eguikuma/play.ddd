<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            DB::table('article_labels')->updateOrInsert(
                ['article_id' => $row['article_id'], 'value' => $row['value']],
            );
        }
    }

    private function rows(): array
    {
        return CsvReader::read(__DIR__.'/data/labels.csv');
    }
}
