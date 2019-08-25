<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class AuthorizationFieldMiddlewareTest extends AbstractQueryProviderTest
{

    public function testException(): void
    {
        $middleware = new AuthorizationFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $descriptor = new QueryFieldDescriptor();
        $descriptor->setMiddlewareAnnotations($this->getAnnotationReader()->getMiddlewareAnnotations(new ReflectionMethod(__CLASS__, 'stub')));

        $this->expectException(IncompatibleAnnotationsException::class);
        $middleware->process($descriptor, new class implements FieldHandlerInterface {
            public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
            {
                return FieldDefinition::create(['name'=>'foo']);
            }
        });
    }

    /**
     * @Logged()
     * @HideIfUnauthorized()
     * @FailWith(null)
     */
    public function stub()
    {

    }
}
