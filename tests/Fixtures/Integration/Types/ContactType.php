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

    /**
     * @Field(prefetchMethod="prefetchContacts")
     */
    public function repeatName(Contact $contact, $data, string $suffix): string
    {
        $index = array_search($contact, $data['contacts'], true);
        return $data['prefix'].$data['contacts'][$index]->getName().$suffix;
    }

    public function prefetchContacts(iterable $contacts, string $prefix)
    {
        return [
            'contacts' => $contacts,
            'prefix' => $prefix
        ];
    }
}
