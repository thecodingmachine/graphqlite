<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Class TrickyProduct.
 *
 * @Type()
 * @Input(name="CreateTrickyProductInput", default=true)
 * @Input(name="UpdateTrickyProductInput")
 */
class TrickyProduct
{
    /** @var string */
    private $name;

    /**
     * @var float
     * @Field()
     */
    public $price;

    /**
     * @var float
     * @Field()
     * @Field(for="CreateTrickyProductInput", inputType="Float")
     * @Field(for="UpdateTrickyProductInput", inputType="Int!")
     */
    public $multi;

    /**
     * @var string[]|null
     * @Field()
     */
    public $list;

    /** @var string */
    private $secret = 'hello';

    /** @var string */
    private $conditionalSecret = 'preset{secret}';

    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @Field()
     * @Autowire(for="testService", identifier="testService")
     */
    public function setName(string $name, string $testService): void
    {
        $this->name = $name . ' ' . $testService;
    }

    /**
     * @Field()
     * @Right("CAN_SEE_SECRET")
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @Field()
     * @Right("CAN_SET_SECRET")
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @Field()
     * @Security("conditionalSecret == 'actually{secret}'")
     * @Security("user && user.bar == 42")
     */
    public function setConditionalSecret(string $conditionalSecret): void
    {
        $this->conditionalSecret = $conditionalSecret;
    }

    /**
     * @Field()
     * @Security("this.isAllowed(key)")
     */
    public function getConditionalSecret(int $key): string
    {
        return $this->conditionalSecret;
    }

    public function isAllowed(string $conditionalSecret): bool
    {
        return $conditionalSecret === '1234';
    }
}
