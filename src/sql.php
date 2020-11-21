<?php
declare(strict_types=1);

namespace sql;

use app;
use arr;
use DomainException;
use PDO;
use Throwable;

/**
 * Size entity
 */
function size(array $entity, array $crit = []): int
{
    $cols = crit($crit, $entity['attr']);
    $stmt = db($entity['db'])->prepare(sel('count(*)') . from($entity['id']) . where($cols['crit']));

    foreach ($cols['param'] as $param) {
        $stmt->bindValue(...$param);
    }

    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

/**
 * Load entity
 */
function load(array $entity, array $crit = [], array $opt = []): array
{
    if (!$opt['select']) {
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
function type(mixed $val): int
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
 */
function val(mixed $val, array $attr): mixed
{
    if ($attr['backend'] === 'json' && is_array($val)) {
        $val = json_encode($val);
    } elseif ($attr['backend'] === 'json' && $val !== null) {
        $val = (string) $val;
    } elseif (is_array($val)) {
        $val = '{' . implode(',', arr\change($val, null, 'NULL')) . '}';
    } elseif (in_array($attr['backend'], ['int[]', 'text[]']) && $val !== null) {
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

            if ($val === null && in_array($op, [APP['op']['='], APP['op']['!=']])) {
                $or[] = $attr['id'] . ' IS' . ($op === APP['op']['!='] ? ' NOT' : '') . ' NULL';
            } elseif (in_array($op, [APP['op']['='], APP['op']['!=']])
                && is_array($val)
                && !in_array($attr['backend'], ['int[]', 'json', 'text[]'])
            ) {
                $not = $op === APP['op']['!='] ? ' NOT' : '';
                $null = $attr['id'] . ' IS' . $not . ' NULL';

                if (($n = array_keys($val, null, true)) && !($val = arr\remove($val, $n))) {
                    $or[] = $null;
                    continue;
                }

                $in = [];

                foreach ($val as $v) {
                    $p = $param . ++$count;
                    $v = val($v, $attr);
                    $cols['param'][] = [$p, $v, type($v)];
                    $in[] = $p;
                }

                $or[] = ($n ? $null . ($not ? ' AND ' : ' OR ') : '')
                    . $attr['id'] . $not . ' IN (' . implode(', ', $in) . ')';
            } elseif (in_array($op, [APP['op']['='], APP['op']['!='], APP['op']['>'], APP['op']['>='], APP['op']['<'], APP['op']['<=']])) {
                $p = $param . ++$count;
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];
                $or[] = $attr['id'] . ' ' . $op . ' ' . $p;
            } elseif (in_array($op, [APP['op']['~'], APP['op']['!~']])
                && in_array($attr['backend'], ['int[]', 'json', 'text[]'])
            ) {
                $p = $param . ++$count;
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];
                $or[] = $attr['id'] . ' @> ' . $p . ($op === APP['op']['!~'] ? ' IS FALSE' : '');
            } elseif (in_array($op, [APP['op']['^'], APP['op']['!^'], APP['op']['$'], APP['op']['!$']])
                && in_array($attr['backend'], ['int[]', 'text[]'])
            ) {
                $n = is_array($val) ? max(0, count($val) - 1) : 0;
                $p = $param . ++$count;
                $val = val($val, $attr);
                $cols['param'][] = [$p, $val, type($val)];

                if (in_array($op, [APP['op']['$'], APP['op']['!$']])) {
                    $n = $n > 0 ? ' - ' . $n : '';
                    $l = '[array_upper(' . $attr['id'] . ', 1)' . $n . ' : array_upper(' . $attr['id'] . ', 1)]';
                } else {
                    $n = $n > 0 ? ' + ' . $n : '';
                    $l = '[array_lower(' . $attr['id'] . ', 1) : array_lower(' . $attr['id'] . ', 1)' . $n . ']';
                }

                $or[] = $attr['id'] . $l . (in_array($op, [APP['op']['!^'], APP['op']['!$']]) ? ' != ' : ' = ') . $p;
            } elseif (in_array($op, [APP['op']['~'], APP['op']['!~'], APP['op']['^'], APP['op']['!^'], APP['op']['$'], APP['op']['!$']])) {
                $not = in_array($op, [APP['op']['!~'], APP['op']['!^'], APP['op']['!$']]) ? ' NOT' : '';
                $pre = in_array($op, [APP['op']['~'], APP['op']['!~'], APP['op']['$'], APP['op']['!$']]) ? '%' : '';
                $post = in_array($op, [APP['op']['~'], APP['op']['!~'], APP['op']['^'], APP['op']['!^']]) ? '%' : '';
                $p = $param . ++$count;
                $val = val($val, $attr);
                $cols['param'][] = [
                    $p,
                    $pre . str_replace(['%', '_'], ['\%', '\_'], (string) $val) . $post,
                    PDO::PARAM_STR
                ];
                $or[] = $attr['id'] . '::text' . $not . ' ILIKE ' . $p;
            }
        }

        $cols['crit'][] = '(' . implode(' OR ', $or) . ')';
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
    $sql = '';

    foreach ($cols as $col => $val) {
        $sql .= ($sql ? ', ' : '') . $col . ' = ' . $val;
    }

    return ' SET ' . $sql;
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
function sel(array $sel): string
{
    $sql = '';

    foreach ($sel as $as => $col) {
        $sql .= ($sql ? ', ' : '') . $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $sql ? ' SELECT ' . $sql : '';
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
 * JOIN part
 *
 * @throws DomainException
 */
function join(string $type, string $tab, string $as = null, array $cols = []): string
{
    if (!$type || empty(APP['join'][$type]) || !$tab) {
        throw new DomainException(app\i18n('Invalid JOIN'));
    }

    return APP['join'][$type] . ' JOIN ' . $tab
        . ($as ? ' AS ' . $as : '')
        . ($cols ? ' ON ' . implode(' AND ', $cols) : '');
}

/**
 * GROUP BY part
 */
function group(array $cols): string
{
    return $cols ? ' GROUP BY ' . implode(', ', $cols) : '';
}

/**
 * ORDER BY part
 */
function order(array $order): string
{
    $sql = '';

    foreach ($order as $attrId => $dir) {
        $sql .= ($sql ? ', ' : '') . $attrId . ($dir === 'desc' ? ' DESC NULLS LAST' : ' ASC NULLS FIRST');
    }

    return $sql ? ' ORDER BY ' . $sql : '';
}

/**
 * LIMIT part
 */
function limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}

/**
 * WITH part
 */
function with(array $with, bool $recursive = false): string
{
    $sql = '';

    foreach ($with as $name => $part) {
        $sql .= ($sql ? ', ' : ' ') . $name . ' AS (' . $part . ')';
    }

    return 'WITH ' . ($recursive ? 'RECURSIVE ' : '') . $sql;
}

/**
 * RETURNING part
 */
function returning(array $cols): string
{
    return $cols ? ' RETURNING ' . implode(', ', $cols) : '';
}

/**
 * UNION part
 */
function union(): string
{
    RETURN ' UNION ';
}
