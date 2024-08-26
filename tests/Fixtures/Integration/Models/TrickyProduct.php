<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
#[Input(name: 'CreateTrickyProductInput', default: true)]
#[Input(name: 'UpdateTrickyProductInput')]
class TrickyProduct
{
    private string $name;

    #[Field]
    public float $price;

    #[Field]
    #[Field(for: 'CreateTrickyProductInput', inputType: 'Float')]
    #[Field(for: 'UpdateTrickyProductInput', inputType: 'Int!')]
    public float $multi;

    /** @var string[]|null */
    #[Field]
    public array|null $list;

    private string $secret = 'hello';

    private string $conditionalSecret = 'preset{secret}';

    #[Field]
    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    #[Field]
    public function setName(
        string $name,
        #[Autowire(identifier: 'testService')]
        string $testService,
    ): void
    {
        $this->name = $name . ' ' . $testService;
    }

    #[Field]

    #[Right('CAN_SEE_SECRET')]
    public function getSecret(): string
    {
        return $this->secret;
    }

    #[Field]

    #[Right('CAN_SEE_SECRET')]
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    #[Field]
    #[Security("conditionalSecret == 'actually{secret}'")]
    #[Security('user && user.bar == 42')]
    public function setConditionalSecret(string $conditionalSecret): void
    {
        $this->conditionalSecret = $conditionalSecret;
    }

    #[Field]
    #[Security('this.isAllowed(key)')]
    public function getConditionalSecret(int $key): string
    {
        return $this->conditionalSecret;
    }

    public function isAllowed(string $conditionalSecret): bool
    {
        return $conditionalSecret === '1234';
    }
}
