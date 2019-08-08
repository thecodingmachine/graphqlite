<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;


use function array_map;
use function implode;
use TheCodingMachine\GraphQLite\TypeMappingException;
use Webmozart\Assert\Assert;

class CompositeFail implements Fail
{
    /**
     * @var array<Fail>
     */
    private $fails = [];

    public function addFail(Fail $fail)
    {
        $this->fails[] = $fail;
    }

    public function hasFailures(): bool
    {
        return !empty($this->fails);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        if (empty($this->fails)) {
            return '';
        }
        if (count($this->fails) === 1) {
            return $this->fails[0]->getMessage();
        }
        return 'Could not fetch type for one of these reasons: '.implode("\n\n", array_map(function(Fail $fail) {return $fail->getMessage();}, $this->fails));
    }

    /**
     * If the result is an error, an exception is thrown
     */
    public function throwIfError(): void
    {
        if (empty($this->fails)) {
            return;
        }
        $type = null;
        foreach ($this->fails as $fail) {
            if ($fail instanceof FailForType) {
                $type = $fail->getType();
            }
        }
        Assert::notNull($type);
        throw TypeMappingException::createFromFail($this, $type);
    }
}
