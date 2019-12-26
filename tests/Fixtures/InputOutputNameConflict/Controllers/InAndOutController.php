<?php


namespace TheCodingMachine\GraphQLite\Fixtures\InputOutputNameConflict\Controllers;


use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\InputOutputNameConflict\Types\InAndOut;

class InAndOutController
{
    /**
     * @Query()
     * @UseInputType(for="$inAndOut", inputType="InAndOut")
     */
    public function testInAndOut(InAndOut $inAndOut): InAndOut
    {
        return $inAndOut;
    }
}
