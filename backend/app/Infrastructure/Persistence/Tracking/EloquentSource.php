<?php

namespace App\Infrastructure\Persistence\Tracking;

use Illuminate\Database\Eloquent\Model;

class EloquentSource extends Model
{
    protected $table = 'sources';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_fetched_at' => 'datetime',
        ];
    }
}
