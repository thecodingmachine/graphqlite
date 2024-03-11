<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Company;

class CompanyController
{
    #[Query]
    public function getCompany(string $id): Company
    {
        return new Company('Company');
    }
}
