<?php

namespace App\Providers;

use App\Domain\Collection\Events\ArticleCollected;
use App\Domain\Collection\Repositories\ArticleRepository;
use App\Domain\Collection\Repositories\FetchExecutionRepository;
use App\Domain\Collection\Services\ContentFetcher;
use App\Domain\Collection\Services\ContentParser;
use App\Domain\Curation\Repositories\ClassificationRuleRepository;
use App\Domain\Curation\Repositories\ReadableArticleRepository;
use App\Domain\Shared\Events\EventDispatcher;
use App\Domain\Tracking\Repositories\SourceRepository;
use App\Infrastructure\Events\LaravelEventDispatcher;
use App\Infrastructure\Http\HttpContentFetcher;
use App\Infrastructure\Parser\RssContentParser;
use App\Infrastructure\Persistence\Collection\MysqlArticleRepository;
use App\Infrastructure\Persistence\Collection\MysqlFetchExecutionRepository;
use App\Infrastructure\Persistence\Curation\MysqlClassificationRuleRepository;
use App\Infrastructure\Persistence\Curation\MysqlReadableArticleRepository;
use App\Infrastructure\Persistence\Tracking\MysqlSourceRepository;
use App\UseCases\Curation\ClassifyArticleOnCollected;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SourceRepository::class, MysqlSourceRepository::class);
        $this->app->bind(ArticleRepository::class, MysqlArticleRepository::class);
        $this->app->bind(FetchExecutionRepository::class, MysqlFetchExecutionRepository::class);
        $this->app->bind(ReadableArticleRepository::class, MysqlReadableArticleRepository::class);
        $this->app->bind(ClassificationRuleRepository::class, MysqlClassificationRuleRepository::class);
        $this->app->bind(ContentParser::class, RssContentParser::class);
        $this->app->bind(ContentFetcher::class, HttpContentFetcher::class);
        $this->app->bind(EventDispatcher::class, LaravelEventDispatcher::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            ArticleCollected::class,
            [ClassifyArticleOnCollected::class, 'handle'],
        );
    }
}
