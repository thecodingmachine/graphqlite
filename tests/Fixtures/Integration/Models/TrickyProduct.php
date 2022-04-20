<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * Class TrickyProduct
 * @package TheCodingMachine\GraphQLite\Fixtures\Integration\Models
 * @Type()
 * @Input(name="CreateTrickyProductInput", default=true)
 * @Input(name="UpdateTrickyProductInput")
 */
class TrickyProduct
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     * @Field()
     */
    public $price;

    /**
     * @param string $name
     * @param float $price
     */
    public function __construct($name, $price)
    {
        $this->name = $name;
        $this->price = $price;
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
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @Field()
     * @Autowire(for="testService", identifier="testService")
     * @param string $name
     * @param string $testService
     * @return void
     */
    public function setName(string $name, string $testService): void
    {
        $this->name = $name . " " .$testService;
    }

}