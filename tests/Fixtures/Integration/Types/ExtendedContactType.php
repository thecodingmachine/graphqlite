<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

use function strtoupper;

#[ExtendType(class: Contact::class)]
class ExtendedContactType
{
    #[Field]
    public function uppercaseName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }

    /** @deprecated use field `uppercaseName` */
    #[Field]
    public function deprecatedUppercaseName(Contact $contact): string
    {
        return strtoupper($contact->getName());
    }

    /**
     * Here, we are testing overriding the field in the extend class.
     */
    #[Field]
    public function company(Contact $contact): string
    {
            return $contact->getName() . ' Ltd';
    }

    /**
     * Regression: #[Security] on an ExtendType field used to blow up with
     * "array_combine(): ... must have the same number of elements" because
     * SecurityFieldMiddleware didn't mirror the source-injection that
     * QueryField::fromFieldDescriptor performs.
     */
    #[Field]
    #[Security("user && user.bar == 42", failWith: null)]
    public function extendedSecretName(Contact $contact): string|null
    {
        return $contact->getName();
    }

    /**
     * Regression: in #[Security] on an #[ExtendType] field, `this` must be
     * the source object, not the resolver instance.
     */
    #[Field]
    #[Security("user && this.getName() == 'Joe'", failWith: null)]
    public function sourceAwareSecretName(Contact $contact): string|null
    {
        return $contact->getName();
    }
}
