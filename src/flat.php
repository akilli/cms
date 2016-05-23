<?php
namespace qnd;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function flat_size(string $eId, array $crit = [], array $opts = []): int
{
    $entity = data('entity', $eId);

    $stmt = prep(
        'SELECT COUNT(*) FROM %s %s',
        $entity['tab'],
        where($crit, $entity['attr'], $opts)
    );
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

/**
 * Load entity
 *
 * @param string $eId
 * @param array $crit
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function flat_load(string $eId, array $crit = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $attrs = $entity['attr'];
    $opts = ['search' => $index === 'search'];

    $stmt = db()->prepare(
        select($attrs)
        . from($entity['tab'])
        . where($crit, $attrs, $opts)
        . order($order, $attrs)
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
    $attrs = $item['_entity']['attr'];
    $cols = cols($attrs, $item);

    $stmt = prep(
        'INSERT INTO %s (%s) VALUES (%s)',
        $item['_entity']['tab'],
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
    $attrs = $item['_entity']['attr'];
    $cols = cols($attrs, $item);

    $stmt = prep(
        'UPDATE %s SET %s WHERE %s = :_id',
        $item['_entity']['tab'],
        implode(', ', $cols['set']),
        $attrs['id']['col']
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
    $attrs = $item['_entity']['attr'];

    $stmt = prep(
        'DELETE FROM %s WHERE %s = :id',
        $item['_entity']['tab'],
        $attrs['id']['col']
    );
    $stmt->bindValue(':id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
    $stmt->execute();

    return true;
}
