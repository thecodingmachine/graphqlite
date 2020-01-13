<?php


namespace TheCodingMachine\GraphQLite\Loggers;


use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Throwable;
use function in_array;

/**
 * A logger that throws an exception on WARN, ERROR, FATAL.
 * Useful to detect errors in PSR-16 caches (that never throw exceptions but that log things)
 */
class ExceptionLogger extends AbstractLogger
{
    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        if (in_array($level, [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING])) {
            if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
                throw $context['exception'];
            }
            throw new \Exception($message);
        }
    }
}
