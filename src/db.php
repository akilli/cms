<?php
declare(strict_types = 1);

namespace db;

use app;
use sql;
use DomainException;

/**
 * Load entity
 */
function load(array $entity, array $crit = [], array $opt = []): array
{
    if ($opt['mode'] === 'size') {
        $opt['select'] = ['COUNT(*)'];
    } elseif (!$opt['select']) {
        $opt['select'] = array_keys(sql\attr($entity['attr']));
    }

    $join = '';

    if ($entity['parent']) {
        $join = array_diff_key($entity['attr'], app\cfg('entity', $entity['parent'])['attr']) ? $entity['id'] : '';
        $crit[] = ['entity', $entity['id']];
    }

    $cols = sql\crit($crit);
    $stmt = sql\db()->prepare(
        sql\sel($opt['select'])
        . sql\from($entity['parent'] ?: $entity['id'])
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
    $entity = $data['_entity'];
    $old = $data['_old'];
    $attrs = $entity['attr'];

    if ($entity['parent']) {
        if ($old && ($old['entity'] !== $entity['id'] || !empty($data['entity']) && $old['entity'] !== $data['entity'])) {
            throw new DomainException(app\i18n('Invalid entity %s', $old['entity']));
        }

        $data['entity'] = $entity['id'];
        $p = app\cfg('entity', $entity['parent']);
        $data['_entity'] = $p;
        $data = ($p['type'] . '\save')($data);
        $data['_entity'] = $entity;
        $attrs = array_diff_key($attrs, $p['attr']);

        if (!$attrs || !array_intersect_key($data, $attrs)) {
            return $data;
        }

        if ($old) {
            $stmt = sql\db()->prepare(
                sql\sel(['COUNT(*)'])
                . sql\from($entity['id'])
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

    if (!($cols = sql\cols($data, $attrs)) || empty($cols['param'])) {
        return $data;
    }

    // Insert or update
    if (!$old) {
        $stmt = sql\db()->prepare(
            sql\ins($entity['id'])
            . sql\vals($cols['val'])
        );
    } else {
        $stmt = sql\db()->prepare(
            sql\upd($entity['id'])
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
        $data['id'] = (int) sql\db()->lastInsertId($entity['id'] . '_id_seq');
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
    $entity = $data['_entity'];
    $old = $data['_old'];

    if ($entity['parent'] && $old['entity'] !== $entity['id']) {
        throw new DomainException(app\i18n('Invalid entity %s', $old['entity']));
    }

    $stmt = sql\db()->prepare(
        sql\del($entity['parent'] ?: $entity['id'])
        . sql\where(['id = :id'])
    );
    $stmt->bindValue(':id', $old['id'], sql\type($old['id']));
    $stmt->execute();
}
