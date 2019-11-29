<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use DateTimeImmutable;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Parameters\DefaultValueParameter;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;

class TypeMapperTest extends AbstractQueryProviderTest
{

    public function testMapScalarUnionException(): void
    {
        $typeMapper = new TypeHandler($this->getArgumentResolver(), $this->getRootTypeMapper(), $this->getTypeResolver());

        $cachedDocBlockFactory = new CachedDocBlockFactory(new Psr16Cache(new ArrayAdapter()));

        $refMethod = new ReflectionMethod($this, 'dummy');
        $docBlockObj = $cachedDocBlockFactory->getDocBlock($refMethod);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapperTest::dummy, in GraphQL, you can only use union types between objects. These types cannot be used in union types: Int!, String!');
        $typeMapper->mapReturnType($refMethod, $docBlockObj);
    }

    public function testHideParameter(): void
    {
        $typeMapper = new TypeHandler($this->getArgumentResolver(), $this->getRootTypeMapper(), $this->getTypeResolver());

        $cachedDocBlockFactory = new CachedDocBlockFactory(new Psr16Cache(new ArrayAdapter()));

        $refMethod = new ReflectionMethod($this, 'withDefaultValue');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $cachedDocBlockFactory->getDocBlock($refMethod);
        $annotations = $this->getAnnotationReader()->getParameterAnnotations($refParameter);

        $param = $typeMapper->mapParameter($refParameter, $docBlockObj, null, $annotations);

        $this->assertInstanceOf(DefaultValueParameter::class, $param);

        $resolveInfo = $this->createMock(ResolveInfo::class);
        $this->assertSame(24, $param->resolve(null, [], null, $resolveInfo));
    }

    public function testHideParameterException(): void
    {
        $typeMapper = new TypeHandler($this->getArgumentResolver(), $this->getRootTypeMapper(), $this->getTypeResolver());

        $cachedDocBlockFactory = new CachedDocBlockFactory(new Psr16Cache(new ArrayAdapter()));

        $refMethod = new ReflectionMethod($this, 'withoutDefaultValue');
        $refParameter = $refMethod->getParameters()[0];
        $docBlockObj = $cachedDocBlockFactory->getDocBlock($refMethod);
        $annotations = $this->getAnnotationReader()->getParameterAnnotations($refParameter);

        $this->expectException(CannotHideParameterRuntimeException::class);
        $this->expectExceptionMessage('For parameter $foo of method TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapperTest::withoutDefaultValue(), cannot use the @HideParameter annotation. The parameter needs to provide a default value.');

        $typeMapper->mapParameter($refParameter, $docBlockObj, null, $annotations);
    }

    /**
     * @return int|string
     */
    private function dummy() {

    }

    /**
     * @HideParameter(for="$foo")
     */
    private function withDefaultValue($foo = 24) {

    }

    /**
     * @HideParameter(for="$foo")
     */
    private function withoutDefaultValue($foo) {

    }
}
