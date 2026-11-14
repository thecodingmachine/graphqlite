<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\InputOutputNameConflict\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\InputOutputNameConflict\Types\InAndOut;

class InAndOutController
{
    #[Query]
    public function testInAndOut(
        #[UseInputType('InAndOut')]
        InAndOut $inAndOut,
    ): InAndOut
    {
        return $inAndOut;
    }
}
