<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use Exception;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Comment;
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

    /**
     * @param Comment[] $prefetchedComments
     *
     * @return Comment[]
     */
    #[Field]
    public function getComments(
        Post $post,
        #[Prefetch('prefetchComments', true)]
        array $prefetchedComments,
    ): array {
        return $prefetchedComments;
    }

    /**
     * @param Post[] $posts
     *
     * @return Promise[]
     *
     * @throws Exception
     */
    public static function prefetchComments(array $posts): iterable
    {
        $syncPromiseAdapter = new SyncPromiseAdapter();
        foreach ($posts as $key => $post) {
            yield $key => $syncPromiseAdapter->createFulfilled([new Comment('comment for ' . $post->title)]);
        }
    }
}
