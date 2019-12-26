<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;


use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

class ContactFactory
{
    /**
     * @Factory()
     * @UseInputType(for="$relations", inputType="[ContactRef!]!")
     * @param string $name
     * @param Contact|null $manager
     * @param Contact[] $relations
     * @return Contact
     */
    public function createContact(string $name, DateTimeInterface $birthDate, ?UploadedFileInterface $photo = null, ?Contact $manager = null, array $relations= []): Contact
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

    /**
     * @Factory(name="ContactRef", default=false)
     * @return Contact
     */
    public function getContact(string $name): Contact
    {
        $contact = new Contact($name);
        return $contact;
    }
}
