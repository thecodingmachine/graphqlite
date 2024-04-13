<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

use function strtoupper;

/**
 * A test type that is not a default type.
 */
#[Type(class:  Contact::class, name: 'ContactOther', default: false)]
class ContactOtherType
{
    #[Field]
    public function fullName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }
}
