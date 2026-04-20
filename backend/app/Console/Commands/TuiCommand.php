<?php

namespace App\Console\Commands;

use App\Console\Tui\Screen;
use App\UseCases\Collection\CollectAll;
use App\UseCases\Curation\BookmarkArticle;
use App\UseCases\Curation\ListUnreadArticles;
use App\UseCases\Curation\MarkAsRead;
use App\UseCases\Curation\UnbookmarkArticle;
use App\UseCases\Tracking\AddSource;
use App\UseCases\Tracking\ListSources;
use App\UseCases\Tracking\PauseSource;
use App\UseCases\Tracking\RemoveSource;
use App\UseCases\Tracking\ResumeSource;
use Illuminate\Console\Command;

class TuiCommand extends Command
{
    protected $signature = 'den';

    protected $description = 'ソースと記事をインタラクティブに管理する';

    public function handle(
        ListUnreadArticles $listUnreadArticles,
        MarkAsRead $markAsRead,
        BookmarkArticle $bookmarkArticle,
        UnbookmarkArticle $unbookmarkArticle,
        AddSource $addSource,
        ListSources $listSources,
        RemoveSource $removeSource,
        PauseSource $pauseSource,
        ResumeSource $resumeSource,
        CollectAll $collectAll,
    ): int {
        return (new Screen(
            $listUnreadArticles,
            $markAsRead,
            $bookmarkArticle,
            $unbookmarkArticle,
            $addSource,
            $listSources,
            $removeSource,
            $pauseSource,
            $resumeSource,
            $collectAll,
        ))->run();
    }
}
