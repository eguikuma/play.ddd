<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            DB::table('articles')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'source_id' => $row['source_id'],
                    'source_kind' => 'rss',
                    'title' => $row['title'],
                    'url' => $row['url'],
                    'body' => $row['body'],
                    'fingerprint' => hash('sha256', $row['url']),
                    'published_at' => $row['published_at'],
                    'collected_at' => now(),
                    'reading_status' => $row['reading_status'],
                    'bookmarked' => $row['bookmarked'] === 'true',
                    'read_at' => null,
                ],
            );
        }
    }

    private function rows(): array
    {
        return CsvReader::read(__DIR__.'/data/articles.csv');
    }
}
