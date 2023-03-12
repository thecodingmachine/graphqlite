<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use Generator;
use phpDocumentor\Reflection\DocBlock;
use ReflectionMethod;
use stdClass;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Parameters\InjectUserParameter;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

class InjectUserParameterHandlerTest extends AbstractQueryProviderTest
{
    /**
     * @dataProvider mapParameterProvider
     */
    public function testMapParameter(bool $optional, string $method): void
    {
        $authenticationService = $this->createMock(AuthenticationServiceInterface::class);

        $refMethod = new ReflectionMethod(__CLASS__, $method);
        $parameter = $refMethod->getParameters()[0];

        $mapped = (new InjectUserParameterHandler($authenticationService))->mapParameter(
            $parameter,
            new DocBlock(),
            null,
            $this->getAnnotationReader()->getParameterAnnotationsPerParameter([$parameter])['user'],
            $this->createMock(ParameterHandlerInterface::class),
        );

        self::assertEquals(
            new InjectUserParameter($authenticationService, $optional),
            $mapped
        );
    }

    public function mapParameterProvider(): Generator
    {
        yield 'required user' => [false, 'requiredUser'];
        yield 'optional user' => [true, 'optionalUser'];
        yield 'missing type' => [true, 'missingType'];
    }

    private function requiredUser(
        #[InjectUser] stdClass $user,
    ) {
    }

    private function optionalUser(
        #[InjectUser] stdClass|null $user,
    ) {
    }

    private function missingType(
        #[InjectUser] $user,
    ) {
    }
}