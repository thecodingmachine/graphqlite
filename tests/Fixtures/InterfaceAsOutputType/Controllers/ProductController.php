<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types\Book;
use TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types\CreateProductInput;
use TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types\ProductInterface;
use TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types\ProductItems;

class ProductController
{
    #[Query]
    public function product(): ProductInterface
    {
        return new Book('The Trial');
    }

    #[Query]
    public function products(): ProductItems
    {
        return new ProductItems([new Book('The Trial'), new Book('The Castle')]);
    }

    // The input type forces GraphQLite to materialise output types mid-way through input field
    // building, which is the ordering that triggered the regression.
    #[Mutation]
    public function createProduct(CreateProductInput $input): ProductInterface
    {
        return new Book($input->name);
    }
}
