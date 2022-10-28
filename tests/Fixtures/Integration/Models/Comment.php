<?php declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Comment
{
    public function __construct(
        private string $text
    ) {
    }

    #[Field]
    public function getText(): string
    {
        return $this->text;
    }
}
