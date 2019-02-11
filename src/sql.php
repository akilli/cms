<?php
declare(strict_types = 1);

namespace sql;

use app;
use arr;
use PDO;
use DomainException;
use Throwable;

/**
 * Load entity
 */
function load(array $entity, array $crit = [], array $opt = []): array
{
    if ($opt['mode'] === 'size') {
        $opt['select'] = ['count(*)'];
    } elseif (!$opt['select']) {
        $opt['select'] = array_keys(attr($entity['attr']));
    }

    $cols = crit($crit, $entity['attr']);
    $stmt = db($entity['db'])->prepare(
        sel($opt['select'])
        . from($entity['id'])
        . where($cols['crit'])
        . order($opt['order'])
        . limit($opt['limit'], $opt['offset'])
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
    $attrs = $entity['attr'];
    $db = db($entity['db']);

    if (!($cols = cols($data, $attrs)) || empty($cols['param'])) {
        return $data;
    }

    // Insert or update
    if ($data['_old']) {
        $stmt = $db->prepare(upd($entity['id']) . set($cols['val']) . where(['id = :_id']));
        $stmt->bindValue(':_id', $data['_old']['id'], type($data['_old']['id']));
    } else {
        $stmt = $db->prepare(ins($entity['id']) . vals($cols['val']));
    }

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    // Set DB generated id
    if (!$data['_old'] && $attrs['id']['type'] === 'serial') {
        $data['id'] = (int) $db->lastInsertId(($entity['parent_id'] ?: $entity['id']) . '_id_seq');
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
    $stmt = db($entity['db'])->prepare(del($entity['id']) . where(['id = :id']));
    $stmt->bindValue(':id', $data['_old']['id'], type($data['_old']['id']));
    $stmt->execute();
}

/**
 * Transaction
 *
 * @throws Throwable
 */
function trans(callable $call, string $id): void
{
    static $level = [];

    $db = db($id);
    $level[$id] = $level[$id] ?? 0;

    ++$level[$id] === 1 ? $db->beginTransaction() : $db->exec('SAVEPOINT LEVEL_' . $level[$id]);

    try {
        $call();
        $level[$id] === 1 ? $db->commit() : $db->exec('RELEASE SAVEPOINT LEVEL_' . $level[$id]);
        --$level[$id];
    } catch (Throwable $e) {
        $level[$id] === 1 ? $db->rollBack() : $db->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $level[$id]);
        --$level[$id];
        app\log($e);
        throw $e;
    }
}

/**
 * Database
 */
function db(string $id): PDO
{
    static $pdo = [];

    if (!$id) {
        throw new DomainException(app\i18n('Invalid configuration'));
    } elseif (empty($pdo[$id])) {
        $cfg = app\cfg('db', $id);
        $pdo[$id] = new PDO($cfg['dsn'], $cfg['user'], $cfg['password'], APP['pdo']);
    }

    return $pdo[$id];
}

/**
 * Returns appropriate parameter type
 */
function type($val): int
{
    if ($val === null) {
        return PDO::PARAM_NULL;
    }

    if (is_bool($val)) {
        return PDO::PARAM_BOOL;
    }

    if (is_int($val)) {
        return PDO::PARAM_INT;
    }

    return PDO::PARAM_STR;
}

/**
 * Prepare columns
 */
function cols(array $data, array $attrs): array
{
    $attrs = attr($attrs, true);
    $cols = ['param' => [], 'val' => []];

    foreach (array_intersect_key($data, $attrs) as $attrId => $val) {
        $val = val($val, $attrs[$attrId]);
        $p = ':' . $attrId;
        $cols['param'][$attrId] = [$p, $val, type($val)];
        $cols['val'][$attrId] = $p;
    }

    return $cols;
}

/**
 * Filter out non-DB and optionally auto increment columns
 */
function attr(array $attrs, bool $auto = false): array
{
    foreach ($attrs as $attrId => $attr) {
        if ($attr['virtual'] || $auto && $attr['auto']) {
            unset($attrs[$attrId]);
        }
    }

    return $attrs;
}

/**
 * Prepare value
 *
 * @return mixed
 */
function val($val, array $attr)
{
    if (is_array($val) && $attr['backend'] === 'json') {
        $val = json_encode($val);
    } elseif ($val !== null && $attr['backend'] === 'json') {
        $val = (string) $val;
    } elseif (is_array($val)) {
        $val = '{' . implode($val, ',') . '}';
    } elseif ($val !== null && $attr['multiple']) {
        $val = '{' . $val . '}';
    }

    return $val;
}

/**
 * Generates criteria
 *
 * @throws DomainException
 */
function crit(array $crit, array $attrs): array
{
    static $count = 0;

    $cols = ['crit' => [], 'param' => []];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $o = [];

        foreach ($part as $c) {
            if (empty($c[0]) || empty($attrs[$c[0]]) || !array_key_exists(1, $c)) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $attr = $attrs[$c[0]];
            $val = $c[1];
            $op = $c[2] ?? APP['op']['='];
            $isCol = !empty($c[3]);

            if (empty(APP['op'][$op]) || $isCol && !$val || is_array($val) && !$val) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $param = ':crit_' . $attr['id'] . '_';
            $val = is_array($val) ? $val : [$val];

            if (in_array($op, [APP['op']['*'], APP['op']['!*'], APP['op']['^'], APP['op']['!^'], APP['op']['$'], APP['op']['!$']])) {
                $not = in_array($op, [APP['op']['!*'], APP['op']['!^'], APP['op']['!$']]) ? ' NOT' : '';
                $pre = in_array($op, [APP['op']['*'], APP['op']['!*'], APP['op']['$'], APP['op']['!$']]) ? '%' : '';
                $post = in_array($op, [APP['op']['*'], APP['op']['!*'], APP['op']['^'], APP['op']['!^']]) ? '%' : '';

                foreach ($val as $v) {
                    if ($isCol) {
                        $o[] = $attr['id'] . $not . ' ILIKE ' . $v;
                    } else {
                        $p = $param . ++$count;
                        $cols['param'][] = [$p, $pre . str_replace(['%', '_'], ['\%', '\_'], $v) . $post, PDO::PARAM_STR];
                        $o[] = $attr['id'] . $not . ' ILIKE ' . $p;
                    }
                }
            } else {
                if (in_array($op, [APP['op']['='], APP['op']['!=']]) && ($n = array_keys($val, null, true))) {
                    $o[] = $attr['id'] . ' IS' . ($op === APP['op']['!='] ? ' NOT' : '') . ' NULL';
                    $val = arr\remove($val, $n);
                }

                if (!$val) {
                    continue;
                } elseif ($attr['backend'] === 'json' || $attr['multiple']) {
                    $val = [$val];
                }

                foreach ($val as $v) {
                    if ($isCol) {
                        $o[] = $attr['id'] . ' ' . $op . ' ' . $v;
                    } else {
                        $p = $param . ++$count;
                        $v = val($v, $attr);
                        $cols['param'][] = [$p, $v, type($v)];
                        $o[] = $attr['id'] . ' ' . $op . ' ' . $p;
                    }
                }
            }
        }

        $cols['crit'][] = '(' . implode(' OR ', $o) . ')';
    }

    return $cols;
}

/**
 * INSERT part
 */
function ins(string $tab): string
{
    return 'INSERT INTO ' . $tab;
}

/**
 * VALUES part
 */
function vals(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

/**
 * UPDATE part
 */
function upd(string $tab): string
{
    return 'UPDATE ' . $tab;
}

/**
 * SET part
 */
function set(array $cols): string
{
    $set = '';

    foreach ($cols as $col => $val) {
        $set .= ($set ? ', ' : '') . $col . ' = ' . $val;
    }

    return ' SET ' . $set;
}

/**
 * DELETE part
 */
function del(string $tab): string
{
    return 'DELETE FROM ' . $tab;
}

/**
 * SELECT part
 */
function sel(array $sel, bool $distinct = false): string
{
    $cols = [];

    foreach ($sel as $as => $col) {
        $cols[] = $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $cols ? ' SELECT ' . ($distinct ? 'DISTINCT ' : '') . implode(', ', $cols) : '';
}

/**
 * FROM part
 */
function from(string $tab, string $as = null): string
{
    return ' FROM ' . $tab . ($as ? ' AS ' . $as : '');
}

/**
 * WHERE part
 */
function where(array $cols): string
{
    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

/**
 * ORDER BY part
 */
function order(array $order): string
{
    $cols = [];

    foreach ($order as $attrId => $dir) {
        $cols[] = $attrId . ' ' . ($dir === 'desc' ? 'DESC' : 'ASC');
    }

    return $cols ? ' ORDER BY ' . implode(', ', $cols) : '';
}

/**
 * LIMIT part
 */
function limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}
