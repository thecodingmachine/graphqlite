<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use DateTimeInterface;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 * @Input()
 * @Input(name="UpdatePostInput", update=true)
 */
class Post
{

    /**
     * @Field(for="Post")
     * @var int
     */
    public int $id = 1;

    /**
     * @Field()
     * @var string
     */
    public string $title;

    /**
     * @Field(for={"Post", "PostInput"})
     * @Field(for="PostUpdateInput", inputType="DateTime")
     * @var DateTimeInterface
     */
    public DateTimeInterface $publishedAt;

    /**
     * @Field()
     * @var string|null
     */
    private ?string $description = 'foo';

    /**
     * @Field()
     * @var string|null
     */
    public ?string $summary = 'foo';

    /**
     * @Field()
     * @var Contact|null
     */
    public ?Contact $author = null;

    /**
     * @Field(for="UpdatePostInput")
     * @var string|null
     */
    private ?string $inaccessible;

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
     * @param string $inaccessible
     */
    private function setInaccessible(string $inaccessible): void
    {
        $this->inaccessible = $inaccessible;
    }
}
