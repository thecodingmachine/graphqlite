<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

/**
 * Every way an input field can be hydrated, each typed `T|Undefined` (refuses null) and `T|null|Undefined`.
 *
 * The `*With*` fields declare a property whose nullability differs from the constructor parameter or setter
 * that receives the value; the receiver decides whether null is accepted. A PHP default other than Undefined
 * is still the GraphQL default.
 */
#[Input]
class OmittableInput
{
    #[Field]
    public int|Undefined $property = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullableProperty = Undefined::VALUE;

    #[Field]
    public int|Undefined $constructorParameter = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullableConstructorParameter = Undefined::VALUE;

    #[Field]
    public int|Undefined $propertyWithNullableConstructorParameter = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullablePropertyWithConstructorParameter = Undefined::VALUE;

    #[Field]
    public int|Undefined $propertyWithNullableSetter = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullablePropertyWithSetter = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullablePropertyWithNullDefault = null;

    private int|Undefined $setter = Undefined::VALUE;

    private int|null|Undefined $nullableSetter = Undefined::VALUE;

    /**
     * @param list<int>|Undefined $list
     * @param list<int>|Undefined|null $nullableList
     */
    public function __construct(
        #[Field]
        public readonly int|Undefined $promotedParameter = Undefined::VALUE,
        #[Field]
        public readonly int|null|Undefined $nullablePromotedParameter = Undefined::VALUE,
        #[Field]
        public readonly array|Undefined $list = Undefined::VALUE,
        #[Field]
        public readonly array|Undefined|null $nullableList = Undefined::VALUE,
        int|Undefined $constructorParameter = Undefined::VALUE,
        int|null|Undefined $nullableConstructorParameter = Undefined::VALUE,
        int|null|Undefined $propertyWithNullableConstructorParameter = Undefined::VALUE,
        int|Undefined $nullablePropertyWithConstructorParameter = Undefined::VALUE,
    )
    {
        $this->constructorParameter = $constructorParameter;
        $this->nullableConstructorParameter = $nullableConstructorParameter;
        $this->propertyWithNullableConstructorParameter = $propertyWithNullableConstructorParameter ?? 0;
        $this->nullablePropertyWithConstructorParameter = $nullablePropertyWithConstructorParameter;
    }

    public function setPropertyWithNullableSetter(int|null|Undefined $value): void
    {
        $this->propertyWithNullableSetter = $value ?? 0;
    }

    public function setNullablePropertyWithSetter(int|Undefined $value): void
    {
        $this->nullablePropertyWithSetter = $value;
    }

    #[Field]
    public function setSetter(int|Undefined $setter): void
    {
        $this->setter = $setter;
    }

    #[Field]
    public function setNullableSetter(int|null|Undefined $nullableSetter): void
    {
        $this->nullableSetter = $nullableSetter;
    }

    public function getSetter(): int|Undefined
    {
        return $this->setter;
    }

    public function getNullableSetter(): int|null|Undefined
    {
        return $this->nullableSetter;
    }
}
