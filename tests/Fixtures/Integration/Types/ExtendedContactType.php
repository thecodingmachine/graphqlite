<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

use function strtoupper;

/**
 * @ExtendType(class=Contact::class)
 */
class ExtendedContactType
{
    /**
     * @Field()
     */
    public function uppercaseName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }

    /**
     * @Field()
     *
     * @deprecated use field `uppercaseName`
     */
    public function deprecatedUppercaseName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }

    /**
     * Here, we are testing overriding the field in the extend class.
     *
     * @Field()
     */
    public function company(Contact $contact): string
    {
        return $contact->getName() . ' Ltd';
    }
}
