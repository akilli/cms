<?php
namespace qnd;

use RuntimeException;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function content_size(string $entity, array $criteria = null, array $options = []): int
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
function content_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = []): array
{
    return [];
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function content_create(array & $item): bool
{
    return true;
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function content_save(array & $item): bool
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
function content_delete(array $item): bool
{
    return true;
}
