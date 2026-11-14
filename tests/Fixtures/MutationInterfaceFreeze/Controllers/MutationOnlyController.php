<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\MutationInterfaceFreeze\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Tests\Fixtures\MutationInterfaceFreeze\Types\ConcreteResult;
use TheCodingMachine\GraphQLite\Tests\Fixtures\MutationInterfaceFreeze\Types\ResultInterface;

class MutationOnlyController
{
    #[Mutation]
    public function mutateResult(): ResultInterface
    {
        return new ConcreteResult('success');
    }
}
