<?php
declare(strict_types = 1);

namespace db;

use sql;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    $attrs = sql\attr($ent['attr']);

    if ($opt['mode'] === 'size') {
        $opt['select'] = ['COUNT(*)'];
    } elseif (!$opt['select']) {
        $opt['select'] = array_column($attrs, 'col');
    }

    $cols = sql\crit($crit, $attrs);
    $stmt = sql\db()->prepare(
        sql\select($opt['select'])
        . sql\from($ent['id'])
        . sql\where($cols['where'])
        . sql\order($opt['order'])
        . sql\limit($opt['limit'], $opt['offset'])
    );

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    if ($opt['mode'] === 'size') {
        return [(int) $stmt->fetchColumn()];
    }

    if ($opt['mode'] === 'one') {
        return $stmt->fetch() ?: [];
    }

    return $stmt->fetchAll();
}

/**
 * Save entity
 */
function save(array $data): array
{
    $attrs = $data['_ent']['attr'];
    $cols = sql\cols($attrs, $data);

    // Insert or update
    if (empty($data['_old'])) {
        $stmt = sql\db()->prepare(
            sql\insert($data['_ent']['id'])
            . sql\values($cols['val'])
        );
    } else {
        $stmt = sql\db()->prepare(
            sql\update($data['_ent']['id'])
            . sql\set($cols['val'])
            . sql\where([$attrs['id']['col'] . ' = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], sql\type($data['_old']['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($data['_old']) && $attrs['id']['auto']) {
        $data['id'] = (int) sql\db()->lastInsertId($data['_ent']['id'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
    $attrs = $data['_ent']['attr'];
    $stmt = sql\db()->prepare(
        sql\delete($data['_ent']['id'])
        . sql\where([$attrs['id']['col'] . ' = :id'])
    );
    $stmt->bindValue(':id', $data['_old']['id'], sql\type($data['_old']['id']));
    $stmt->execute();
}
