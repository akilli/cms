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
function flat_size(string $entity, array $criteria = null, array $options = []): int
{
    $meta = data('meta', $entity);

    $stmt = db()->prepare(
        'SELECT COUNT(*) as total'
        . from($meta['table'])
        . where((array) $criteria, $meta['attributes'], $options)
    );
    $stmt->execute();

    return (int) $stmt->fetch()['total'];
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
function flat_load(string $entity, array $criteria = null, $index = null, array $order = [], array $limit = []): array
{
    $meta = data('meta', $entity);
    $options = ['search' => $index === 'search'];

    $stmt = db()->prepare(
        select($meta['attributes'])
        . from($meta['table'])
        . where((array) $criteria, $meta['attributes'], $options)
        . order($order, $meta['attributes'])
        . limit($limit)
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Create entity
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

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $cols = cols($attrs, $item);

    $stmt = db()->prepare(
        'INSERT INTO ' . $meta['table']
        . ' (' . implode(', ', $cols['col']) . ') VALUES (' . implode(', ', $cols['param']) . ')'
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // Set DB generated id
    if ($attrs['id']['generator'] === 'auto') {
        $item['id'] = (int) db()->lastInsertId();
    }

    return true;
}

/**
 * Save entity
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

    $meta = $item['_meta'];
    $cols = cols($meta['attributes'], $item);

    $stmt = db()->prepare(
        'UPDATE ' . $meta['table']
        . ' SET ' . implode(', ', $cols['set'])
        . ' WHERE ' . $meta['attributes']['id']['column'] . '  = :id'
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($meta['attributes'][$code], $item[$code]));
    }

    $stmt->bindValue(':id', $item['_old']['id'], db_type($meta['attributes']['id'], $item['_old']['id']));
    $stmt->execute();

    return true;
}

/**
 * Delete entity
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

    $meta = $item['_meta'];

    $stmt = db()->prepare(
        'DELETE FROM ' . $meta['table'] . ' WHERE ' . $meta['attributes']['id']['column'] . '  = :id'
    );
    $stmt->bindValue(':id', $item['_old']['id'], db_type($meta['attributes']['id'], $item['_old']['id']));
    $stmt->execute();

    return true;
}
