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
function sql_size(string $entity, array $criteria = null, array $options = []): int
{
    $metadata = db_meta($entity);

    // Prepare statement
    $stmt = db()->prepare(
        'SELECT COUNT(*) as total'
        . from($metadata['table'])
        . where((array) $criteria, $metadata['attributes'], $options)
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
function sql_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $metadata = db_meta($entity);
    $options = ['search' => $index === 'search'];

    // Prepare statement
    $stmt = db()->prepare(
        select($metadata['attributes'])
        . from($metadata['table'])
        . where((array) $criteria, $metadata['attributes'], $options)
        . order((array) $order, $metadata['attributes'])
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
function sql_create(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = db_meta($item['_metadata']);
    $cols = db_columns($metadata['attributes'], $item);

    // Prepare statement
    $stmt = db()->prepare(
        'INSERT INTO ' . $metadata['table']
        . ' (' . implode(', ', $cols['col']) . ') VALUES (' . implode(', ', $cols['param']) . ')'
    );

    // Bind values
    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($metadata['attributes'][$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($metadata['attributes']['id']['auto'])) {
        $item['id'] = (int) db()->lastInsertId($metadata['sequence']);
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
function sql_save(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = db_meta($item['_metadata']);
    $cols = db_columns($metadata['attributes'], $item);

    // Prepare statement
    $stmt = db()->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET ' . implode(', ', $cols['set'])
        . ' WHERE ' . $metadata['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($metadata['attributes'][$code], $item[$code]));
    }

    $stmt->bindValue(
        ':id',
        $item['_original']['id'],
        db_type($metadata['attributes']['id'], $item['_original']['id'])
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
function sql_delete(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = db_meta($item['_metadata']);

    // Prepare statement
    $stmt = db()->prepare(
        'DELETE FROM ' . $metadata['table'] . ' WHERE ' . $metadata['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    $stmt->bindValue(
        ':id',
        $item['_original']['id'],
        db_type($metadata['attributes']['id'], $item['_original']['id'])
    );

    // Execute statement
    $stmt->execute();

    return true;
}
