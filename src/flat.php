<?php
declare(strict_types = 1);

namespace cms;

/**
 * Load entity
 */
function flat_load(array $entity, array $crit = [], array $opts = []): array
{
    $attrs = db_attr($entity['attr']);

    if ($opts['mode'] === 'size') {
        $opts['select'] = ['COUNT(*)'];
    } elseif (!$opts['select']) {
        $opts['select'] = array_column($attrs, 'col');
    }

    $cols = db_crit($crit, $attrs);
    $stmt = db()->prepare(
        db_select($opts['select'])
        . db_from($entity['tab'])
        . db_where($cols['where'])
        . db_order($opts['order'])
        . db_limit($opts['limit'], $opts['offset'])
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
 */
function flat_save(array $data): array
{
    $attrs = $data['_entity']['attr'];
    $cols = db_cols($attrs, $data);

    // Insert or update
    if (empty($data['_old'])) {
        $stmt = db()->prepare(db_insert($data['_entity']['tab']) . db_values($cols['val']));
    } else {
        $stmt = db()->prepare(
            db_update($data['_entity']['tab'])
            . db_set($cols['val'])
            . db_where([$attrs['id']['col'] . ' = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], db_type($attrs['id'], $data['_old']['id']));
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
 */
function flat_delete(array $data): void
{
    $attrs = $data['_entity']['attr'];
    $stmt = db()->prepare(db_delete($data['_entity']['tab']) . db_where([$attrs['id']['col'] . ' = :id']));
    $stmt->bindValue(':id', $data['_old']['id'], db_type($attrs['id'], $data['_old']['id']));
    $stmt->execute();
}
