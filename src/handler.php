<?php
declare(strict_types = 1);

namespace handler;

use app;
use session;
use ErrorException;
use Throwable;

/**
 * Error
 */
function error(int $severity, string $msg, string $file, int $line): void
{
    app\log(new ErrorException($msg, 0, $severity, $file, $line));
}

/**
 * Exception
 */
function exception(Throwable $e): void
{
    app\log($e);
}

/**
 * Shutdown
 */
function shutdown(): void
{
    if ($data = app\reg('msg')) {
        session\set('msg', $data);
    }
}
