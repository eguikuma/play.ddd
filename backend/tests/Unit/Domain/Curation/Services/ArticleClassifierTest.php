<?php

namespace Tests\Unit\Domain\Curation\Services;

use App\Domain\Curation\Aggregates\ClassificationRule;
use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ClassificationRuleRepository;
use App\Domain\Curation\Services\ArticleClassifier;
use App\Domain\Curation\ValueObjects\Label;
use App\Domain\Curation\ValueObjects\MatchField;
use App\Domain\Curation\ValueObjects\RulePattern;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleClassifierTest extends TestCase
{
    #[Test]
    public function 一致するルールのラベルが記事に付与される(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('Laravel'),
            false,
            new Label('laravel'),
            MatchField::Title,
        );

        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([$rule]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Laravel 12 Released',
            'https://example.com/laravel-12',
            'Laravel 12 has been released.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertCount(1, $article->labels());
        $this->assertSame('laravel', $article->labels()[0]->value());
    }

    #[Test]
    public function 一致しないルールのラベルは付与されない(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('Vue'),
            false,
            new Label('vue'),
            MatchField::Title,
        );

        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([$rule]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Laravel 12 Released',
            'https://example.com/laravel-12',
            'Laravel 12 has been released.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertEmpty($article->labels());
    }

    #[Test]
    public function 複数のルールが一致すると複数のラベルが付与される(): void
    {
        $rule1 = ClassificationRule::create(
            new RulePattern('Laravel'),
            false,
            new Label('laravel'),
            MatchField::Title,
        );

        $rule2 = ClassificationRule::create(
            new RulePattern('Released'),
            false,
            new Label('release'),
            MatchField::Title,
        );

        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([$rule1, $rule2]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Laravel 12 Released',
            'https://example.com/laravel-12',
            'Laravel 12 has been released.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertCount(2, $article->labels());
    }

    #[Test]
    public function ルールがない場合はラベルが付与されない(): void
    {
        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Some Article',
            'https://example.com/article',
            'Content.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertEmpty($article->labels());
    }

    #[Test]
    public function URLフィールドで一致するルールのラベルが付与される(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('laravel-news'),
            false,
            new Label('laravel'),
            MatchField::Url,
        );

        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([$rule]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Some Title',
            'https://laravel-news.com/article',
            'Some content.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertCount(1, $article->labels());
        $this->assertSame('laravel', $article->labels()[0]->value());
    }

    #[Test]
    public function 本文フィールドで一致するルールのラベルが付与される(): void
    {
        $rule = ClassificationRule::create(
            new RulePattern('Eloquent'),
            false,
            new Label('database'),
            MatchField::Content,
        );

        $repository = $this->createStub(ClassificationRuleRepository::class);
        $repository->method('findAllEnabled')->willReturn([$rule]);

        $classifier = new ArticleClassifier($repository);

        $article = ReadableArticle::fromCollected(
            'article-1',
            'source-1',
            'Some Title',
            'https://example.com/article',
            'This article covers Eloquent ORM in depth.',
            new \DateTimeImmutable,
        );

        $classifier->classify($article);

        $this->assertCount(1, $article->labels());
        $this->assertSame('database', $article->labels()[0]->value());
    }
}
