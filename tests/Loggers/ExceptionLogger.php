<?php

namespace TheCodingMachine\GraphQLite\Loggers;

use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Throwable;

use function in_array;

/**
 * A logger that throws an exception on WARN, ERROR, FATAL.
 * Useful to detect errors in PSR-16 caches (that never throw exceptions but that log things).
 */
class ExceptionLogger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        if (in_array($level, [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING])) {
            if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
                throw $context['exception'];
            }
            throw new Exception($message);
        }
    }
}
