<?php
namespace qnd;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function content_size(string $entity, array $criteria = [], array $options = []): int
{
    $criteria['entity_id'] = $entity;

    return flat_size('content', $criteria, $options);
}

/**
 * Load entity
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function content_load(string $entity, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $criteria['entity_id'] = $entity;

    return flat_load($entity, $criteria, $index, $order, $limit);
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
    $item['entity_id'] = $item['_meta']['id'];

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
    $item['entity_id'] = $item['_meta']['id'];

    return flat_save($item);
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function content_delete(array $item): bool
{
    return !empty($item['_meta']['id']) && $item['_meta']['id'] === $item['entity_id'] && flat_delete($item);
}
