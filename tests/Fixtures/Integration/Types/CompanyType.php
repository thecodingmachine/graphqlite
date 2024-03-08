<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Company;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

/**
 * @ExtendType(class=Company::class)
 */
class CompanyType
{
   /**
     * @Field()
     */
    public function getName(Company $company): string
    {
        return $company->name;
    }

    /**
     * @Field()
     */
    public function getContact(
        Company $company,
        #[Prefetch('prefetchContacts')]
        array $contacts
    ): ?Contact {
        return $contacts[$company->name] ?? null;
    }

    public static function prefetchContacts(array $companies): array
    {
        $contacts = [];

        foreach ($companies as $company) {
             $contacts[$company->name] = new Contact('Kate');
        }

        return $contacts;
    }
}
