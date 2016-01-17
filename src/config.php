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
function value(string $key = null)
{
    $data = app\data('config');

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}
