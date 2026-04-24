<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Cost;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Article;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\UpdateArticleInput;
use TheCodingMachine\GraphQLite\Undefined;

class ArticleController
{
    /** @return Article[] */
    #[Query]
    #[Cost(complexity: 5, multipliers: ['take'], defaultMultiplier: 500)]
    public function articles(int|null $take = 10): array
    {
        return [
            new Article('Title'),
        ];
    }

    #[Mutation]
    public function createArticle(Article $article): Article
    {
        return $article;
    }

    #[Mutation]
    public function updateArticle(UpdateArticleInput $input): Article
    {
        $article = new Article('test');
        $article->magazine = 'The New Yorker';

        $article->summary = $input->summary;

        if ($input->magazine !== Undefined::VALUE) {
            $article->magazine = $input->magazine;
        }

        return $article;
    }
}
