<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Types;


use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;

class ContactFactory
{
    /**
     * @Factory()
     * @param string $name
     * @param Contact|null $manager
     * @param Contact[] $relations
     * @return Contact
     */
    public function createContact(string $name, ?Contact $manager = null, array $relations= []): Contact
    {
        $contact = new Contact($name);
        $contact->setManager($manager);
        $contact->setRelations($relations);
        return $contact;
    }
}
