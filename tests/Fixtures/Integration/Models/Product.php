<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models;


use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

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
     * @Field(name="price")
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }
}
