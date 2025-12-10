<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Loggers;

use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

use function in_array;

/**
 * A logger that throws an exception on WARN, ERROR, FATAL.
 * Useful to detect errors in PSR-16 caches (that never throw exceptions but that log things)
 */
class ExceptionLogger extends AbstractLogger
{
    /** @inheritDoc */
    public function log($level, $message, array $context = []): void
    {
        if (in_array($level, [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING])) {
            if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
                throw $context['exception'];
            }
            throw new Exception($message);
        }
    }
}
