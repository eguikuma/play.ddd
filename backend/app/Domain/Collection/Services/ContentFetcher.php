<?php

namespace App\Domain\Collection\Services;

/**
 * ソースURLからコンテンツを取得するインターフェース
 */
interface ContentFetcher
{
    /**
     * 指定URLからフィードの生コンテンツを取得する
     *
     * @return string RSSまたはAtomフィードのXML文字列
     *
     * @throws \RuntimeException 取得に失敗した場合
     */
    public function fetch(string $url): string;
}
