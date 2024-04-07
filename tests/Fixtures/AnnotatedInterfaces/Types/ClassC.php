<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;

class ClassC extends ClassB implements WizzInterface
{
    #[Field]
    public function getWizz(): string
    {
        return 'wizz';
    }
}
