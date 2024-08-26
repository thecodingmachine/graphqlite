<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use ArrayIterator;
use DateTimeImmutable;
use Porpaginas\Arrays\ArrayResult;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\ProductTypeEnum;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\SpecialProduct;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\TrickyProduct;

class ProductController
{
    /** @return Product[] */
    #[Query]
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
     * @return Product[]
     */
    #[Query]
    public function getProductsBadType(): array
    {
        return [
            [
                new Product('Foo', 42.0, ProductTypeEnum::NON_FOOD()),
                new Product('Foo', 42.0, ProductTypeEnum::NON_FOOD()),
            ],
        ];
    }

    #[Query]
    public function echoProductType(ProductTypeEnum $productType): ProductTypeEnum
    {
        return $productType;
    }

    #[Query]
    public function echoSomeProductType(): ProductTypeEnum
    {
        return ProductTypeEnum::FOOD();
    }

    #[Query]
    public function echoDate(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date;
    }

    #[Query(name: 'getProduct')]
    public function getProduct(): Product|SpecialProduct
    {
        return new SpecialProduct('Special box', 10.99);
    }

    /** @return (Product|SpecialProduct)[] */
    #[Query(name: 'getProducts2')]
    public function getProducts2(): ArrayIterator
    {
        return new ArrayIterator([new SpecialProduct('Special box', 10.99), new SpecialProduct('Special box', 10.99)]);
    }

    #[Mutation]
    public function createTrickyProduct(
        #[UseInputType('CreateTrickyProductInput!')]
        TrickyProduct $product,
    ): TrickyProduct
    {
        return $product;
    }

    #[Query]
    public function getTrickyProduct(): TrickyProduct
    {
        $product = new TrickyProduct();
        $product->setName('Special', 'box');
        $product->price = 11.99;
        $product->multi = 11.11;
        return $product;
    }

    #[Mutation]
    public function updateTrickyProduct(
        #[UseInputType('UpdateTrickyProductInput!')]
        TrickyProduct $product,
    ): TrickyProduct
    {
        return $product;
    }
}
