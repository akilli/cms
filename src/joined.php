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
function joined_size(string $entity, array $criteria = [], array $options = []): int
{
    return 0;
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
function joined_load(string $entity, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    return [];
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 */
function joined_create(array & $item): bool
{
    return true;
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function joined_save(array & $item): bool
{
    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function joined_delete(array $item): bool
{
    return true;
}
