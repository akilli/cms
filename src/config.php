<?php
namespace config;

use akilli;

/**
 * Value
 *
 * @param string $key
 *
 * @return mixed
 */
function value(string $key = null)
{
    $data = akilli\data('config');

    if ($key === null) {
        return $data;
    }

    return $data[$key] ?? null;
}
