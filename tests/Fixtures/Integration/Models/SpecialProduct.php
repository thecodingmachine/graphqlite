<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Class SpecialProduct
 * @package TheCodingMachine\GraphQLite\Fixtures\Integration\Models
 * @Type()
 */
class SpecialProduct
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var float
     */
    private $price;

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @Field()
     * @return string
     */
    public function getSpecial(): string
    {
        return "unicorn";
    }

    /**
     * @Field()
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @Field()
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }
}