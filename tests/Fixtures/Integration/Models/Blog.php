<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

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
     * @param Post[] $prefetchedPosts
     *
     * @return Post[]
     */
    #[Field]
    public function getPosts(
        #[Prefetch('prefetchPosts', true)]
        array $prefetchedPosts,
    ): array {
        return $prefetchedPosts;
    }

    /**
     * @param Blog[] $prefetchedSubBlogs
     *
     * @return Blog[]
     */
    #[Field]
    public function getSubBlogs(
        #[Prefetch('prefetchSubBlogs', true)]
        array $prefetchedSubBlogs,
    ): array {
        return $prefetchedSubBlogs;
    }

    /**
     * @param Blog[] $blogs
     *
     * @return Post[][]
     */
    public static function prefetchPosts(iterable $blogs): array
    {
        $posts = [];
        foreach ($blogs as $key => $blog) {
            $blogId = $blog->getId();
            $posts[$key] = [
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
        foreach ($blogs as $key => $blog) {
            $blogId = $blog->getId();
            $subBlogId = $blogId * 10;
            $subBlogs[$key] = [new Blog($subBlogId)];
        }

        return $subBlogs;
    }
}
