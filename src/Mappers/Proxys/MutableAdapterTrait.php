<?php


namespace TheCodingMachine\GraphQLite\Mappers\Proxys;


use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use RuntimeException;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\NoFieldsException;
use function get_class;

/**
 * @internal
 */
trait MutableAdapterTrait
{
    /**
     * @var ObjectType|InterfaceType
     */
    private $type;
    /**
     * @var string|null
     */
    private $className;

    /** @var string */
    private $status;

    /** @var array<callable> */
    private $fieldsCallables = [];

    /** @var FieldDefinition[]|null */
    private $finalFields;

    /**
     * @throws InvariantViolation
     */
    public function assertValid(): void
    {
        $this->type->assertValid();
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->type->jsonSerialize();
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->type->toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type->__toString();
    }


    /**
     * @param string $name
     *
     * @return FieldDefinition
     *
     * @throws Exception
     */
    public function getField($name): FieldDefinition
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a '.get_class($this).' before fetching its fields.');
        }

        return $this->type->getField($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name): bool
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a '.get_class($this).' before fetching its fields.');
        }

        return $this->type->hasField($name);
    }

    /**
     * @return FieldDefinition[]
     *
     * @throws InvariantViolation
     */
    public function getFields(): array
    {
        if ($this->finalFields === null) {
            if ($this->status === MutableInterface::STATUS_PENDING) {
                throw new RuntimeException('You must freeze() a '.get_class($this).' before fetching its fields.');
            }

            $this->finalFields = $this->type->getFields();
            foreach ($this->fieldsCallables as $fieldsCallable) {
                $this->finalFields = FieldDefinition::defineFieldMap($this, $fieldsCallable()) + $this->finalFields;
            }
            if (empty($this->finalFields)) {
                throw NoFieldsException::create($this->name);
            }
        }

        return $this->finalFields;
    }

    public function freeze(): void
    {
        $this->status = self::STATUS_FROZEN;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function addFields(callable $fields): void
    {
        if ($this->status !== MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('Tried to add fields to a frozen MutableInterfaceType.');
        }
        $this->fieldsCallables[] = $fields;
    }
}
