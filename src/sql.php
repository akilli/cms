<?php
declare(strict_types = 1);

namespace sql;

use app;
use PDO;
use DomainException;
use Throwable;

/**
 * Database
 */
function db(string $id = 'app'): PDO
{
    static $pdo = [];

    if (empty($pdo[$id])) {
        if (!$id || !($cfg = app\cfg('sql', $id))) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $opt = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        $pdo[$id] = new PDO($cfg['dsn'], $cfg['user'], $cfg['password'], $opt);
    }

    return $pdo[$id];
}

/**
 * Transaction
 *
 * @throws Throwable
 */
function trans(callable $call): void
{
    static $level = 0;

    ++$level === 1 ? db()->beginTransaction() : db()->exec('SAVEPOINT LEVEL_' . $level);

    try {
        $call();
        $level === 1 ? db()->commit() : db()->exec('RELEASE SAVEPOINT LEVEL_' . $level);
        --$level;
    } catch (Throwable $e) {
        $level === 1 ? db()->rollBack() : db()->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $level);
        --$level;
        app\log($e);
        throw $e;
    }
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
        $val = $attrs[$attrId]['backend'] === 'json' && is_array($val) ? json_encode($val) : $val;
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
function insert(string $tab): string
{
    return 'INSERT INTO ' . $tab;
}

/**
 * VALUES part
 */
function values(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

/**
 * UPDATE part
 */
function update(string $tab): string
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
function delete(string $tab): string
{
    return 'DELETE FROM ' . $tab;
}

/**
 * SELECT part
 */
function select(array $sel, bool $distinct = false): string
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
