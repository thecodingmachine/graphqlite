<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use GraphQL\Deferred;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Blog
{
    public function __construct(
        private readonly int $id,
    ) {
    }

    #[Field(outputType: 'ID!')]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param Post[][] $prefetchedPosts
     *
     * @return Post[]
     */
    #[Field]
    public function getPosts(
        #[Prefetch('prefetchPosts')]
        array $prefetchedPosts,
    ): array {
        return $prefetchedPosts[$this->id];
    }

    /** @param Blog[][] $prefetchedSubBlogs */
    #[Field(outputType: '[Blog!]!')]
    public function getSubBlogs(
        #[Prefetch('prefetchSubBlogs')]
        array $prefetchedSubBlogs,
    ): Deferred {
        return new Deferred(fn () => $prefetchedSubBlogs[$this->id]);
    }

    /**
     * @param Blog[] $blogs
     *
     * @return Post[][]
     */
    public static function prefetchPosts(iterable $blogs): array
    {
        $posts = [];
        foreach ($blogs as $blog) {
            $blogId = $blog->getId();
            $posts[$blog->getId()] = [
                new Post('post-' . $blogId . '.1'),
                new Post('post-' . $blogId . '.2'),
            ];
        }

        return $posts;
    }

    /**
     * @param Blog[] $blogs
     *
     * @return Blog[][]
     */
    public static function prefetchSubBlogs(iterable $blogs): array
    {
        $subBlogs = [];
        foreach ($blogs as $blog) {
            $blogId = $blog->getId();
            $subBlogId = $blogId * 10;
            $subBlogs[$blog->id] = [new Blog($subBlogId)];
        }

        return $subBlogs;
    }

    /** @return callable(): User  */
    #[Field]
    public function author(): callable {
        return fn () => new User('Author', 'author@graphqlite');
    }
}
