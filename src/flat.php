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
    $mode = $opts['mode'] ?? 'all';
    $attrs = db_attr($entity['attr']);
    $select = $mode === 'size' ? ['COUNT(*)'] : array_column($attrs, 'col');
    $stmt = db()->prepare(
        select($select)
        . from($entity['tab'])
        . where($crit, $attrs, $opts)
        . order($opts['order'] ?? [])
        . limit($opts['limit'] ?? 0, $opts['offset'] ?? 0)
    );
    $stmt->execute();

    if ($mode === 'size') {
        return [(int) $stmt->fetchColumn()];
    }

    if ($mode === 'one') {
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

    $stmt = db_prep(
        'INSERT INTO %s (%s) VALUES (%s) ON CONFLICT (%s) DO UPDATE SET %s',
        $item['_entity']['tab'],
        db_list(array_column($cols, 'col')),
        db_list(array_column($cols, 'cast')),
        $attrs['id']['col'],
        db_list(array_column($cols, 'set'))
    );

    foreach ($cols as $col) {
        $stmt->bindValue($col['param'], $col['val'], $col['type']);
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
    $stmt->bindValue(':id', $item['_old']['id'], db_type($item['_old']['id'], $attrs['id']));
    $stmt->execute();

    return true;
}
