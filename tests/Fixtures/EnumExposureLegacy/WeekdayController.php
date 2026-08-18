<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\EnumExposureLegacy;

use TheCodingMachine\GraphQLite\Annotations\Query;

class WeekdayController
{
    #[Query]
    public function weekday(): Weekday
    {
        return Weekday::Monday;
    }
}
