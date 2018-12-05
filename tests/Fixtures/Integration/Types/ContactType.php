<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Types;

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

}