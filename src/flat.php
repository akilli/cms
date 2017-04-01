<?php
declare(strict_types = 1);

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
    $opts['order'] = $opts['mode'] === 'size' || $opts['order'] ? $opts['order'] : ['id' => 'asc'];
    $select = $opts['mode'] === 'size' ? ['COUNT(*)'] : array_column($attrs, 'col');
    $cols = db_crit($crit, $attrs);
    $stmt = db()->prepare(
        select($select)
        . from($entity['tab'])
        . where($cols['where'])
        . order($opts['order'])
        . limit($opts['limit'], $opts['offset'])
    );

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

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
 * @param array $data
 *
 * @return array
 */
function flat_save(array $data): array
{
    $attrs = $data['_entity']['attr'];
    $cols = db_cols($attrs, $data);

    // Insert or update
    if (empty($data['_old'])) {
        $stmt = db_prep(
            'INSERT INTO %s (%s) VALUES (%s)',
            $data['_entity']['tab'],
            db_list(array_keys($cols['in'])),
            db_list($cols['in'])
        );
    } else {
        $stmt = db_prep(
            'UPDATE %s SET %s WHERE %s = :_id',
            $data['_entity']['tab'],
            db_list($cols['up']),
            $attrs['id']['col']
        );
        $stmt->bindValue(':_id', $data['_old']['id'], $attrs['id']['pdo']);
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($data['_old']) && $attrs['id']['auto']) {
        $data['id'] = (int) db()->lastInsertId($data['_entity']['tab'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 *
 * @param array $data
 *
 * @return void
 */
function flat_delete(array $data): void
{
    $stmt = db_prep(
        'DELETE FROM %s WHERE %s = :id',
        $data['_entity']['tab'],
        $data['_entity']['attr']['id']['col']
    );
    $stmt->bindValue(':id', $data['_old']['id'], $data['_entity']['attr']['id']['pdo']);
    $stmt->execute();
}
