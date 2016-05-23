<?php
namespace qnd;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $criteria
 * @param array $opts
 *
 * @return int
 */
function content_size(string $eId, array $criteria = [], array $opts = []): int
{
    $criteria['entity_id'] = $eId;

    return flat_size('content', $criteria, $opts);
}

/**
 * Load entity
 *
 * @param string $eId
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function content_load(string $eId, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $criteria['entity_id'] = $eId;

    return flat_load($eId, $criteria, $index, $order, $limit);
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
