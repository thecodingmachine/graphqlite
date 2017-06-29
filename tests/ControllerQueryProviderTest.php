<?php

namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestController;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;

class ControllerQueryProviderTest extends TestCase
{
    public function testQueryProvider()
    {
        $controller = new TestController();
        $reader = new AnnotationReader();

        $typeMapper = new class implements TypeMapperInterface {
            public function mapClassToType(string $className): TypeInterface
            {
                if ($className === TestObject::class) {
                    return new ObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => new StringType(),
                        ],
                    ]);
                } else {
                    throw new \RuntimeException('Unexpected type');
                }
            }
        };

        $hydrator = new class implements HydratorInterface {
            public function hydrate(array $data, TypeInterface $type)
            {
                return new TestObject($data['test']);
            }
        };

        $queryProvider = new ControllerQueryProvider($controller, $reader, $typeMapper, $hydrator);

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

        $mockResolveInfo = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $result = $usersQuery->resolve('foo', ['int'=>42, 'string'=>'foo', 'list'=>[
            ['test'=>42],
            ['test'=>12],
        ]], $mockResolveInfo);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('foo424212', $result->getTest());
    }
}
