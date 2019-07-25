<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use TheCodingMachine\GraphQLite\FactoryContext;
use TheCodingMachine\GraphQLite\InputTypeUtils;

/**
 * A type mapper that is passed the list of classes that it must scan (unlike the GlobTypeMapper that find those automatically).
 */
final class StaticClassListTypeMapperFactory implements TypeMapperFactoryInterface
{
    /** @var array<int, string> The list of classes to be scanned. */
    private $classList;

    /**
     * StaticClassListTypeMapperFactory constructor.
     *
     * @param array<int, string> $classList
     */
    public function __construct(
        array $classList
    ) {
        $this->classList           = $classList;
    }

    public function create(FactoryContext $context): TypeMapperInterface
    {
        $inputTypeUtils = new InputTypeUtils($context->getAnnotationReader(), $context->getNamingStrategy());

        return new StaticClassListTypeMapper(
            $this->classList,
            $context->getTypeGenerator(),
            $context->getInputTypeGenerator(),
            $inputTypeUtils,
            $context->getContainer(),
            $context->getAnnotationReader(),
            $context->getNamingStrategy(),
            $context->getRecursiveTypeMapper(),
            $context->getCache(),
            $context->getGlobTtl(),
            $context->getMapTtl()
        );
    }
}
