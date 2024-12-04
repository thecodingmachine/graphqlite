<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;


use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\Type;

class ProductInternal
{
    public function getMargin(): float
    {
        return 12.0;
    }
}

/**
 * @Type(class=ProductInternal::class)
 */
class ProductExternalType {
    /**
     * @Field()
     * @Security("this.canAccess()")
     */
    public function getMarginFails(ProductInternal $product): float
    {
        return $product->getMargin();
    }

    /**
     * @Field()
     */
    public function getMarginOk(ProductInternal $product): float
    {
        return $product->getMargin();
    }

    public function canAccess(): bool
    {
        return false;
    }

}
