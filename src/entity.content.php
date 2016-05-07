<?php
namespace qnd;

use RuntimeException;

/**
 * Size content entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function entity_content_size(string $entity, array $criteria = null, array $options = []): int
{
    return 0;
}

/**
 * Load content entity
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int[] $limit
 *
 * @return array
 */
function entity_content_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    return [];
}

/**
 * Create content entity
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function entity_content_create(array & $item): bool
{
    return true;
}

/**
 * Save content entity
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function entity_content_save(array & $item): bool
{
    return true;
}

/**
 * Delete content entity
 *
 * @param array $item
 *
 * @return bool
 */
function entity_content_delete(array $item): bool
{
    return true;
}
