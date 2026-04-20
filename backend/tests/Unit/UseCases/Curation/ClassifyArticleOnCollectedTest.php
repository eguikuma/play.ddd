<?php

namespace Tests\Unit\UseCases\Curation;

use App\Domain\Collection\Events\ArticleCollected;
use App\Domain\Collection\ValueObjects\CollectionMethod;
use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\Services\ArticleClassifier;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\MatchField;
use App\Domain\Curation\ValueObjects\RulePattern;
use App\Infrastructure\Persistence\Curation\InMemoryClassificationRuleRepository;
use App\Infrastructure\Persistence\Curation\InMemoryReadableArticleRepository;
use App\UseCases\Curation\ClassifyArticleOnCollected;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClassifyArticleOnCollectedTest extends TestCase
{
    #[Test]
    public function 収集された記事が分類される(): void
    {
        $articleRepository = new InMemoryReadableArticleRepository;
        $ruleRepository = new InMemoryClassificationRuleRepository;
        $classifier = new ArticleClassifier($ruleRepository);

        $handler = new ClassifyArticleOnCollected($articleRepository, $classifier);

        $event = new ArticleCollected(
            articleId: 'article-1',
            sourceReference: 'source-1',
            collectionMethod: CollectionMethod::Rss,
            title: 'Laravel 12 Released',
            url: 'https://example.com/laravel-12',
            body: 'New features in Laravel 12.',
            publishedAt: new \DateTimeImmutable,
            collectedAt: new \DateTimeImmutable,
        );

        $handler->handle($event);

        $articles = $articleRepository->findUnread();
        $this->assertCount(1, $articles);
        $this->assertSame('Laravel 12 Released', $articles[0]->title());
    }

    #[Test]
    public function 分類ルールに一致するとラベルが付与される(): void
    {
        $articleRepository = new InMemoryReadableArticleRepository;
        $ruleRepository = new InMemoryClassificationRuleRepository;
        $ruleRepository->save(ClassificationRule::create(
            new RulePattern('Laravel'),
            false,
            new Label('laravel'),
            MatchField::Title,
        ));
        $classifier = new ArticleClassifier($ruleRepository);

        $handler = new ClassifyArticleOnCollected($articleRepository, $classifier);

        $event = new ArticleCollected(
            articleId: 'article-1',
            sourceReference: 'source-1',
            collectionMethod: CollectionMethod::Rss,
            title: 'Laravel 12 Released',
            url: 'https://example.com/laravel-12',
            body: 'New features.',
            publishedAt: new \DateTimeImmutable,
            collectedAt: new \DateTimeImmutable,
        );

        $handler->handle($event);

        $articles = $articleRepository->findUnread();
        $this->assertCount(1, $articles[0]->labels());
        $this->assertSame('laravel', $articles[0]->labels()[0]->value());
    }

    #[Test]
    public function 同じ記事は重複して分類されない(): void
    {
        $articleRepository = new InMemoryReadableArticleRepository;
        $ruleRepository = new InMemoryClassificationRuleRepository;
        $classifier = new ArticleClassifier($ruleRepository);

        $handler = new ClassifyArticleOnCollected($articleRepository, $classifier);

        $event = new ArticleCollected(
            articleId: 'article-1',
            sourceReference: 'source-1',
            collectionMethod: CollectionMethod::Rss,
            title: 'Test',
            url: 'https://example.com/test',
            body: 'Body.',
            publishedAt: new \DateTimeImmutable,
            collectedAt: new \DateTimeImmutable,
        );

        $handler->handle($event);
        $handler->handle($event);

        $articles = $articleRepository->findUnread();
        $this->assertCount(1, $articles);
    }
}
