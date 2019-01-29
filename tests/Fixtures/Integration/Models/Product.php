<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;


use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class Product
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var float
     */
    private $price;

    /**
     * Product constructor.
     * @param string $name
     * @param float $price
     */
    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @Field(name="name")
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
