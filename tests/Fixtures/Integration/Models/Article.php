<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 * @Input()
 */
class Article extends Post
{
    /**
     * @Field(for="Article")
     * @var int
     */
    public $id = 2;

    /**
     * @Field()
     * @var string|null
     */
    public $magazine = null;
}
