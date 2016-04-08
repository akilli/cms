<?php
namespace akilli;

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function flat_size(string $entity, array $criteria = null, array $options = []): int
{
    $meta = db_meta($entity);

    // Prepare statement
    $stmt = db()->prepare(
        'SELECT COUNT(*) as total'
        . from($meta['table'])
        . where((array) $criteria, $meta['attributes'], $options)
    );

    // Execute statement
    $stmt->execute();

    // Result
    $item = $stmt->fetch();

    return (int) $item['total'];
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int[] $limit
 *
 * @return array
 */
function flat_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $meta = db_meta($entity);
    $options = ['search' => $index === 'search'];

    // Prepare statement
    $stmt = db()->prepare(
        select($meta['attributes'])
        . from($meta['table'])
        . where((array) $criteria, $meta['attributes'], $options)
        . order((array) $order, $meta['attributes'])
        . limit($limit)
    );

    // Execute statement
    $stmt->execute();

    // Result
    return $stmt->fetchAll();
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 */
function flat_create(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $cols = db_columns($meta['attributes'], $item);

    // Prepare statement
    $stmt = db()->prepare(
        'INSERT INTO ' . $meta['table']
        . ' (' . implode(', ', $cols['col']) . ') VALUES (' . implode(', ', $cols['param']) . ')'
    );

    // Bind values
    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($meta['attributes'][$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($meta['attributes']['id']['auto'])) {
        $item['id'] = (int) db()->lastInsertId($meta['sequence']);
    }

    return true;
}

/**
 * Save
 *
 * @param array $item
 *
 * @return bool
 */
function flat_save(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $cols = db_columns($meta['attributes'], $item);

    // Prepare statement
    $stmt = db()->prepare(
        'UPDATE ' . $meta['table']
        . ' SET ' . implode(', ', $cols['set'])
        . ' WHERE ' . $meta['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($meta['attributes'][$code], $item[$code]));
    }

    $stmt->bindValue(
        ':id',
        $item['_old']['id'],
        db_type($meta['attributes']['id'], $item['_old']['id'])
    );

    // Execute statement
    $stmt->execute();

    return true;
}

/**
 * Delete data
 *
 * @param array $item
 *
 * @return bool
 */
function flat_delete(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);

    // Prepare statement
    $stmt = db()->prepare(
        'DELETE FROM ' . $meta['table'] . ' WHERE ' . $meta['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    $stmt->bindValue(
        ':id',
        $item['_old']['id'],
        db_type($meta['attributes']['id'], $item['_old']['id'])
    );

    // Execute statement
    $stmt->execute();

    return true;
}
