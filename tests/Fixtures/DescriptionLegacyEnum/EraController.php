<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionLegacyEnum;

use TheCodingMachine\GraphQLite\Annotations\Query;

class EraController
{
    #[Query]
    public function era(): Era
    {
        return Era::Modern;
    }
}
