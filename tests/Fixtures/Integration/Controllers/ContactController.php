<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\User;

class ContactController
{
    /**
     * @Query()
     * @return Contact[]
     */
    public function getContacts(): array
    {
        return [
            new Contact('Joe'),
            new User('Bill', 'bill@example.com'),
        ];
    }

    /**
     * @Mutation()
     * @param Contact $contact
     * @return Contact
     */
    public function saveContact(Contact $contact): Contact
    {
        return $contact;
    }

    /**
     * @Mutation()
     * @param \DateTimeInterface $birthDate
     * @return Contact
     */
    public function saveBirthDate(\DateTimeInterface $birthDate): Contact {
        $contact = new Contact('Bill');
        $contact->setBirthDate($birthDate);

        return $contact;
    }

    /**
     * @Query()
     * @return Contact[]
     */
    public function getContactsIterator(): ArrayResult
    {
        return new ArrayResult([
            new Contact('Joe'),
            new User('Bill', 'bill@example.com'),
        ]);
    }

    /**
     * @Query()
     * @return string[]|ArrayResult
     */
    public function getContactsNamesIterator(): ArrayResult
    {
        return new ArrayResult([
            'Joe',
            'Bill',
        ]);
    }

    /**
     * @Query(outputType="ContactOther")
     */
    public function getOtherContact(): Contact
    {
        return new Contact('Joe');
    }
}
