<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use function array_map;
use function array_merge;

/**
 * A custom error handler and error formatter for Webonyx that can read the GraphQLAggregateExceptionInterface
 * and the GraphQLExceptionInterface.
 */
final class WebonyxErrorHandler
{
    /**
     * @return mixed[]
     */
    public static function errorFormatter(Error $error): array
    {
        $formattedError = FormattedError::createFromException($error);
        $previous = $error->getPrevious();
        if ($previous instanceof GraphQLExceptionInterface && ! empty($previous->getExtensions())) {
            $formattedError['extensions'] += $previous->getExtensions();
        }

        return $formattedError;
    }

    /**
     * @param Error[] $errors
     *
     * @return mixed[]
     */
    public static function errorHandler(array $errors, callable $formatter): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $previous = $error->getPrevious();
            if ($previous instanceof GraphQLAggregateExceptionInterface) {
                $exceptions = $previous->getExceptions();
                $innerErrors = array_map(static function (ClientAware $clientAware) use ($error) {
                    // Let's build a new error at the same spot than the aggregated one, but for the wrapped exception.
                    $extensions = $clientAware instanceof GraphQLExceptionInterface ? $clientAware->getExtensions() : [];

                    return new Error($clientAware->getMessage(), $error->getNodes(), $error->getSource(), $error->getPositions(), $error->getPath(), $clientAware, $extensions);
                }, $exceptions);

                $formattedInnerErrors = self::errorHandler($innerErrors, $formatter);

                $formattedErrors = array_merge($formattedErrors, $formattedInnerErrors);
            } else {
                $formattedErrors[] = $formatter($error);
            }
        }

        return $formattedErrors;
    }
}
