<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use function array_search;
use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;

/**
 * @ExtendType(class=Contact::class)
 * @SourceField(name="name", phpType="string")
 * @SourceField(name="birthDate")
 * @SourceField(name="manager")
 * @SourceField(name="relations")
 * @SourceField(name="injectServiceFromExternal", annotations={@Autowire(for="testService", identifier="testService"), @HideParameter(for="testSkip"), @UseInputType(for="$id", inputType="String")})
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
        if ($index === false) {
            throw new \RuntimeException('Index not found');
        }
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
