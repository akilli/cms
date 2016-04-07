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
    $db = db($metadata['db']);

    // Prepare statement
    $stmt = $db->prepare(
        'SELECT COUNT(*) as total'
        . db_from($db, $metadata['table'])
        . db_where($db, (array) $criteria, $metadata['attributes'], null, false, $options)
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
 * @param int|array $limit
 *
 * @return array
 */
function sql_load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = db_meta($entity);
    $db = db($metadata['db']);
    $options = ['search' => $index === 'search'];

    // Prepare statement
    $stmt = $db->prepare(
        db_select($db, $metadata['attributes'])
        . db_from($db, $metadata['table'])
        . db_where($db, (array) $criteria, $metadata['attributes'], null, false, $options)
        . db_order($db, (array) $order, $metadata['attributes'])
        . db_limit($limit)
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
    $db = db($metadata['db']);

    // Columns
    $columns = $params = $sets = [];
    db_columns($metadata['attributes'], $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'INSERT INTO ' . $metadata['table']
        . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($metadata['attributes'][$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($metadata['attributes']['id']['auto'])) {
        $item['id'] = (int) $db->lastInsertId($metadata['sequence']);
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
    $db = db($metadata['db']);

    // Columns
    $columns = $params = $sets = [];
    db_columns($metadata['attributes'], $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET ' . implode(', ', $sets)
        . ' WHERE ' . $metadata['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($params as $code => $param) {
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
    $db = db($metadata['db']);

    // Prepare statement
    $stmt = $db->prepare(
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
