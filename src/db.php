<?php
declare(strict_types = 1);

namespace db;

use sql;

/**
 * Load entity
 */
function load(array $ent, array $crit = [], array $opt = []): array
{
    if ($opt['mode'] === 'size') {
        $opt['select'] = ['COUNT(*)'];
    } elseif (!$opt['select']) {
        $opt['select'] = array_keys(sql\attr($ent['attr']));
    }

    $cols = sql\crit($crit);
    $stmt = sql\db()->prepare(
        sql\select($opt['select'])
        . sql\from($ent['id'])
        . sql\join((string) $ent['parent_id'])
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
    $cols = sql\cols($data['_ent']['attr'], $data);

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
            . sql\where(['id = :_id'])
        );
        $stmt->bindValue(':_id', $data['_old']['id'], sql\type($data['_old']['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (empty($data['_old']) && $data['_ent']['attr']['id']['auto']) {
        $data['id'] = (int) sql\db()->lastInsertId($data['_ent']['id'] . '_id_seq');
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
