<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class AuthorizationFieldMiddlewareTest extends AbstractQueryProviderTest
{
    public function testReturnsResolversValueWhenAuthorized(): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('isLogged')
            ->willReturn(true);
        $authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $authorizationService->method('isAllowed')
            ->willReturn(true);
        $middleware = new AuthorizationFieldMiddleware($authenticationService, $authorizationService);

        $descriptor = $this
            ->stubDescriptor([new Logged(), new Right('test')])
            ->withResolver(fn () => 123);

        $field = $middleware->process($descriptor, $this->stubFieldHandler());

        self::assertNotNull($field);
        self::assertSame(123, ($field->resolveFn)());
    }


    public function testFailsForHideIfUnauthorizedAndFailWith(): void
    {
        $middleware = new AuthorizationFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $this->expectException(IncompatibleAnnotationsException::class);
        $middleware->process($this->stubDescriptor([new Logged(), new HideIfUnauthorized(), new FailWith(value: 123)]), $this->stubFieldHandler());
    }

    public function testHidesFieldForHideIfUnauthorized(): void
    {
        $middleware = new AuthorizationFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged(), new HideIfUnauthorized()]), $this->stubFieldHandler());

        self::assertNull($field);
    }

    public function testReturnsFailsWithValueWhenNotAuthorized(): void
    {
        $middleware = new AuthorizationFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged(), new FailWith(value: 123)]), $this->stubFieldHandler());

        self::assertNotNull($field);
        self::assertSame(123, ($field->resolveFn)());
    }

    public function testThrowsUnauthorizedExceptionWhenNotAuthorized(): void
    {
        $middleware = new AuthorizationFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged()]), $this->stubFieldHandler());

        self::assertNotNull($field);

        $this->expectExceptionObject(MissingAuthorizationException::unauthorized());

        ($field->resolveFn)();
    }

    public function testThrowsForbiddenExceptionWhenNotAuthorized(): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('isLogged')
            ->willReturn(true);
        $middleware = new AuthorizationFieldMiddleware($authenticationService, new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged(), new Right('test')]), $this->stubFieldHandler());

        self::assertNotNull($field);

        $this->expectExceptionObject(MissingAuthorizationException::forbidden());

        ($field->resolveFn)();
    }

    /**
     * @param MiddlewareAnnotationInterface[] $annotations
     */
    private function stubDescriptor(array $annotations): QueryFieldDescriptor
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            middlewareAnnotations: new MiddlewareAnnotations($annotations),
        );
        $descriptor = $descriptor->withResolver(fn () => self::fail('Should not be called.'));

        return $descriptor;
    }

    private function stubFieldHandler(): FieldHandlerInterface
    {
        return new class implements FieldHandlerInterface {
            public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
            {
                return new FieldDefinition([
                    'name' => $fieldDescriptor->getName(),
                    'resolve' => $fieldDescriptor->getResolver(),
                ]);
            }
        };
    }
}
