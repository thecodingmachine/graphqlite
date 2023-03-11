<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;

class PostController
{
    /**
     * @Mutation()
     */
    public function createPost(Post $post): Post
    {
        return $post;
    }

    /**
     * @Mutation()
     * @UseInputType(for="$post", inputType="UpdatePostInput")
     */
    public function updatePost(int $id, Post $post): Post
    {
        $post->id = $id;

        return $post;
    }
}
