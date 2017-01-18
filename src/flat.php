<?php
namespace qnd;

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
    $attrs = db_attr($entity['attr']);
    $select = $opts['mode'] === 'size' ? ['COUNT(*)'] : array_column($attrs, 'col') + $opts['select'];
    $stmt = db()->prepare(
        select($select)
        . from($entity['tab'])
        . where($crit, $attrs, $opts)
        . order($opts['order'])
        . limit($opts['limit'], $opts['offset'])
    );
    $stmt->execute();

    if ($opts['mode'] === 'size') {
        return [(int) $stmt->fetchColumn()];
    }

    if ($opts['mode'] === 'one') {
        return $stmt->fetch() ?: [];
    }

    return $stmt->fetchAll();
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
    $cols = db_cols($attrs, $item);

    // Insert or update
    if (empty($item['_old'])) {
        $stmt = db_prep(
            'INSERT INTO %s (%s) VALUES (%s)',
            $item['_entity']['tab'],
            db_list(array_keys($cols['in'])),
            db_list($cols['in'])
        );
    } else {
        $stmt = db_prep(
            'UPDATE %s SET %s WHERE %s = :_id',
            $item['_entity']['tab'],
            db_list($cols['up']),
            $attrs['id']['col']
        );
        $stmt->bindValue(':_id', $item['_old']['id'], $attrs['id']['pdo']);
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($item['_old']) && $attrs['id']['auto']) {
        $item['id'] = (int) db()->lastInsertId($item['_entity']['tab'] . '_id_seq');
    }

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

    $stmt = db_prep(
        'DELETE FROM %s WHERE %s = :id',
        $item['_entity']['tab'],
        $attrs['id']['col']
    );
    $stmt->bindValue(':id', $item['_old']['id'], $attrs['id']['pdo']);
    $stmt->execute();

    return true;
}
