<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;

#[ExtendType(class:Post::class)]
class PostType
{
    #[Field]
    public function getId(Post $post): int
    {
        return (int) $post->id;
    }

    #[Field]
    public function getTitle(Post $post): string
    {
        return $post->title;
    }
}
