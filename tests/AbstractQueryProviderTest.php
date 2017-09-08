<?php


namespace TheCodingMachine\GraphQL\Controllers;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use Youshido\GraphQL\Type\InputObject\InputObjectType;
use Youshido\GraphQL\Type\InputTypeInterface;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;

abstract class AbstractQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $inputTestObjectType;
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

    protected function getInputTestObjectType()
    {
        if ($this->inputTestObjectType === null) {
            $this->inputTestObjectType = new InputObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => new StringType(),
                ],
            ]);
        }
        return $this->inputTestObjectType;
    }

    protected function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $this->typeMapper = new class($this->getTestObjectType(), $this->getInputTestObjectType()) implements TypeMapperInterface {
                /**
                 * @var ObjectType
                 */
                private $testObjectType;
                /**
                 * @var InputObjectType
                 */
                private $inputTestObjectType;

                public function __construct(ObjectType $testObjectType, InputObjectType $inputTestObjectType)
                {
                    $this->testObjectType = $testObjectType;
                    $this->inputTestObjectType = $inputTestObjectType;
                }

                public function mapClassToType(string $className): TypeInterface
                {
                    if ($className === TestObject::class) {
                        return $this->testObjectType;
                    } else {
                        throw new \RuntimeException('Unexpected type');
                    }
                }

                public function mapClassToInputType(string $className): InputTypeInterface
                {
                    if ($className === TestObject::class) {
                        return $this->inputTestObjectType;
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
