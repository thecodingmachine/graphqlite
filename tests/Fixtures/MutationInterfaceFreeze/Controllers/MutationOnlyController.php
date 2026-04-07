<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\MutationInterfaceFreeze\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Fixtures\MutationInterfaceFreeze\Types\ConcreteResult;
use TheCodingMachine\GraphQLite\Fixtures\MutationInterfaceFreeze\Types\ResultInterface;

class MutationOnlyController
{
    #[Mutation]
    public function mutateResult(): ResultInterface
    {
        return new ConcreteResult('success');
    }
}
