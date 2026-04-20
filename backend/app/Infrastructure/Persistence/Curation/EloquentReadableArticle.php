<?php

namespace App\Infrastructure\Persistence\Curation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EloquentReadableArticle extends Model
{
    protected $table = 'articles';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bookmarked' => 'boolean',
            'published_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function labels(): HasMany
    {
        return $this->hasMany(EloquentArticleLabel::class, 'article_id');
    }
}
