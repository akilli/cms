<?php
namespace qnd;

/**
 * Attribute loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function attribute_loader(array $attr, array $item)
{
    $callback = fqn('attribute_loader_' . $attr['type']);

    return is_callable($callback) ? $callback($attr, $item) : cast($attr, $item[$attr['id']] ?? null);
}

/**
 * JSON attribute loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function attribute_loader_json(array $attr, array $item): array
{
    if (empty($item[$attr['id']])) {
        return [];
    } elseif (is_array($item[$attr['id']])) {
        return $item[$attr['id']];
    }

    return json_decode($item[$attr['id']], true) ?: [];
}

/**
 * Multicheckbox attribute loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function attribute_loader_multicheckbox(array $attr, array $item): array
{
    return attribute_loader_json($attr, $item);
}

/**
 * Multiselect attribute loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function attribute_loader_multiselect(array $attr, array $item): array
{
    return attribute_loader_json($attr, $item);
}
