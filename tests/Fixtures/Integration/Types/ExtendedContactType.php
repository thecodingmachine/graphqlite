<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Types;

use function strtoupper;
use TheCodingMachine\GraphQL\Controllers\Annotations\ExtendType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;

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
}