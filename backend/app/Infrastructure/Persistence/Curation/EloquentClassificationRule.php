<?php

namespace App\Infrastructure\Persistence\Curation;

use Illuminate\Database\Eloquent\Model;

class EloquentClassificationRule extends Model
{
    protected $table = 'classification_rules';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_regex' => 'boolean',
            'enabled' => 'boolean',
        ];
    }
}
