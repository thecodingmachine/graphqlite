<?php declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Blog
{
    public function __construct(
        private int $id,
    ) {
    }

    #[Field(outputType: 'ID!')]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param Post[][] $prefetchedPosts
     * @return Post[]
     */
    #[Field(prefetchMethod: 'prefetchPosts')]
    public function getPosts(array $prefetchedPosts): array
    {
        return $prefetchedPosts[$this->id] ?? [];
    }

    /**
     * @param Blog[][] $prefetchedSubBlogs
     * @return Blog[]
     */
    #[Field(prefetchMethod: 'prefetchSubBlogs')]
    public function getSubBlogs(array $prefetchedSubBlogs): array
    {
        return $prefetchedSubBlogs[$this->id] ?? [];
    }

    /**
     * @param self[] $blogs
     * @return Post[][]
     */
    public function prefetchPosts(array $blogs): array
    {
        $posts = [];
        foreach ($blogs as $blog) {
            $blogId = $blog->getId();
            $posts[$blogId][] = new Post("post-$blogId.1");
            $posts[$blogId][] = new Post("post-$blogId.2");
        }
        return $posts;
    }

    /**
     * @param self[] $blogs
     * @return Blog[][]
     */
    public function prefetchSubBlogs(array $blogs): array
    {
        $subBlogs = [];
        foreach ($blogs as $blog) {
            $blogId = $blog->getId();
            $subBlogId = $blogId * 10;
            $subBlogs[$blogId][] = new Blog($subBlogId);
        }
        return $subBlogs;
    }
}
