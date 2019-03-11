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
 * @SourceField(name="name")
 * @SourceField(name="birthDate")
 * @SourceField(name="manager")
 * @SourceField(name="relations")
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