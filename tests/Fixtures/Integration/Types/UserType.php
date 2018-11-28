<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\User;

/**
 * @Type(class=User::class)
 * @SourceField(name="email")
 */
class UserType
{

}