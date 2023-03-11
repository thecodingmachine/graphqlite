<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

/**
 * Just a wrapper Exception class to allow us to confirm an Exception thrown, is from validation.
 *
 * @author Jacob Thomason <jacob@thomason.xxx>
 */
class ValidationException extends GraphQLException
{
}
