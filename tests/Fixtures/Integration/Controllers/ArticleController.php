<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Article;

class ArticleController
{
    /**
     * @Mutation()
     */
    public function createArticle(Article $article): Article
    {
        return $article;
    }
}
