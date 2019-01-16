<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\User;

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
