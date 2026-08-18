<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\EnumExposure;

use TheCodingMachine\GraphQLite\Annotations\Query;

class EnumExposureController
{
    #[Query]
    public function publishStatus(): PublishStatus
    {
        return PublishStatus::Published;
    }
}
