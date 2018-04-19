<?php
declare(strict_types = 1);

namespace db;

use app;
use sql;
use DomainException;

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

    $join = '';

    if ($ent['parent']) {
        $join = array_diff_key($ent['attr'], app\cfg('ent', $ent['parent'])['attr']) ? $ent['id'] : '';
        $crit[] = ['ent', $ent['id']];
    }

    $cols = sql\crit($crit);
    $stmt = sql\db()->prepare(
        sql\select($opt['select'])
        . sql\from($ent['parent'] ?: $ent['id'])
        . sql\ljoin($join)
        . sql\where($cols['crit'])
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
 *
 * @throws DomainException
 */
function save(array $data): array
{
    $ent = $data['_ent'];
    $old = $data['_old'];
    $attrs = $ent['attr'];

    if ($ent['parent']) {
        if ($old && ($old['ent'] !== $ent['id'] || !empty($data['ent']) && $old['ent'] !== $data['ent'])) {
            throw new DomainException(app\i18n('Invalid entity %s', $old['ent']));
        }

        $data['ent'] = $ent['id'];
        $p = app\cfg('ent', $ent['parent']);
        $data['_ent'] = $p;
        $data = ($p['type'] . '\save')($data);
        $data['_ent'] = $ent;
        $attrs = array_diff_key($attrs, $p['attr']);

        if (!$attrs || !array_intersect_key($data, $attrs)) {
            return $data;
        }

        if ($old) {
            $stmt = sql\db()->prepare(
                sql\select(['COUNT(*)'])
                . sql\from($ent['id'])
                . sql\where(['id = :id'])
            );
            $stmt->bindValue(':id', $old['id'], sql\type($old['id']));
            $stmt->execute();

            if ((int) $stmt->fetchColumn() <= 0) {
                $old = [];
            }
        }

        if (!$old) {
            $attrs['id'] = array_replace($p['attr']['id'], ['auto' => false]);
        }
    }

    if (!($cols = sql\cols($attrs, $data)) || empty($cols['param'])) {
        return $data;
    }

    // Insert or update
    if (!$old) {
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
        $stmt->bindValue(':_id', $old['id'], sql\type($old['id']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (!$old && $attrs['id']['auto']) {
        $data['id'] = (int) sql\db()->lastInsertId($ent['id'] . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 *
 * @throws DomainException
 */
function delete(array $data): void
{
    $ent = $data['_ent'];
    $old = $data['_old'];

    if ($ent['parent'] && $old['ent'] !== $ent['id']) {
        throw new DomainException(app\i18n('Invalid entity %s', $old['ent']));
    }

    $stmt = sql\db()->prepare(
        sql\delete($ent['parent'] ?: $ent['id'])
        . sql\where(['id = :id'])
    );
    $stmt->bindValue(':id', $old['id'], sql\type($old['id']));
    $stmt->execute();
}
