<?php
namespace akilli;

use RuntimeException;

/**
 * Factory
 *
 * @param string $key
 *
 * @return object
 *
 * @throws RuntimeException
 */
function db(string $key)
{
    $db = & registry('db');

    if (!isset($db[$key])) {
        $db[$key] = [];
        $data = data('db', $key);

        if (empty($data['callback']) || !is_callable($data['callback'])) {
            throw new RuntimeException(_('Invalid database configuration'));
        }

        $db[$key] = $data['callback']($data);
    }

    return $db[$key];
}

/**
 * Transaction
 *
 * @param string $key
 * @param callable $callback
 *
 * @return bool
 */
function db_transaction(string $key, callable $callback): bool
{
    static $data;

    // Load config
    if ($data === null) {
        $data = data('db');
    }

    return !empty($data[$key]['transaction']) ? $data[$key]['transaction'](db($key), $callback) : $callback();
}
