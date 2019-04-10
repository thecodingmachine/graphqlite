<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

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
     * Here, we are testing overriding the field in the extend class.
     *
     * @Field()
     * @return string
     */
    public function company(Contact $contact): string
    {
        return $contact->getName().' Ltd';
    }
}
