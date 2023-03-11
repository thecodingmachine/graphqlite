<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Class SpecialProduct.
 *
 * @Type()
 */
class SpecialProduct
{
    /** @var string */
    private $name;

    /** @var float */
    private $price;

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @Field()
     */
    public function getSpecial(): string
    {
        return 'unicorn';
    }

    /**
     * @Field()
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
}
