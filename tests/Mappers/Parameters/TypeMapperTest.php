<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use ReflectionMethod;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;

class TypeMapperTest extends AbstractQueryProviderTest
{

    public function testMapScalarUnionException()
    {
        $typeMapper = new TypeMapper($this->getTypeMapper(), $this->getArgumentResolver(), new CompositeRootTypeMapper([
            new MyCLabsEnumTypeMapper(),
            new BaseTypeMapper($this->getTypeMapper())
        ]), $this->getTypeResolver());

        $cachedDocBlockFactory = new CachedDocBlockFactory(new ArrayCache());

        $refMethod = new ReflectionMethod($this, 'dummy');
        $docBlockObj = $cachedDocBlockFactory->getDocBlock($refMethod);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('In GraphQL, you can only use union types between objects. These types cannot be used in union types: Int, String');
        $typeMapper->mapReturnType($refMethod, $docBlockObj);
    }

    /**
     * @return int|string
     */
    private function dummy() {

    }
}
