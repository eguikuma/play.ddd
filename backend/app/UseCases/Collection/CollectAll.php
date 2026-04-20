<?php

namespace App\UseCases\Collection;

use App\Domain\Collection\Aggregates\FetchExecution;
use App\Domain\Tracking\Repositories\SourceRepository;

class CollectAll
{
    public function __construct(
        private readonly SourceRepository $sourceRepository,
        private readonly CollectSource $collectSource,
    ) {}

    /**
     * アクティブな全ソースから記事を収集し、取得実行記録の一覧を返す
     *
     * 個々のソースで収集に失敗しても他のソースの収集は継続する
     * 失敗したソースの実行記録は結果に含まれない
     *
     * @return array{executions: FetchExecution[], failures: int}
     */
    public function execute(): array
    {
        $sources = $this->sourceRepository->findAllActive();
        $executions = [];
        $failures = 0;

        foreach ($sources as $source) {
            try {
                $executions[] = $this->collectSource->execute($source->id()->value());
            } catch (\Throwable) {
                /**
                 * 個々のソースの失敗は他のソースの収集を妨げない
                 */
                $failures++;
            }
        }

        return ['executions' => $executions, 'failures' => $failures];
    }
}
