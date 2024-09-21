<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Company;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

#[ExtendType(class:Company::class)]
class CompanyType
{
    #[Field]
    public function getName(Company $company): string
    {
        return $company->name;
    }

    /** @param Contact[] $contacts */
    #[Field]
    public function getContact(
        Company $company,
        #[Prefetch('prefetchContacts')]
        array $contacts,
    ): Contact|null {
        return $contacts[$company->name] ?? null;
    }

    /**
     * @param Company[] $companies
     *
     * @return Contact[]
     */
    public static function prefetchContacts(array $companies): array
    {
        $contacts = [];

        foreach ($companies as $company) {
             $contacts[$company->name] = new Contact('Kate');
        }

        return $contacts;
    }

    #[Field]
    public function getContactRequested(
        Company $company,
        #[Prefetch('prefetchContactsExact', true)]
        Contact $contact,
    ): Contact|null {
        return $contact;
    }

    /**
     * @param Company[] $companies
     *
     * @return Contact[]
     */
    public static function prefetchContactsExact(array $companies): array
    {
        $contacts = [];

        foreach ($companies as $key => $company) {
            $contacts[$key] = new Contact('Kate');
        }

        return $contacts;
    }
}
