<?php


namespace TheCodingMachine\GraphQLite\Fixtures;


use MyCLabs\Enum\Enum;

/**
 * @method static TestEnum ON()
 * @method static TestEnum OFF()
 */
class TestEnum extends Enum
{
    private const ON = 'on';
    private const OFF = 'off';
}
