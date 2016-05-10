<?php
namespace qnd;

/**
 * Check access
 *
 * @param string $key
 *
 * @return bool
 */
function allowed(string $key = null): bool
{
    $data = data('privilege');
    $key = privilege($key);

    // Privilege does not exist
    if (empty($data[$key])) {
        return false;
    }

    $privileges = user('privilege');
    $allKey = strstr($key, '.', true) . '.all';

    return empty($data[$key]['active'])
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
function privilege(string $key = null): string
{
    if (!is_string($key) || empty($key)) {
        return request('id');
    }

    return substr_count($key, '.') === 0 ? request('entity') . '.' . $key : $key;
}

/**
 * Retrieve all applied privileges
 *
 * @return array
 */
function privileges(): array
{
    static $data;

    if ($data === null) {
        $data = data_order(
            array_filter(
                data('privilege'),
                function ($item) {
                    return !empty($item['active']) && empty($item['callback']);
                }
            ),
            ['sort' => 'asc', 'name' => 'asc']
        );
    }

    return $data;
}

/**
 * Is admin
 *
 * @return bool
 */
function admin(): bool
{
    return registered() && in_array('all', user('privilege'));
}
