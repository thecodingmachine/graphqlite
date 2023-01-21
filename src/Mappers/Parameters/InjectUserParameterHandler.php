<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\InjectUserParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

/**
 * Maps the current user to a parameter targetted by the \@InjectUser annotation.
 */
class InjectUserParameterHandler implements ParameterMiddlewareInterface
{
    public function __construct(private readonly AuthenticationServiceInterface $authenticationService)
    {
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, Type|null $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        $injectUser = $parameterAnnotations->getAnnotationByType(InjectUser::class);

        if ($injectUser === null) {
            return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        return new InjectUserParameter($this->authenticationService);
    }
}
