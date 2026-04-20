<?php

namespace App\Infrastructure\Persistence\Collection;

use Illuminate\Database\Eloquent\Model;

class EloquentArticle extends Model
{
    protected $table = 'articles';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'collected_at' => 'datetime',
        ];
    }
}
