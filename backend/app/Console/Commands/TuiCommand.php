<?php

namespace App\Console\Commands;

use App\Console\Tui\Screen;
use Illuminate\Console\Command;

class TuiCommand extends Command
{
    protected $signature = 'den';

    protected $description = 'ソースと記事をインタラクティブに管理する';

    public function handle(Screen $screen): int
    {
        return $screen->run();
    }
}
