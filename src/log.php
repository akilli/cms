<?php
namespace log;

use app;
use file;

/**
 * Log levels
 */
const EMERGENCY = 'emergency';
const ALERT = 'alert';
const CRITICAL = 'critical';
const ERROR = 'error';
const WARNING = 'warning';
const NOTICE = 'notice';
const INFO = 'info';
const DEBUG = 'debug';

/**
 * System is unusable.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function emergency($message, array $context = [])
{
    log(EMERGENCY, $message, $context);
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
function alert($message, array $context = [])
{
    log(ALERT, $message, $context);
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
function critical($message, array $context = [])
{
    log(CRITICAL, $message, $context);
}

/**
 * Runtime errors that do not require immediate action but should typically be logged and monitored.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function error($message, array $context = [])
{
    log(ERROR, $message, $context);
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
function warning($message, array $context = [])
{
    log(WARNING, $message, $context);
}

/**
 * Normal but significant events.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function notice($message, array $context = [])
{
    log(NOTICE, $message, $context);
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
function info($message, array $context = [])
{
    log(INFO, $message, $context);
}

/**
 * Detailed debug information.
 *
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function debug($message, array $context = [])
{
    log(DEBUG, $message, $context);
}

/**
 * Logs with an arbitrary level.
 *
 * @param int $level
 * @param string $message
 * @param array $context
 *
 * @return void
 */
function log($level, $message, array $context = [])
{
    if (empty($context['file'])) {
        $context['file'] = 'qnd.log';
    }

    $file = app\path('log', $context['file']);
    file\put($file, '[' . $level . '][' . date('r') . '] ' . $message . "\n\n", FILE_APPEND);
}
