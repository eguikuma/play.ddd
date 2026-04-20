<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            DB::table('sources')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'name' => $row['name'],
                    'url' => $row['url'],
                    'kind' => 'rss',
                    'status' => $row['status'],
                    'fetch_interval_minutes' => 60,
                    'registered_at' => now(),
                    'last_fetched_at' => now(),
                ],
            );
        }
    }

    private function rows(): array
    {
        return CsvReader::read(__DIR__.'/data/sources.csv');
    }
}
