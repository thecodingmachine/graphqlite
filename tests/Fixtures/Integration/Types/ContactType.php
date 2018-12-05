<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Types;

use function strtoupper;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;

/**
 * @Type(class=Contact::class)
 * @SourceField(name="name")
 * @SourceField(name="manager")
 */
class ContactType
{
    /**
     * @Field()
     */
    public function customField(Contact $contact, string $prefix): string
    {
        return $prefix.' '.strtoupper($contact->getName());
    }
}