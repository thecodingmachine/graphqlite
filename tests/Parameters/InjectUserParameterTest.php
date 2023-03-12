<?php

namespace TheCodingMachine\GraphQLite\Parameters;

use Generator;
use GraphQL\Type\Definition\ResolveInfo;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

class InjectUserParameterTest extends TestCase
{
    /**
     * @dataProvider resolveReturnsUserProvider
     */
    public function testResolveReturnsUser(stdClass|null $user, bool $optional): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('getUser')
            ->willReturn($user);

        $resolved = (new InjectUserParameter($authenticationService, $optional))->resolve(
            null,
            [],
            null,
            $this->createStub(ResolveInfo::class)
        );

        self::assertSame($user, $resolved);
    }

    public function resolveReturnsUserProvider(): Generator
    {
        yield 'non optional and has user' => [new stdClass(), false];
        yield 'optional and has user' => [new stdClass(), true];
        yield 'optional and doesnt have user' => [null, true];
    }

    public function testThrowsMissingAuthorization(): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $authenticationService->method('getUser')
            ->willReturn(null);

        $this->expectExceptionObject(MissingAuthorizationException::unauthorized());

        (new InjectUserParameter($authenticationService, false))->resolve(
            null,
            [],
            null,
            $this->createStub(ResolveInfo::class)
        );
    }
}