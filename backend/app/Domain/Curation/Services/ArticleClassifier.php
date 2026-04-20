<?php

namespace App\Domain\Curation\Services;

use App\Domain\Curation\Aggregates\ReadableArticle;
use App\Domain\Curation\Repositories\ClassificationRuleRepository;
use App\Domain\Curation\ValueObjects\MatchField;

/**
 * 記事分類サービス
 *
 * 有効な分類ルールを記事に適用し、一致するルールのラベルを記事に付与する
 */
class ArticleClassifier
{
    public function __construct(
        private readonly ClassificationRuleRepository $ruleRepository,
    ) {}

    /**
     * 有効な分類ルールを記事に適用し、一致したルールのラベルを付与する
     *
     * 各ルールに対応する記事フィールド（タイトル・URL・本文）を照合対象とし、一致したルールのラベルを記事に追加する
     */
    public function classify(ReadableArticle $article): void
    {
        $rules = $this->ruleRepository->findAllEnabled();

        foreach ($rules as $rule) {
            $text = match ($rule->matchField()) {
                MatchField::Title => $article->title(),
                MatchField::Url => $article->url(),
                MatchField::Content => $article->body(),
            };

            if ($rule->matches($text)) {
                $article->addLabel($rule->label());
            }
        }
    }
}
