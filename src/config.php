<?php
namespace config;

use app;

/**
 * Value
 *
 * @param string $key
 *
 * @return mixed
 */
function value($key = null)
{
    $data = app\data('config');

    if ($key === null) {
        return $data;
    }

    return isset($data[$key]) ? $data[$key] : null;
}
