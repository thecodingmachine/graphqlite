<?php


namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Upload\UploadType;

/**
 * Keep track of all custom types (DateTimeType, UploadType...)
 */
class CustomTypesRegistry
{
    private static $uploadType;

    public static function getUploadType(): UploadType
    {
        if (self::$uploadType === null) {
            self::$uploadType = new UploadType();
        }
        return self::$uploadType;
    }

    public static function mapNameToType(string $name): ?Type
    {
        if ($name === 'Upload') {
            return self::getUploadType();
        }

        if ($name === 'DateTime') {
            return DateTimeType::getInstance();
        }

        return null;
    }
}
