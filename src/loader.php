<?php
namespace qnd;

/**
 * Loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function loader(array $attr, array $item)
{
    return $attr['loader'] ? $attr['loader']($attr, $item) : cast($attr, $item[$attr['id']] ?? null);
}

/**
 * Datetime loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function loader_datetime(array $attr, array $item)
{
    $code = $attr['id'];

    return empty($item[$code]) ? null : $item[$code];
}

/**
 * JSON loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function loader_json(array $attr, array $item): array
{
    $code = $attr['id'];

    if (empty($item[$code])) {
        return [];
    } elseif (is_array($item[$code])) {
        return $item[$code];
    }

    return json_decode($item[$code], true) ?: [];
}
