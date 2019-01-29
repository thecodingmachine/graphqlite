<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\User;

class ProductController
{
    /**
     * @Query()
     * @return Product[]
     */
    public function getProducts(): ArrayResult
    {
        return new ArrayResult([
            new Product('Foo', 42.0),
        ]);
    }
}
