<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use DateTimeInterface;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 * @Input(name="PostInput", default=true)
 * @Input(name="UpdatePostInput", update=true)
 */
class Post
{

    /**
     * @Field(for="Post")
     * @var int
     */
    public $id = 1;

    /**
     * @Field()
     * @var string
     */
    public $title;

    /**
     * @Field(for={"Post", "PostInput"})
     * @Field(for="UpdatePostInput", inputType="DateTime")
     * @var DateTimeInterface
     */
    public $publishedAt;

    /**
     * @Field(name="comment")
     * @var string|null
     */
    private $description = 'foo';

    /**
     * @Field()
     * @var string|null
     */
    public $summary = 'foo';

    /**
     * @Field()
     * @var Contact|null
     */
    public $author = null;

    /**
     * @Field(for="UpdatePostInput")
     * @var int
     */
    public $views;

    /**
     * @Field(for="UpdatePostInput")
     * @var string|null
     */
    private $inaccessible;

    /**
     * @param string $title
     */
    public function __construct(string $title, $dummy = null)
    {
        $this->title = $title;
        $this->description = 'bar';
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string|null $summary
     */
    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * @param Comment[][] $prefetchedComments
     * @return Comment[]
     */
    #[Field(prefetchMethod: 'prefetchComments')]
    public function getComments(array $prefetchedComments): array
    {
        return $prefetchedComments[$this->title] ?? [];
    }

    /**
     * @param self[] $posts
     * @return Comment[][]
     */
    public function prefetchComments(array $posts): array
    {
        $comments = [];
        foreach ($posts as $post) {
            $comments[$post->title][] = new Comment("comment for $post->title");
        }
        return $comments;
    }

    /**
     * @param string $inaccessible
     */
    private function setInaccessible(string $inaccessible): void
    {
        $this->inaccessible = $inaccessible;
    }
}
