<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

class ContactFactory
{
    /** @param Contact[] $relations */
    #[Factory]
    public function createContact(
        string $name,
        DateTimeInterface $birthDate,
        UploadedFileInterface|null $photo = null,
        Contact|null $manager = null,
        #[UseInputType(inputType: '[ContactRef!]!')]
        array $relations = [],
    ): Contact
    {
        $contact = new Contact($name);
        if ($photo) {
            $contact->setPhoto($photo);
        }
        $contact->setBirthDate($birthDate);
        $contact->setManager($manager);
        $contact->setRelations($relations);
        return $contact;
    }

    #[Factory(name: 'ContactRef', default: false)]
    public function getContact(string $name): Contact
    {
        return new Contact($name);
    }
}
