<?php
declare(strict_types = 1);

namespace db;

use sql;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    $select = $opt['mode'] === 'size' ? ['COUNT(*)'] : array_keys(sql\attr($ent['attr']));
    $cols = sql\crit($crit);
    $stmt = sql\db()->prepare(
        sql\select($select)
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
    $ent = $data['_ent'];
    $attrs = $ent['attr'];

    if (!($cols = sql\cols($attrs, $data)) || empty($cols['param'])) {
        return $data;
    }

    // Insert or update
    if (!$data['_old']) {
        $stmt = sql\db()->prepare(
            sql\insert($ent['id'])
            . sql\values($cols['val'])
        );
    } else {
        $stmt = sql\db()->prepare(
            sql\update($ent['id'])
            . sql\set($cols['val'])
            . sql\where(['id = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], sql\type($data['_old']['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (!$data['_old'] && $attrs['id']['type'] === 'serial') {
        $data['id'] = (int) sql\db()->lastInsertId($ent['id'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
    $stmt = sql\db()->prepare(
        sql\delete($data['_ent']['id'])
        . sql\where(['id = :id'])
    );
    $stmt->bindValue(':id', $data['_old']['id'], sql\type($data['_old']['id']));
    $stmt->execute();
}
