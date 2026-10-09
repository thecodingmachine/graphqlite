<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\UndefinedDescription;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Undefined;

class UndefinedDescriptionController
{
    /**
     * @param int|Undefined $describedArgument Age in years.
     * @param int|null|Undefined $describedNullableArgument Age in years.
     */
    #[Query]
    public function undefinedDescriptionArguments(
        int|Undefined $argument,
        int|null|Undefined $nullableArgument,
        int|Undefined $describedArgument,
        int|null|Undefined $describedNullableArgument,
        int|null $plainArgument = null,
        #[UseInputType('Int!')]
        int|Undefined $nonNullArgument = Undefined::VALUE,
    ): bool
    {
        return true;
    }

    #[Mutation]
    public function updateUndefinedDescription(UndefinedDescriptionInput $input): bool
    {
        return true;
    }
}
