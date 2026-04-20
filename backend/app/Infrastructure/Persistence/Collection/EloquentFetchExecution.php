<?php

namespace App\Infrastructure\Persistence\Collection;

use Illuminate\Database\Eloquent\Model;

class EloquentFetchExecution extends Model
{
    protected $table = 'fetch_executions';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'new_article_count' => 'integer',
            'skipped_article_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
