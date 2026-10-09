<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\UnionType;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Fixtures\UnionOutputType;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Parameters\DefaultValueParameter;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\NullArgumentException;
use TheCodingMachine\GraphQLite\Undefined;

use function assert;
use function count;
use function reset;

class TypeMapperTest extends AbstractQueryProvider
{
    private int|Undefined $undefinedProperty = Undefined::VALUE;

    private int|null|Undefined $nullableUndefinedProperty = Undefined::VALUE;

    private int|null|Undefined $nullableUndefinedPropertyWithoutDefault;

    /** @var int|null|Undefined */
    private $docBlockUndefinedProperty;

    public function testMapScalarUnionException(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'dummy');
        $docBlockObj = $docBlockFactory->create($refMethod);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapperTest::dummy, in GraphQL, you can only use union types between objects. These types cannot be used in union types: String!, Int!');
        $typeMapper->mapReturnType($refMethod, $docBlockObj);
    }

    public function testMapObjectUnionWorks(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod(UnionOutputType::class, 'objectUnion');
        $docBlockObj = $docBlockFactory->create($refMethod);

        $gqType = $typeMapper->mapReturnType($refMethod, $docBlockObj);
        $this->assertInstanceOf(NonNull::class, $gqType);
        assert($gqType instanceof NonNull);
        $memberType = $gqType->getWrappedType();
        $this->assertInstanceOf(UnionType::class, $memberType);
        assert($memberType instanceof UnionType);
        $unionTypes = $memberType->getTypes();
        $this->assertEquals('TestObject', $unionTypes[0]->name);
        $this->assertEquals('TestObject2', $unionTypes[1]->name);
    }

    public function testMapObjectNullableUnionWorks(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod(UnionOutputType::class, 'nullableObjectUnion');
        $docBlockObj = $docBlockFactory->create($refMethod);

        $gqType = $typeMapper->mapReturnType($refMethod, $docBlockObj);
        $this->assertNotInstanceOf(NonNull::class, $gqType);
        assert(! ($gqType instanceof NonNull));
        $this->assertInstanceOf(UnionType::class, $gqType);
        assert($gqType instanceof UnionType);
        $unionTypes = $gqType->getTypes();
        $this->assertEquals(2, count($unionTypes));
        $this->assertEquals('TestObject', $unionTypes[0]->name);
        $this->assertEquals('TestObject2', $unionTypes[1]->name);
    }

    public function testMapUndefinedListParameterDoesNotCreateForbiddenUnion(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withUndefinedList');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $docBlockFactory->create($refMethod);
        $paramTags = $docBlockObj->getTagsByName('param');
        $paramTagType = reset($paramTags)->getType();
        $annotations = $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$refParameter])['foo'];

        $parameter = $typeMapper->mapParameter($refParameter, $docBlockObj, $paramTagType, $annotations);

        $this->assertInstanceOf(InputTypeParameter::class, $parameter);
        assert($parameter instanceof InputTypeParameter);
        // The reflection `array` and the phpdoc `list<string>` must resolve to a single `[String!]`,
        // not an illegal `array | list<string>` input union once `Undefined` is in the type.
        $this->assertSame('[String!]', $parameter->getType()->toString());
        // The `Undefined` default is the "optional field" marker: GraphQL prints no default for it.
        $this->assertFalse($parameter->hasDefaultValue());
        $this->assertNull($parameter->getDefaultValue());
    }

    public function testUndefinedParameterRefusesNullUnlessNullable(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withUndefined');
        $docBlockObj = $docBlockFactory->create($refMethod);
        $resolveInfo = $this->createMock(ResolveInfo::class);
        [$refParameter, $refNullableParameter] = $refMethod->getParameters();
        $annotations = $this->getAnnotationReader()->getParameterAnnotationsPerParameter($refMethod->getParameters());

        $nullableParameter = $typeMapper->mapParameter($refNullableParameter, $docBlockObj, null, $annotations['nullableFoo']);
        $this->assertNull($nullableParameter->resolve(null, ['nullableFoo' => null], null, $resolveInfo));
        $this->assertSame(Undefined::VALUE, $nullableParameter->resolve(null, [], null, $resolveInfo));

        $parameter = $typeMapper->mapParameter($refParameter, $docBlockObj, null, $annotations['foo']);
        $this->assertSame('Int', $parameter->getType()->toString());
        $this->assertSame(Undefined::VALUE, $parameter->resolve(null, [], null, $resolveInfo));

        $this->expectException(NullArgumentException::class);
        $this->expectExceptionMessage("Argument 'foo' may be omitted but cannot be null");
        $parameter->resolve(null, ['foo' => null], null, $resolveInfo);
    }

    public function testUndefinedInputPropertyRefusesNullUnlessNullable(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $resolveInfo = $this->createMock(ResolveInfo::class);

        $refNullableProperty = new ReflectionProperty($this, 'nullableUndefinedProperty');
        $nullableProperty = $typeMapper->mapInputProperty($refNullableProperty, $docBlockFactory->create($refNullableProperty), 'nullableUndefinedProperty');
        $this->assertNull($nullableProperty->resolve(null, ['nullableUndefinedProperty' => null], null, $resolveInfo));

        $refProperty = new ReflectionProperty($this, 'undefinedProperty');
        $property = $typeMapper->mapInputProperty($refProperty, $docBlockFactory->create($refProperty), 'undefinedProperty');
        $this->assertSame('Int', $property->getType()->toString());
        $this->assertSame(1, $property->resolve(null, ['undefinedProperty' => 1], null, $resolveInfo));

        $this->expectException(NullArgumentException::class);
        $this->expectExceptionMessage("Argument 'undefinedProperty' may be omitted but cannot be null");
        $property->resolve(null, ['undefinedProperty' => null], null, $resolveInfo);
    }

    public function testNullableUndefinedInputPropertyDefaultsToUndefined(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $resolveInfo = $this->createMock(ResolveInfo::class);

        foreach (['nullableUndefinedPropertyWithoutDefault', 'docBlockUndefinedProperty'] as $name) {
            $refProperty = new ReflectionProperty($this, $name);
            $property = $typeMapper->mapInputProperty($refProperty, $docBlockFactory->create($refProperty), $name);

            $this->assertSame('Int', $property->getType()->toString(), $name);
            // Undefined, not a printed `null` that GraphQL would fill in for an omitted field.
            $this->assertFalse($property->hasDefaultValue(), $name);
            $this->assertTrue($property->isDefaultValueUndefined(), $name);
            $this->assertSame(Undefined::VALUE, $property->resolve(null, [], null, $resolveInfo), $name);
            $this->assertNull($property->resolve(null, [$name => null], null, $resolveInfo), $name);
        }
    }

    public function testDocBlockUndefinedParameterDefaultsToUndefined(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withDocBlockUndefined');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $docBlockFactory->create($refMethod);
        $paramTags = $docBlockObj->getTagsByName('param');
        $annotations = $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$refParameter])['foo'];

        $parameter = $typeMapper->mapParameter($refParameter, $docBlockObj, reset($paramTags)->getType(), $annotations);
        assert($parameter instanceof InputTypeParameter);

        $this->assertSame('Int', $parameter->getType()->toString());
        $this->assertFalse($parameter->hasDefaultValue());
        $this->assertSame(Undefined::VALUE, $parameter->resolve(null, [], null, $this->createMock(ResolveInfo::class)));
    }

    public function testHideParameter(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withDefaultValue');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $docBlockFactory->create($refMethod);
        $annotations = $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$refParameter])['foo'];

        $param = $typeMapper->mapParameter($refParameter, $docBlockObj, null, $annotations);

        $this->assertInstanceOf(DefaultValueParameter::class, $param);

        $resolveInfo = $this->createMock(ResolveInfo::class);
        $this->assertSame(24, $param->resolve(null, [], null, $resolveInfo));
    }

    public function testParameterWithDescription(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withParamDescription');
        $docBlockObj = $docBlockFactory->create($refMethod);
        $refParameter = $refMethod->getParameters()[0];

        $parameter = $typeMapper->mapParameter($refParameter, $docBlockObj, null, $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$refParameter])['foo']);
        $this->assertInstanceOf(InputTypeParameter::class, $parameter);
        assert($parameter instanceof InputTypeParameter);
        $this->assertEquals('Foo parameter', $parameter->getDescription());
    }

    public function testHideParameterException(): void
    {
        $docBlockFactory = $this->getDocBlockFactory();

        $typeMapper = new TypeHandler(
            $this->getArgumentResolver(),
            $this->getRootTypeMapper(),
            $this->getTypeResolver(),
            $docBlockFactory,
        );

        $refMethod = new ReflectionMethod($this, 'withoutDefaultValue');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $docBlockFactory->create($refMethod);
        $annotations = $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$refParameter])['foo'];

        $this->expectException(CannotHideParameterRuntimeException::class);
        $this->expectExceptionMessage('For parameter $foo of method TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapperTest::withoutDefaultValue(), cannot use the @HideParameter annotation. The parameter needs to provide a default value.');

        $typeMapper->mapParameter($refParameter, $docBlockObj, null, $annotations);
    }

    private function dummy(): int|string
    {
    }

    /** @param list<string>|null $foo */
    private function withUndefinedList(array|Undefined|null $foo = Undefined::VALUE): void
    {
    }

    private function withUndefined(int|Undefined $foo, int|null|Undefined $nullableFoo): void
    {
    }

    /**
     * @param int|null|Undefined $foo
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private function withDocBlockUndefined($foo): void
    {
    }

    /** @param int $foo Foo parameter */
    private function withParamDescription(int $foo): void
    {
    }

    private function withDefaultValue(#[HideParameter]
    $foo = 24,): void
    {
    }

    private function withoutDefaultValue(#[HideParameter]
    $foo,): void
    {
    }
}
