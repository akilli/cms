<?php
namespace db;

use akilli;
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
function factory(string $key)
{
    $db = & akilli\registry('db');

    if (!isset($db[$key])) {
        $db[$key] = [];
        $data = akilli\data('db', $key);

        if (empty($data['callback']) || !is_callable($data['callback'])) {
            throw new RuntimeException(akilli\_('Invalid database configuration'));
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
function transaction(string $key, callable $callback): bool
{
    static $data;

    // Load config
    if ($data === null) {
        $data = akilli\data('db');
    }

    return !empty($data[$key]['transaction']) ? $data[$key]['transaction'](factory($key), $callback) : $callback();
}
