<?php
namespace qnd;

/**
 * Size entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function content_size(array $entity, array $crit = [], array $opts = []): int
{
    $crit['entity_id'] = $entity['id'];

    return flat_size($entity, $crit, $opts);
}

/**
 * Load entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function content_load(array $entity, array $crit = [], array $opts = []): array
{
    $crit['entity_id'] = $entity['id'];

    return flat_load($entity, $crit, $opts);
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 */
function content_create(array & $item): bool
{
    $item['entity_id'] = $item['_entity']['id'];

    return flat_create($item);
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function content_save(array & $item): bool
{
    $item['entity_id'] = $item['_entity']['id'];

    return flat_save($item);
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function content_delete(array & $item): bool
{
    return !empty($item['_entity']['id']) && $item['_entity']['id'] === $item['entity_id'] && flat_delete($item);
}
