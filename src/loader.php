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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    return $attr['loader'] && ($call = fqn('loader_' . $attr['loader'])) ? $call($attr, $item) : $item[$attr['id']];
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
    if (empty($item[$attr['id']])) {
        return [];
    }

    if (is_array($item[$attr['id']])) {
        return $item[$attr['id']];
    }

    return json_decode($item[$attr['id']], true) ?: [];
}
