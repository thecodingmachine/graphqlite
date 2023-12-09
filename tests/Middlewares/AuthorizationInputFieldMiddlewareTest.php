<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use stdClass;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class AuthorizationInputFieldMiddlewareTest extends AbstractQueryProviderTest
{
    public function testReturnsResolversValueWhenAuthorized(): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('isLogged')
            ->willReturn(true);
        $authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $authorizationService->method('isAllowed')
            ->willReturn(true);
        $middleware = new AuthorizationInputFieldMiddleware($authenticationService, $authorizationService);

        $descriptor = $this
            ->stubDescriptor([new Logged(), new Right('test')])
            ->withResolver(fn () => 123);

        $field = $middleware->process($descriptor, $this->stubFieldHandler());

        self::assertNotNull($field);
        self::assertSame(123, $this->resolveField($field));
    }

    public function testHidesFieldForHideIfUnauthorized(): void
    {
        $middleware = new AuthorizationInputFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged(), new HideIfUnauthorized()]), $this->stubFieldHandler());

        self::assertNull($field);
    }

    public function testThrowsUnauthorizedExceptionWhenNotAuthorized(): void
    {
        $middleware = new AuthorizationInputFieldMiddleware(new VoidAuthenticationService(), new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged()]), $this->stubFieldHandler());

        self::assertNotNull($field);

        $this->expectExceptionObject(MissingAuthorizationException::unauthorized());

        $this->resolveField($field);
    }

    public function testThrowsForbiddenExceptionWhenNotAuthorized(): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('isLogged')
            ->willReturn(true);
        $middleware = new AuthorizationInputFieldMiddleware($authenticationService, new VoidAuthorizationService());

        $field = $middleware->process($this->stubDescriptor([new Logged(), new Right('test')]), $this->stubFieldHandler());

        self::assertNotNull($field);

        $this->expectExceptionObject(MissingAuthorizationException::forbidden());

        $this->resolveField($field);
    }

    /**
     * @param MiddlewareAnnotationInterface[] $annotations
     */
    private function stubDescriptor(array $annotations): InputFieldDescriptor
    {
        $resolver = fn () => self::fail('Should not be called.');

        return new InputFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
            middlewareAnnotations: new MiddlewareAnnotations($annotations),
        );
    }

    private function stubFieldHandler(): InputFieldHandlerInterface
    {
        return new class implements InputFieldHandlerInterface {
            public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
            {
                return new InputField(
                    name: $inputFieldDescriptor->getName(),
                    type: $inputFieldDescriptor->getType(),
                    arguments: [
                        'foo' => new SourceParameter(),
                    ],
                    originalResolver: $inputFieldDescriptor->getOriginalResolver(),
                    resolver: $inputFieldDescriptor->getResolver(),
                    forConstructorHydration: false,
                    comment: null,
                    isUpdate: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                );
            }
        };
    }
    
    private function resolveField(InputField $field): mixed
    {
        return $field->getResolve()(
            new stdClass(), [], null, $this->createStub(ResolveInfo::class),
        );
    }
}
