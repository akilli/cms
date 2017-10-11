<?php
declare(strict_types = 1);

namespace cms;

/**
 * Load entity
 */
function flat_load(array $entity, array $crit = [], array $opts = []): array
{
    $attrs = sql_attr($entity['attr']);

    if ($opts['mode'] === 'size') {
        $opts['select'] = ['COUNT(*)'];
    } elseif (!$opts['select']) {
        $opts['select'] = array_column($attrs, 'col');
    }

    $cols = sql_crit($crit, $attrs);
    $stmt = sql()->prepare(
        sql_select($opts['select'])
        . sql_from($entity['tab'])
        . sql_where($cols['where'])
        . sql_order($opts['order'])
        . sql_limit($opts['limit'], $opts['offset'])
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
    $cols = sql_cols($attrs, $data);

    // Insert or update
    if (empty($data['_old'])) {
        $stmt = sql()->prepare(sql_insert($data['_entity']['tab']) . sql_values($cols['val']));
    } else {
        $stmt = sql()->prepare(
            sql_update($data['_entity']['tab'])
            . sql_set($cols['val'])
            . sql_where([$attrs['id']['col'] . ' = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], sql_type($attrs['id'], $data['_old']['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($data['_old']) && $attrs['id']['auto']) {
        $data['id'] = (int) sql()->lastInsertId($data['_entity']['tab'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 */
function flat_delete(array $data): void
{
    $attrs = $data['_entity']['attr'];
    $stmt = sql()->prepare(sql_delete($data['_entity']['tab']) . sql_where([$attrs['id']['col'] . ' = :id']));
    $stmt->bindValue(':id', $data['_old']['id'], sql_type($attrs['id'], $data['_old']['id']));
    $stmt->execute();
}
