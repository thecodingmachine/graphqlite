<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use Porpaginas\Result;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Subscription;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\User;

class ContactController
{
    /**
     * @return Contact[]
     */
    #[Query]
    public function getContacts(): array
    {
        return [
            new Contact('Joe'),
            new User('Bill', 'bill@example.com'),
        ];
    }

    #[Query]
    public function getContact(string $name): ?Contact
    {
        return match( $name ) {
            'Joe' => new Contact('Joe'),
            'Bill' => new Contact('Bill'),
            default => null,
        };
    }

    #[Mutation]
    public function saveContact(Contact $contact): Contact
    {
        return $contact;
    }

    #[Mutation]
    public function saveBirthDate(\DateTimeInterface $birthDate): Contact {
        $contact = new Contact('Bill');
        $contact->setBirthDate($birthDate);

        return $contact;
    }

    /**
     * @return Contact[]
     */
    #[Query]
    public function getContactsIterator(): ArrayResult
    {
        return new ArrayResult([
            new Contact('Joe'),
            new User('Bill', 'bill@example.com'),
        ]);
    }

    /**
     * @return string[]|ArrayResult
     */
    #[Query]
    public function getContactsNamesIterator(): ArrayResult
    {
        return new ArrayResult([
            'Joe',
            'Bill',
        ]);
    }

    #[Query(outputType: 'ContactOther')]
    public function getOtherContact(): Contact
    {
        return new Contact('Joe');
    }

    /**
     * Test that we can have nullable results from Porpaginas.
     *
     * @return Result|Contact[]|null
     */
    #[Query]
    public function getNullableResult(): ?Result
    {
        return null;
    }

    #[Subscription]
    public function contactAdded(): Contact
    {
        return new Contact('Joe');
    }

    #[Subscription(outputType: 'Contact')]
    public function contactAddedWithFilter(Contact $contact): void
    {
        // Save the subscription somewhere
    }
}
