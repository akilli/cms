<?php
namespace db;

use app;
use i18n;
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
function factory($key)
{
    $db = & app\registry('db');

    if (!isset($db[$key])) {
        $db[$key] = [];
        $data = app\data('db', $key);

        if (empty($data['callback']) || !is_callable($data['callback'])) {
            throw new RuntimeException(i18n\translate('Invalid database configuration'));
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
function transaction($key, callable $callback)
{
    static $data;

    // Load config
    if ($data === null) {
        $data = app\data('db');
    }

    return !empty($data[$key]['transaction']) ? $data[$key]['transaction'](factory($key), $callback) : $callback();
}
