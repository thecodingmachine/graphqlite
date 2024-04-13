<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use DateTimeInterface;
use TheCodingMachine\GraphQLite\Annotations\Cost;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
#[Input(name: 'PostInput', default: true)]
#[Input(name: 'UpdatePostInput', update: true)]
class Post
{
    #[Field(for: 'Post')]
    public int $id = 1;

    #[Field]
    public string $title;

    #[Field(for: ['Post', 'PostInput'])]
    #[Field(for: 'UpdatePostInput', inputType: 'DateTime')]
    public DateTimeInterface $publishedAt;

    #[Field(name: 'comment')]
    #[Cost(complexity: 5)]
    private string|null $description = 'foo';

    #[Field]
    public string|null $summary = 'foo';

    #[Field]
    #[Cost(complexity: 3)]
    public Contact|null $author = null;

    #[Field(for: 'UpdatePostInput')]
    public int $views;

    #[Field(for: 'UpdatePostInput')]
    private string|null $inaccessible;

    public function __construct(string $title, $dummy = null)
    {
        $this->title = $title;
        $this->description = 'bar';
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function setDescription(string|null $description): void
    {
        $this->description = $description;
    }

    public function setSummary(string|null $summary): void
    {
        $this->summary = $summary;
    }

    private function setInaccessible(string $inaccessible): void
    {
        $this->inaccessible = $inaccessible;
    }
}
