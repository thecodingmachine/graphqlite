<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class Product
{
    /** @var string */
    private $name;

    /** @var float */
    private $price;

    /** @var ProductTypeEnum */
    private $type;

    /**
     * Product constructor.
     */
    public function __construct(string $name, float $price, ProductTypeEnum $type = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->type = $type;
    }

    /**
     * @Field(name="name")
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @Field()
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @Field()
     * @Right("YOU_DONT_HAVE_THIS_RIGHT")
     * @FailWith(null)
     */
    public function getUnauthorized(): string
    {
        return 'You are not allowed to see this';
    }

    public function getType(): ProductTypeEnum
    {
        return $this->type;
    }

    /**
     * @Factory()
     */
    public static function create(string $name, float $price, ProductTypeEnum $type = null): self
    {
        return new self($name, $price, $type);
    }

    /**
     * @Field()
     * @Security("this.isAllowed(secret)")
     */
    public function getMargin(string $secret): float
    {
        return 12.0;
    }

    public function isAllowed(string $secret): bool
    {
        return $secret === '42';
    }
}
