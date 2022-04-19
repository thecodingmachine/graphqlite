<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

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
     * @Field(for={CreateTrickyProductInput}, inputType="CreateTrickyProductInput!")
     * @Field(for={UpdateTrickyProductInput}, inputType="UpdateTrickyProductInput")
     */
    private $price;


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
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

}