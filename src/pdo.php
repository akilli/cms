<?php
declare(strict_types=1);

namespace pdo;

use DomainException;
use PDO;
use Throwable;
use app;
use arr;
use sql;

/**
 * Size entity
 */
function size(array $entity, array $crit = []): int
{
    $cols = crit($crit, $entity['attr']);
    $stmt = db($entity['db'])->prepare(sql\select(['count(*)']) . sql\from($entity['id']) . sql\where($cols['crit']));
    array_map(fn(array $param): bool => $stmt->bindValue(...$param), $cols['param']);
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

/**
 * Load one entity
 */
function one(array $entity, array $crit = [], array $select = [], array $order = [], int $offset = 0): ?array
{
    return all($entity, $crit, $select, $order, 1, $offset)[0] ?? null;
}

/**
 * Load entity collection
 */
function all(
    array $entity,
    array $crit = [],
    array $select = [],
    array $order = [],
    int $limit = 0,
    int $offset = 0
): array {
    $select = $select ?: array_keys(attr($entity['attr']));
    $cols = crit($crit, $entity['attr']);
    $stmt = db($entity['db'])->prepare(
        sql\select($select)
        . sql\from($entity['id'])
        . sql\where($cols['crit'])
        . sql\order($order)
        . sql\limit($limit, $offset)
    );
    array_map(fn(array $param): bool => $stmt->bindValue(...$param), $cols['param']);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Save entity
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
        $stmt = $db->prepare(sql\update($entity['id']) . sql\set($cols['val']) . sql\where(['id = :_id']));
        $stmt->bindValue(':_id', $data['_old']['id'], type($data['_old']['id']));
    } else {
        $stmt = $db->prepare(sql\insert($entity['id']) . sql\values($cols['val']));
    }

    array_map(fn(array $param): bool => $stmt->bindValue(...$param), $cols['param']);
    $stmt->execute();

    // Set DB generated id
    if (!$data['_old'] && $attrs['id']['backend'] === 'serial') {
        $data['id'] = (int) $db->lastInsertId(($entity['parent_id'] ?: $entity['id']) . '_id_seq');
    }

    return $data;
}

/**
 * Delete entity
 */
function delete(array $data): void
{
    $entity = $data['_entity'];
    $stmt = db($entity['db'])->prepare(sql\delete($entity['id']) . sql\where(['id = :id']));
    $stmt->bindValue(':id', $data['_old']['id'], type($data['_old']['id']));
    $stmt->execute();
}

/**
 * Transaction
 *
 * @throws Throwable
 */
function transaction(callable $call, string $id): void
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

    if (empty($pdo[$id])) {
        $cfg = app\cfg('db', $id);
        $pdo[$id] = new PDO($cfg['dsn'], $cfg['user'], $cfg['password'], APP['pdo']);
    }

    return $pdo[$id];
}

/**
 * Returns appropriate parameter type
 */
function type(mixed $val): int
{
    return match (true) {
        $val === null => PDO::PARAM_NULL,
        is_bool($val) => PDO::PARAM_BOOL,
        is_int($val) => PDO::PARAM_INT,
        default => PDO::PARAM_STR,
    };
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
        if ($auto && $attr['auto']) {
            unset($attrs[$attrId]);
        }
    }

    return $attrs;
}

/**
 * Prepare value
 */
function val(mixed $val, array $attr): mixed
{
    return match (true) {
        $val === null => null,
        $attr['backend'] === 'json' => is_array($val) ? json_encode($val) : (string) $val,
        is_array($val) => '{' . implode(',', arr\change($val, null, 'NULL')) . '}',
        in_array($attr['backend'], ['multiint', 'multitext']) => '{' . $val . '}',
        default => $val,
    };
}

/**
 * Generates unique parameter
 */
function param(string $name): string
{
    static $count = 0;
    return $name . ++$count;
}

/**
 * Generates criteria
 *
 * @throws DomainException
 */
function crit(array $crit, array $attrs): array
{
    $cols = ['crit' => [], 'param' => []];
    $compEq = [APP['op']['='], APP['op']['!=']];
    $comp = [...$compEq, APP['op']['>'], APP['op']['>='], APP['op']['<'], APP['op']['<=']];
    $regexSearch = [APP['op']['~'], APP['op']['!~']];
    $regexStart = [APP['op']['^'], APP['op']['!^']];
    $regexEnd = [APP['op']['$'], APP['op']['!$']];
    $regex = [...$regexSearch, ...$regexStart, ...$regexEnd];
    $multi = ['multiint', 'multitext'];
    $multiJson = ['json', ...$multi];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $or = [];

        foreach ($part as $c) {
            if (empty($c[0]) || empty($attrs[$c[0]]) || !array_key_exists(1, $c)) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $attr = $attrs[$c[0]];
            $val = $c[1];
            $op = $c[2] ?? APP['op']['='];

            if (empty(APP['op'][$op]) || is_array($val) && !$val) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $param = ':crit_' . $attr['id'] . '_';

            if ($val === null && in_array($op, $compEq)) {
                $or[] = $attr['id'] . ' IS' . ($op === APP['op']['!='] ? ' NOT' : '') . ' NULL';
            } elseif (in_array($op, $compEq) && is_array($val) && !in_array($attr['backend'], $multiJson)) {
                $not = $op === APP['op']['!='] ? ' NOT' : '';
                $null = $attr['id'] . ' IS' . $not . ' NULL';
                $in = [];

                if (($n = array_keys($val, null, true)) && !($val = arr\remove($val, $n))) {
                    $or[] = $null;
                    continue;
                }

                foreach ($val as $v) {
                    $p = param($param);
                    $v = val($v, $attr);
                    $cols['param'][] = [$p, $v, type($v)];
                    $in[] = $p;
                }

                $inStr = implode(', ', $in);
                $or[] = ($n ? $null . ($not ? ' AND ' : ' OR ') : '') . $attr['id'] . $not . ' IN (' . $inStr . ')';
            } elseif (in_array($op, $comp)) {
                $p = param($param);
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];
                $or[] = $attr['id'] . ' ' . $op . ' ' . $p;
            } elseif (in_array($op, $regexSearch) && in_array($attr['backend'], $multiJson)) {
                $p = param($param);
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];
                $or[] = $attr['id'] . ' @> ' . $p . ($op === APP['op']['!~'] ? ' IS FALSE' : '');
            } elseif (in_array($op, [...$regexStart, ...$regexEnd]) && in_array($attr['backend'], $multi)) {
                $n = is_array($val) ? max(0, count($val) - 1) : 0;
                $p = param($param);
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];

                if (in_array($op, $regexEnd)) {
                    $n = $n > 0 ? ' - ' . $n : '';
                    $l = '[array_upper(' . $attr['id'] . ', 1)' . $n . ' : array_upper(' . $attr['id'] . ', 1)]';
                } else {
                    $n = $n > 0 ? ' + ' . $n : '';
                    $l = '[array_lower(' . $attr['id'] . ', 1) : array_lower(' . $attr['id'] . ', 1)' . $n . ']';
                }

                $or[] = $attr['id'] . $l . (in_array($op, [APP['op']['!^'], APP['op']['!$']]) ? ' != ' : ' = ') . $p;
            } elseif (in_array($op, $regex)) {
                $not = in_array($op, [APP['op']['!~'], APP['op']['!^'], APP['op']['!$']]) ? ' NOT' : '';
                $pre = in_array($op, [...$regexSearch, ...$regexEnd]) ? '%' : '';
                $post = in_array($op, [...$regexSearch, ...$regexStart]) ? '%' : '';
                $p = param($param);
                $val = val($val, $attr);
                $v = $pre . str_replace(['%', '_'], ['\%', '\_'], (string) $val) . $post;
                $cols['param'][] = [$p, $v, PDO::PARAM_STR];
                $or[] = $attr['id'] . '::text' . $not . ' ILIKE ' . $p;
            }
        }

        $cols['crit'][] = '(' . implode(' OR ', $or) . ')';
    }

    return $cols;
}
