<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\UndefinedDescription;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

/**
 * Input fields typed `T|Undefined` and `T|null|Undefined`, with and without a developer description, for each way
 * an input field is hydrated.
 */
#[Input]
class UndefinedDescriptionInput
{
    #[Field]
    public int|Undefined $property = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullableProperty = Undefined::VALUE;

    /** Age in years. */
    #[Field]
    public int|Undefined $describedProperty = Undefined::VALUE;

    /** Age in years. */
    #[Field]
    public int|null|Undefined $describedNullableProperty = Undefined::VALUE;

    #[Field(description: 'Age in years. May be omitted; null is not accepted.')]
    public int|Undefined $alreadyDescribedProperty = Undefined::VALUE;

    /** Age in years. */
    #[Field]
    public int|null $plainProperty = null;

    #[Field(inputType: 'Int!')]
    public int|Undefined $nonNullProperty = Undefined::VALUE;

    public function __construct(
        #[Field]
        public readonly int|Undefined $promotedParameter = Undefined::VALUE,
        #[Field]
        public readonly int|null|Undefined $nullablePromotedParameter = Undefined::VALUE,
        #[Field(description: 'Age in years.')]
        public readonly int|Undefined $describedPromotedParameter = Undefined::VALUE,
        #[Field(description: 'Age in years.')]
        public readonly int|null|Undefined $describedNullablePromotedParameter = Undefined::VALUE,
    )
    {
    }

    #[Field]
    public function setSetter(int|Undefined $setter): void
    {
    }

    #[Field]
    public function setNullableSetter(int|null|Undefined $nullableSetter): void
    {
    }

    /** Age in years. */
    #[Field]
    public function setDescribedSetter(int|Undefined $describedSetter): void
    {
    }

    /** Age in years. */
    #[Field]
    public function setDescribedNullableSetter(int|null|Undefined $describedNullableSetter): void
    {
    }

    #[Field(inputType: 'Int!')]
    public function setNonNullSetter(int|Undefined $nonNullSetter): void
    {
    }
}
