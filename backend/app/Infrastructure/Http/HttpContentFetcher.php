<?php

namespace App\Infrastructure\Http;

use App\Domain\Collection\Services\ContentFetcher;
use Illuminate\Support\Facades\Http;

class HttpContentFetcher implements ContentFetcher
{
    public function fetch(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'den/1.0 (RSS Aggregator)',
        ])->timeout(30)->get($url);

        if ($response->failed()) {
            throw new \RuntimeException(
                "コンテンツの取得に失敗しました: {$url} (HTTP {$response->status()})",
            );
        }

        return $response->body();
    }
}
