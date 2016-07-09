<?php
namespace qnd;

/**
 * Size entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function flat_size(array $entity, array $crit = [], array $opts = []): int
{
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
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function flat_load(array $entity, array $crit = [], array $opts = []): array
{
    $stmt = db()->prepare(
        select($entity['attr'])
        . from($entity['tab'])
        . where($crit, $entity['attr'], $opts)
        . order($opts['order'] ?? [], $entity['attr'])
        . limit($opts['limit'] ?? 0, $opts['offset'] ?? 0)
    );
    $stmt->execute();

    if (!empty($opts['one'])) {
        return $stmt->fetch() ?: [];
    }

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
        implode(', ', array_column($cols, 'col')),
        implode(', ', array_column($cols, 'param'))
    );

    foreach ($cols as $uid => $col) {
        $stmt->bindValue($col['param'], $col['val'], $col['type']);
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
        implode(', ', array_column($cols, 'set')),
        $attrs['id']['col']
    );

    foreach ($cols as $uid => $col) {
        $stmt->bindValue($col['param'], $col['val'], $col['type']);
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
