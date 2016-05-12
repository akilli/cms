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
function flat_size(string $entity, array $criteria = [], array $options = []): int
{
    $meta = data('meta', $entity);

    $stmt = prep(
        'SELECT COUNT(*) AS total FROM %s %s',
        $meta['table'],
        where($criteria, $meta['attributes'], $options)
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
function flat_load(string $entity, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $meta = data('meta', $entity);
    $options = ['search' => $index === 'search'];

    $stmt = db()->prepare(
        select($meta['attributes'])
        . from($meta['table'])
        . where($criteria, $meta['attributes'], $options)
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

    $stmt = prep(
        'INSERT INTO %s (%s) VALUES (%s)',
        $meta['table'],
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
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
    $attrs = $meta['attributes'];
    $cols = cols($attrs, $item);

    $stmt = prep(
        'UPDATE %s SET %s WHERE %s = :_id',
        $meta['table'],
        implode(', ', $cols['set']),
        $attrs['id']['column']
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':_id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
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
    $attrs = $meta['attributes'];

    $stmt = prep(
        'DELETE FROM %s WHERE %s = :id',
        $meta['table'],
        $attrs['id']['column']
    );
    $stmt->bindValue(':id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
    $stmt->execute();

    return true;
}
