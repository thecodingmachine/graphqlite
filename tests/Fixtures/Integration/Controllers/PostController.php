<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;

class PostController
{

    /**
     * @Mutation()
     * @param Post $post
     *
     * @return Post
     */
    public function createPost(Post $post): Post
    {
        return $post;
    }

    /**
     * @Mutation()
     * @UseInputType(for="$post", inputType="UpdatePostInput")
     *
     * @param int  $id
     * @param Post $post
     *
     * @return Post
     */
    public function updatePost(int $id, Post $post): Post
    {

        $post->id = $id;

        return $post;
    }
}
