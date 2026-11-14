<?php

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Post;

class PostController
{

    /**
     * @param Post $post
     *
     * @return Post
     */
    #[Mutation]
    public function createPost(Post $post): Post
    {
        return $post;
    }

    /**
     *
     * @param int  $id
     * @param Post $post
     *
     * @return Post
     */
    #[Mutation]
    public function updatePost(
        int $id,
        #[UseInputType('UpdatePostInput')]
        Post $post): Post
    {

        $post->id = $id;

        return $post;
    }
}
