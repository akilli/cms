<?php
declare(strict_types=1);

namespace qnd;

/**
 * System is unusable.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function emergency(string $message, array $context = []): void
{
    logger('emergency', $message, $context);
}

/**
 * Action must be taken immediately.
 *
 * Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function alert(string $message, array $context = []): void
{
    logger('alert', $message, $context);
}

/**
 * Critical conditions.
 *
 * Example: Application component unavailable, unexpected exception.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function critical(string $message, array $context = []): void
{
    logger('critical', $message, $context);
}

/**
 * Runtime errors that do not require immediate action but should typically be logged and monitored.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function error(string $message, array $context = []): void
{
    logger('error', $message, $context);
}

/**
 * Exceptional occurrences that are not errors.
 *
 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function warning(string $message, array $context = []): void
{
    logger('warning', $message, $context);
}

/**
 * Normal but significant events.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function notice(string $message, array $context = []): void
{
    logger('notice', $message, $context);
}

/**
 * Interesting events.
 *
 * Example: User logs in, SQL logs.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function info(string $message, array $context = []): void
{
    logger('info', $message, $context);
}

/**
 * Detailed debug information.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function debug(string $message, array $context = []): void
{
    logger('debug', $message, $context);
}

/**
 * Logs with an arbitrary level.
 *
 * @param string $level
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function logger(string $level, string $message, array $context = []): void
{
    $context['file'] = empty($context['file']) ? 'app.log' : $context['file'];
    $file = path('log', $context['file']);
    file_put_contents($file, '[' . $level . '][' . date('r') . '] ' . $message . "\n\n", FILE_APPEND);
}
