<?php

namespace App\Infrastructure\Persistence\Curation;

use Illuminate\Database\Eloquent\Model;

class EloquentArticleLabel extends Model
{
    protected $table = 'article_labels';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];
}
