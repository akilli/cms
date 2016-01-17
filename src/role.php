<?php
namespace role;

use app;
use account;
use data;
use http;

/**
 * Check access
 *
 * @param string $key
 *
 * @return bool
 */
function allowed($key = null)
{
    $data = app\data('privilege');
    $key = privilege($key);

    // Privilege does not exist
    if (empty($data[$key])) {
        return false;
    }

    $privileges = account\user('privilege');
    $allKey = strstr($key, '.', true) . '.all';

    return empty($data[$key]['is_active'])
    || admin()
    || !empty($data[$key]['callback']) && is_callable($data[$key]['callback']) && $data[$key]['callback']()
    || $privileges && (in_array($allKey, $privileges) || in_array($key, $privileges));
}

/**
 * Retrieve full privilege id from current request
 *
 * @param string $key
 *
 * @return string
 */
function privilege($key = null)
{
    if (!is_string($key) || empty($key)) {
        return http\request('id');
    }

    return substr_count($key, '.') === 0 ? http\request('entity') . '.' . $key : $key;
}

/**
 * Retrieve all applied privileges
 *
 * @return array
 */
function privileges()
{
    static $data;

    if ($data === null) {
        $data = data\order(
            array_filter(
                app\data('privilege'),
                function ($item) {
                    return (!isset($item['is_active']) || $item['is_active'] !== false)
                    && (empty($item['callback']) || !is_callable($item['callback']));
                }
            ),
            ['sort_order' => 'asc', 'name' => 'asc']
        );
    }

    return $data;
}

/**
 * Is admin
 *
 * @return bool
 */
function admin()
{
    return account\registered() && ($privileges = account\user('privilege')) && in_array('all', $privileges);
}
