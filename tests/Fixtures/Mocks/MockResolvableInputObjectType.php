<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Mocks;


use BadMethodCallException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use LogicException;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;

class MockResolvableInputObjectType extends InputObjectType implements ResolvableMutableInputInterface
{
    /** @var callable[] */
    private $decorators = [];
    /**
     * @var callable|null
     */
    private $resolveFn;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config, ?callable $resolve = null)
    {
        parent::__construct($config);

        $this->resolveFn = $resolve;
    }

    public function freeze(): void
    {
        //throw new BadMethodCallException('Unauthorized call to freeze in Mock object');
    }

    public function getStatus(): string
    {
        throw new BadMethodCallException('Unauthorized call to getStatus in Mock object');
    }

    public function addFields(callable $fields): void
    {
        throw new BadMethodCallException('Unauthorized call to addFields in Mock object');
    }

    /**
     * Resolves the arguments into an object.
     *
     * @param array $args
     * @return object
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $resolveInfo): object
    {
        $resolve = $this->resolveFn;
        if ($resolve === null) {
            throw new LogicException('Cannot call resolve on MockResolvableInputObjectType because no resolve function was passed when created.');
        }
        return $resolve($source, $args, $context, $resolveInfo);
    }

    /**
     * Decorates the call to the resolver with the $decorator.
     * The $decorator MUST receive the decorated object as first parameter and MUST return an object of a compatible type.
     * Additional parameters can be used to add fields.
     *
     * @param callable $decorator
     */
    public function decorate(callable $decorator): void
    {
        $this->decorators[] = $decorator;
    }

    /**
     * @return callable[]
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }
}