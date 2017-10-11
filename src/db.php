<?php
declare(strict_types = 1);

namespace db;

use sql;

/**
 * Load entity
 */
function load(array $entity, array $crit = [], array $opts = []): array
{
    $attrs = sql\attr($entity['attr']);

    if ($opts['mode'] === 'size') {
        $opts['select'] = ['COUNT(*)'];
    } elseif (!$opts['select']) {
        $opts['select'] = array_column($attrs, 'col');
    }

    $cols = sql\crit($crit, $attrs);
    $stmt = sql\db()->prepare(
        sql\select($opts['select'])
        . sql\from($entity['tab'])
        . sql\where($cols['where'])
        . sql\order($opts['order'])
        . sql\limit($opts['limit'], $opts['offset'])
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
function save(array $data): array
{
    $attrs = $data['_entity']['attr'];
    $cols = sql\cols($attrs, $data);

    // Insert or update
    if (empty($data['_old'])) {
        $stmt = sql\db()->prepare(sql\insert($data['_entity']['tab']) . sql\values($cols['val']));
    } else {
        $stmt = sql\db()->prepare(
            sql\update($data['_entity']['tab'])
            . sql\set($cols['val'])
            . sql\where([$attrs['id']['col'] . ' = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], sql\type($attrs['id'], $data['_old']['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($data['_old']) && $attrs['id']['auto']) {
        $data['id'] = (int) sql\db()->lastInsertId($data['_entity']['tab'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
    $attrs = $data['_entity']['attr'];
    $stmt = sql\db()->prepare(sql\delete($data['_entity']['tab']) . sql\where([$attrs['id']['col'] . ' = :id']));
    $stmt->bindValue(':id', $data['_old']['id'], sql\type($attrs['id'], $data['_old']['id']));
    $stmt->execute();
}
