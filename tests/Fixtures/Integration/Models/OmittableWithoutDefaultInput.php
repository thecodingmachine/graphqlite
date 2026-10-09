<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

/**
 * Every way an input field can be hydrated, typed with `Undefined` but without a PHP default.
 */
#[Input]
class OmittableWithoutDefaultInput
{
    #[Field]
    public int|Undefined $property;

    #[Field]
    public int|null|Undefined $nullableProperty;

    /** @var int|null|Undefined */
    #[Field]
    public $docBlockProperty;

    #[Field]
    public int|null|Undefined $nullableConstructorParameter;

    #[Field]
    public int|null|Undefined $nullablePropertyWithSetter;

    private int|null|Undefined $nullableSetter = Undefined::VALUE;

    #[Field]
    public int|Undefined $assignedInConstructor;

    /** @var int|Undefined */
    #[Field]
    public $docBlockAssignedInConstructor;

    #[Field]
    public readonly int|Undefined $readonlyAssignedInConstructor;

    public function __construct(
        #[Field]
        public readonly int|null|Undefined $nullablePromotedParameter,
        int|null|Undefined $nullableConstructorParameter,
    )
    {
        $this->nullableConstructorParameter = $nullableConstructorParameter;
        $this->assignedInConstructor = 10;
        $this->docBlockAssignedInConstructor = 11;
        $this->readonlyAssignedInConstructor = Undefined::VALUE;
    }

    public function setNullablePropertyWithSetter(int|null|Undefined $value): void
    {
        $this->nullablePropertyWithSetter = $value;
    }

    #[Field]
    public function setNullableSetter(int|null|Undefined $nullableSetter): void
    {
        $this->nullableSetter = $nullableSetter;
    }

    public function getNullableSetter(): int|null|Undefined
    {
        return $this->nullableSetter;
    }
}
