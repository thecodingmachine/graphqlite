<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use DateTimeImmutable;
use Porpaginas\Arrays\ArrayResult;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\ProductTypeEnum;
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
            new Product('Foo', 42.0, ProductTypeEnum::NON_FOOD()),
        ]);
    }

    /**
     * This is supposed to return an array of products... but it returns an array of array of Products.
     * Useful to test error messages.
     *
     * @Query()
     * @return Product[]
     */
    public function getProductsBadType(): array
    {
        return [
            [
                new Product('Foo', 42.0, ProductTypeEnum::NON_FOOD()),
                new Product('Foo', 42.0, ProductTypeEnum::NON_FOOD()),
            ]
        ];
    }

    /**
     * @Query()
     */
    public function echoProductType(ProductTypeEnum $productType): ProductTypeEnum
    {
        return $productType;
    }

    /**
     * @Query()
     */
    public function echoDate(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date;
    }
}
