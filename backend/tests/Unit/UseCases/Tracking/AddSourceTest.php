<?php

namespace Tests\Unit\UseCases\Tracking;

use App\Domain\Tracking\ValueObjects\SourceKind;
use App\Infrastructure\Persistence\Tracking\InMemorySourceRepository;
use App\UseCases\Tracking\AddSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddSourceTest extends TestCase
{
    private InMemorySourceRepository $repository;

    private AddSource $useCase;

    protected function setUp(): void
    {
        $this->repository = new InMemorySourceRepository;
        $this->useCase = new AddSource($this->repository);
    }

    #[Test]
    public function ソースを追加できる(): void
    {
        $source = $this->useCase->execute(
            'https://laravel-news.com/feed',
            'Laravel News',
            SourceKind::Rss,
            60,
        );

        $this->assertSame('Laravel News', $source->name()->value());
        $this->assertSame('https://laravel-news.com/feed', $source->url()->value());
        $this->assertNotNull($this->repository->findById($source->id()));
    }

    #[Test]
    public function 名前を省略するとホスト名がソース名になる(): void
    {
        $source = $this->useCase->execute(
            'https://laravel-news.com/feed',
            null,
            SourceKind::Rss,
            60,
        );

        $this->assertSame('laravel-news.com', $source->name()->value());
    }

    #[Test]
    public function 同じURLのソースは重複登録できない(): void
    {
        $this->useCase->execute('https://example.com/rss', 'Test', SourceKind::Rss, 60);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('このURLのソースは既に登録されています');

        $this->useCase->execute('https://example.com/rss', 'Test 2', SourceKind::Rss, 60);
    }
}
