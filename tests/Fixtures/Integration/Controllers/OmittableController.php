<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\OmittableInput;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\OmittableMagicSetterInput;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\OmittablePropertiesInput;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\OmittableValue;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\OmittableWithoutDefaultInput;
use TheCodingMachine\GraphQLite\Undefined;

use function array_map;
use function array_merge;
use function json_encode;

class OmittableController
{
    /**
     * @param list<int>|Undefined $list
     *
     * @return string[]
     */
    #[Query]
    public function omittableArguments(
        int|Undefined $argument,
        int|null|Undefined $nullableArgument,
        array|Undefined $list,
    ): array
    {
        return [
            'argument: ' . self::describe($argument),
            'nullableArgument: ' . self::describe($nullableArgument),
            'list: ' . self::describe($list),
        ];
    }

    /** @return string[] */
    #[Mutation]
    public function updateOmittable(OmittableInput $input): array
    {
        return [
            'property: ' . self::describe($input->property),
            'nullableProperty: ' . self::describe($input->nullableProperty),
            'setter: ' . self::describe($input->getSetter()),
            'nullableSetter: ' . self::describe($input->getNullableSetter()),
            'promotedParameter: ' . self::describe($input->promotedParameter),
            'nullablePromotedParameter: ' . self::describe($input->nullablePromotedParameter),
            'constructorParameter: ' . self::describe($input->constructorParameter),
            'nullableConstructorParameter: ' . self::describe($input->nullableConstructorParameter),
            'propertyWithNullableConstructorParameter: ' . self::describe($input->propertyWithNullableConstructorParameter),
            'nullablePropertyWithConstructorParameter: ' . self::describe($input->nullablePropertyWithConstructorParameter),
            'propertyWithNullableSetter: ' . self::describe($input->propertyWithNullableSetter),
            'nullablePropertyWithSetter: ' . self::describe($input->nullablePropertyWithSetter),
            'nullablePropertyWithNullDefault: ' . self::describe($input->nullablePropertyWithNullDefault),
            'list: ' . self::describe($input->list),
            'nullableList: ' . self::describe($input->nullableList),
        ];
    }

    /** @return string[] */
    #[Mutation]
    public function updateOmittableWithoutDefault(OmittableWithoutDefaultInput $input): array
    {
        return [
            'property: ' . self::describe($input->property),
            'nullableProperty: ' . self::describe($input->nullableProperty),
            'docBlockProperty: ' . self::describe($input->docBlockProperty),
            'nullablePromotedParameter: ' . self::describe($input->nullablePromotedParameter),
            'nullableConstructorParameter: ' . self::describe($input->nullableConstructorParameter),
            'nullablePropertyWithSetter: ' . self::describe($input->nullablePropertyWithSetter),
            'nullableSetter: ' . self::describe($input->getNullableSetter()),
            'assignedInConstructor: ' . self::describe($input->assignedInConstructor),
            'docBlockAssignedInConstructor: ' . self::describe($input->docBlockAssignedInConstructor),
            'readonlyAssignedInConstructor: ' . self::describe($input->readonlyAssignedInConstructor),
        ];
    }

    /** @return string[] */
    #[Mutation]
    public function updateOmittableProperties(OmittablePropertiesInput $input): array
    {
        return [
            'property: ' . self::describe($input->property),
            'nullableProperty: ' . self::describe($input->nullableProperty),
            'propertyWithoutDefault: ' . self::describe($input->propertyWithoutDefault),
            'nullablePropertyWithoutDefault: ' . self::describe($input->nullablePropertyWithoutDefault),
        ];
    }

    /** @return string[] */
    #[Mutation]
    public function updateOmittableMagicSetter(OmittableMagicSetterInput $input): array
    {
        return [
            'property: ' . self::describe($input->getProperty()),
            'nullableProperty: ' . self::describe($input->getNullableProperty()),
            'propertyWithoutDefault: ' . self::describe($input->getPropertyWithoutDefault()),
            'nullablePropertyWithoutDefault: ' . self::describe($input->getNullablePropertyWithoutDefault()),
        ];
    }

    /**
     * @param OmittableInput[] $inputs
     *
     * @return string[]
     */
    #[Mutation]
    public function updateOmittableList(array $inputs): array
    {
        return array_merge(...array_map($this->updateOmittable(...), $inputs));
    }

    /** @return string[] */
    #[Query]
    public function omittableFactory(OmittableValue $value): array
    {
        return [
            'value: ' . self::describe($value->value),
            'nullableValue: ' . self::describe($value->nullableValue),
        ];
    }

    private static function describe(mixed $value): string
    {
        return $value === Undefined::VALUE ? 'undefined' : json_encode($value);
    }
}
