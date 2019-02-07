<?php
declare(strict_types = 1);

namespace sql;

use app;
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

    $cols = crit($crit);
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
        $p = ':' . $attrId;

        if (is_array($val) && $attrs[$attrId]['backend'] === 'json') {
            $val = json_encode($val);
        } elseif ($attrs[$attrId]['multiple']) {
            $val = '{' . (is_array($val) ? implode($val, ',') : $val) . '}';
        }

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
 * Generates criteria
 *
 * @throws DomainException
 */
function crit(array $crit): array
{
    static $count = [];

    $cols = ['crit' => [], 'param' => []];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $o = [];

        foreach ($part as $c) {
            $attrId = $c[0];
            $val = $c[1] ?? null;
            $op = $c[2] ?? APP['crit']['='];
            $isCol = !empty($c[3]);

            if (!$attrId || empty(APP['crit'][$op]) || is_array($val) && !$val) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $param = ':crit_' . $attrId . '_';
            $type = type($val);
            $count[$attrId] = $count[$attrId] ?? 0;
            $val = is_array($val) ? $val : [$val];
            $r = [];

            switch ($op) {
                case APP['crit']['=']:
                case APP['crit']['!=']:
                case APP['crit']['>']:
                case APP['crit']['>=']:
                case APP['crit']['<']:
                case APP['crit']['<=']:
                    $null = null;

                    if (in_array($op, [APP['crit']['='], APP['crit']['!=']])) {
                        $null = ' IS' . ($op === APP['crit']['!='] ? ' NOT' : '') . ' NULL';
                    }

                    foreach ($val as $v) {
                        if ($null && $v === null) {
                            $r[] = $attrId . $null;
                        } elseif ($isCol) {
                            $r[] = $attrId . ' ' . $op . ' ' . $v;
                        } else {
                            $p = $param . ++$count[$attrId];
                            $cols['param'][] = [$p, $v, $type];
                            $r[] = $attrId . ' ' . $op . ' ' . $p;
                        }
                    }
                    break;
                case APP['crit']['~']:
                case APP['crit']['!~']:
                case APP['crit']['~^']:
                case APP['crit']['!~^']:
                case APP['crit']['~$']:
                case APP['crit']['!~$']:
                    $not = in_array($op, [APP['crit']['!~'], APP['crit']['!~^'], APP['crit']['!~$']]) ? ' NOT' : '';
                    $pre = in_array($op, [APP['crit']['~'], APP['crit']['!~'], APP['crit']['~$'], APP['crit']['!~$']]) ? '%' : '';
                    $post = in_array($op, [APP['crit']['~'], APP['crit']['!~'], APP['crit']['~^'], APP['crit']['!~^']]) ? '%' : '';

                    foreach ($val as $v) {
                        if ($isCol) {
                            $r[] = $attrId . $not . ' ILIKE ' . $v;
                        } else {
                            $p = $param . ++$count[$attrId];
                            $cols['param'][] = [$p, $pre . str_replace(['%', '_'], ['\%', '\_'], $v) . $post, PDO::PARAM_STR];
                            $r[] = $attrId . $not . ' ILIKE ' . $p;
                        }
                    }
                    break;
                default:
                    throw new DomainException(app\i18n('Invalid criteria'));
            }

            $o[] = implode(' OR ', $r);
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
 * JOIN part
 *
 * @throws DomainException
 */
function join(string $tab, string $as = null, array $cols = [], string $type = null): string
{
    if (!$tab) {
        return '';
    }

    if ($type && empty(APP['join'][$type])) {
        throw new DomainException(app\i18n('Invalid JOIN-type'));
    }

    if ($cols) {
        $pre = '';
        $post = ' ON ' . implode(' AND ', $cols);
    } else {
        $pre = ' NATURAL';
        $post = '';
    }

    if ($type) {
        $pre .= ' ' . strtoupper(APP['join'][$type]);
    }

    return $pre . ' JOIN ' . $tab . ($as ? ' AS ' . $as : '') . $post;
}

/**
 * INNER JOIN part
 */
function ijoin(string $tab, string $as = null, array $cols = []): string
{
    return join($tab, $as, $cols, APP['join']['inner']);
}

/**
 * LEFT JOIN part
 */
function ljoin(string $tab, string $as = null, array $cols = []): string
{
    return join($tab, $as, $cols, APP['join']['left']);
}

/**
 * RIGHT JOIN part
 */
function rjoin(string $tab, string $as = null, array $cols = []): string
{
    return join($tab, $as, $cols, APP['join']['right']);
}

/**
 * FULL JOIN part
 */
function fjoin(string $tab, string $as = null, array $cols = []): string
{
    return join($tab, $as, $cols, APP['join']['full']);
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

/**
 * WITH part
 */
function with(string $name, string $sql, bool $recursive = false): string
{
    return 'WITH ' . ($recursive ? 'RECURSIVE ' : '') . $name . ' AS (' . $sql . ')';
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
function union (): string
{
    RETURN ' UNION ';
}
