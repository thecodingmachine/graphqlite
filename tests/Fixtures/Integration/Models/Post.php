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

    public function __construct(string $title, $dummy = null)
    {
        $this->title = $title;
        $this->description = 'bar';
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    private function setInaccessible(string $inaccessible): void
    {
        $this->inaccessible = $inaccessible;
    }
}
