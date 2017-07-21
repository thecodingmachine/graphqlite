<?php

namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestController;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;

class ControllerQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $typeMapper;
    private $hydrator;

    private function getTestObjectType()
    {
        if ($this->testObjectType === null) {
            $this->testObjectType = new ObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => new StringType(),
                ],
            ]);
        }
        return $this->testObjectType;
    }

    private function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $this->typeMapper = new class($this->getTestObjectType()) implements TypeMapperInterface {
                /**
                 * @var ObjectType
                 */
                private $testObjectType;

                public function __construct(ObjectType $testObjectType)
                {
                    $this->testObjectType = $testObjectType;
                }

                public function mapClassToType(string $className): TypeInterface
                {
                    if ($className === TestObject::class) {
                        return $this->testObjectType;
                    } else {
                        throw new \RuntimeException('Unexpected type');
                    }
                }
            };
        }
        return $this->typeMapper;
    }

    private function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = new class implements HydratorInterface {
                public function hydrate(array $data, TypeInterface $type)
                {
                    return new TestObject($data['test']);
                }
            };
        }
        return $this->hydrator;
    }

    public function testQueryProvider()
    {
        $controller = new TestController();
        $reader = new AnnotationReader();

        $queryProvider = new ControllerQueryProvider($controller, $reader, $this->getTypeMapper(), $this->getHydrator(), new VoidAuthenticationService(), new VoidAuthorizationService());

        $queries = $queryProvider->getQueries();

        $this->assertCount(1, $queries);
        $usersQuery = $queries[0];
        $this->assertSame('test', $usersQuery->getName());

        $this->assertCount(3, $usersQuery->getArguments());
        $this->assertInstanceOf(NonNullType::class, $usersQuery->getArgument('int')->getType());
        $this->assertInstanceOf(IntType::class, $usersQuery->getArgument('int')->getType()->getTypeOf());
        $this->assertInstanceOf(StringType::class, $usersQuery->getArgument('string')->getType());
        $this->assertInstanceOf(NonNullType::class, $usersQuery->getArgument('list')->getType());
        $this->assertInstanceOf(ListType::class, $usersQuery->getArgument('list')->getType()->getTypeOf());
        $this->assertInstanceOf(NonNullType::class, $usersQuery->getArgument('list')->getType()->getTypeOf()->getItemType());
        $this->assertInstanceOf(ObjectType::class, $usersQuery->getArgument('list')->getType()->getTypeOf()->getItemType()->getTypeOf());
        $this->assertSame('TestObject', $usersQuery->getArgument('list')->getType()->getTypeOf()->getItemType()->getTypeOf()->getName());

        $mockResolveInfo = $this->createMock(ResolveInfo::class);

        $result = $usersQuery->resolve('foo', ['int'=>42, 'string'=>'foo', 'list'=>[
            ['test'=>42],
            ['test'=>12],
        ]], $mockResolveInfo);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('foo424212', $result->getTest());
    }

    public function testMutations()
    {
        $controller = new TestController();
        $reader = new AnnotationReader();

        $queryProvider = new ControllerQueryProvider($controller, $reader, $this->getTypeMapper(), $this->getHydrator(), new VoidAuthenticationService(), new VoidAuthorizationService());

        $mutations = $queryProvider->getMutations();

        $this->assertCount(1, $mutations);
        $mutation = $mutations[0];
        $this->assertSame('mutation', $mutation->getName());

        $mockResolveInfo = $this->createMock(ResolveInfo::class);

        $result = $mutation->resolve('foo', ['testObject'=>['test'=>42]], $mockResolveInfo);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertEquals('42', $result->getTest());
    }
}
