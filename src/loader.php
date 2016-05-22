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
    $callback = fqn('loader_' . $attr['type']);

    if (is_callable($callback)) {
        return $callback($attr, $item);
    }

    // Temporary
    if ($attr['multiple']) {
        return loader_json($attr, $item);
    }

    return $item[$attr['id']];
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
    } elseif (is_array($item[$attr['id']])) {
        return $item[$attr['id']];
    }

    return json_decode($item[$attr['id']], true) ?: [];
}
