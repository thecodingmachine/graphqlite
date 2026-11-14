<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

enum TestEnum: string
{
    case ON = 'on';
    case OFF = 'off';
}
