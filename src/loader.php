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
    $item[$attr['uid']] = cast($attr, $item[$attr['uid']] ?? null);

    return $attr['loader'] && ($call = fqn('loader_' . $attr['loader'])) ? $call($attr, $item) : $item[$attr['uid']];
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
    if (empty($item[$attr['uid']])) {
        return [];
    }

    if (is_array($item[$attr['uid']])) {
        return $item[$attr['uid']];
    }

    return json_decode($item[$attr['uid']], true) ?: [];
}
