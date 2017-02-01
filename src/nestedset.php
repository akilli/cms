<?php
namespace qnd;

/**
 * Load entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function nestedset_load(array $entity, array $crit = [], array $opts = []): array
{
    $opts['order'] = $opts['mode'] === 'size' || $opts['order'] ? $opts['order'] : ['root_id' => 'asc', 'lft' => 'asc'];
    $opts['select'] = ['pos' => "root_id || ':' || lft"];

    return flat_load($entity, $crit, $opts);
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return array
 */
function nestedset_save(array $item): array
{
    $attrs = $item['_entity']['attr'];
    $parts = explode(':', $item['pos']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['lft'] = ($item['mode'] === 'child' ? 1 : -1) * cast($attrs['lft'], $parts[1]);

    return flat_save($item);
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return array
 */
function nestedset_delete(array $item): array
{
    return flat_delete($item);
}
