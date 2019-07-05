<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use function array_search;
use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

/**
 * A test type that is not a default type.
 *
 * @Type(class=Contact::class, name="ContactOther", default=false)
 */
class ContactOtherType
{
    /**
     * @Field()
     */
    public function fullName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }
}
