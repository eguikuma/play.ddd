<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * 開発用テストデータを投入するシーダー
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SourceSeeder::class,
            ArticleSeeder::class,
            ClassificationRuleSeeder::class,
            LabelSeeder::class,
        ]);
    }
}
