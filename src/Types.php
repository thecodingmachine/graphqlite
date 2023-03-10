<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Upload\UploadType;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\VoidType;

/**
 * Set of GraphQLite provided types.
 */
class Types
{
    private static UploadType $uploadType;
    private static DateTimeType $dateTimeType;
    private static VoidType $voidType;

    public static function upload(): UploadType
    {
        return self::$uploadType ??= new UploadType();
    }

    public static function dateTime(): DateTimeType
    {
        return self::$dateTimeType ??= new DateTimeType();
    }

    public static function void(): VoidType
    {
        return self::$voidType ??= new VoidType();
    }
}
