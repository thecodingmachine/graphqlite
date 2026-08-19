<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\StripFieldPrefixes;

class Product
{
    public function __construct(
        private readonly string $name,
        private readonly bool $inStock,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasStock(): bool
    {
        return $this->inStock;
    }
}
