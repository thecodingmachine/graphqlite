<?php


namespace TheCodingMachine\GraphQL\Controllers;


use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;

abstract class AbstractQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $typeMapper;
    private $hydrator;

    protected function getTestObjectType()
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

    protected function getTypeMapper()
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

    protected function getHydrator()
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
}