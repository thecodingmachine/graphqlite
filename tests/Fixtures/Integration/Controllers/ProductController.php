<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use ArrayIterator;
use DateTimeImmutable;
use Porpaginas\Arrays\ArrayResult;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\ProductTypeEnum;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\SpecialProduct;

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
            ],
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

    /**
     * @Query(name="getProduct")
     * @return \TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product|\TheCodingMachine\GraphQLite\Fixtures\Integration\Models\SpecialProduct
     */
    public function getProduct()
    {
        return new SpecialProduct('Special box', 10.99);
    }

    /**
     * @Query(name="getProducts2")
     * @return (\TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product|\TheCodingMachine\GraphQLite\Fixtures\Integration\Models\SpecialProduct)[]
     */
    public function getProducts2(): ArrayIterator
    {
        return new ArrayIterator([new SpecialProduct('Special box', 10.99), new SpecialProduct('Special box', 10.99)]);
    }
}
