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
function db(): PDO
{
    static $pdo;

    if ($pdo === null) {
        $cfg = app\cfg('sql');
        $opt = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        $pdo = new PDO($cfg['dsn'], $cfg['user'], $cfg['password'], $opt);
    }

    return $pdo;
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
function cols(array $attrs, array $data): array
{
    $attrs = attr($attrs, true);
    $cols = ['param' => [], 'val' => []];

    foreach (array_intersect_key($data, $attrs) as $aId => $val) {
        $p = ':' . $aId;
        $val = $attrs[$aId]['backend'] === 'json' ? json_encode($val) : $val;
        $cols['param'][$aId] = [$p, $val, type($val)];
        $cols['val'][$aId] = $p;
    }

    return $cols;
}

/**
 * Filter out non-DB and optionally auto increment columns
 */
function attr(array $attrs, bool $auto = false): array
{
    foreach ($attrs as $aId => $attr) {
        if ($attr['virtual'] || $auto && $attr['auto']) {
            unset($attrs[$aId]);
        }
    }

    return $attrs;
}

/**
 * Generates criteria for WHERE part
 */
function crit(array $crit): array
{
    $cols = ['where' => [], 'param' => []];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $o = [];
        $z = [];

        foreach ($part as $c) {
            $aId = $c[0];
            $val = $c[1] ?? null;
            $op = $c[2] ?? APP['crit']['='];

            if (!$aId || empty(APP['crit'][$op]) || is_array($val) && !$val) {
                throw new DomainException(app\i18n('Invalid criteria'));
            }

            $param = ':crit_' . $aId . '_';
            $type = type($val);
            $z[$aId] = $z[$aId] ?? 0;
            $val = is_array($val) ? $val : [$val];
            $r = [];

            switch ($op) {
                case APP['crit']['=']:
                case APP['crit']['!=']:
                    $null = ' IS' . ($op === APP['crit']['!='] ? ' NOT' : '') . ' NULL';

                    foreach ($val as $v) {
                        if ($v === null) {
                            $r[] = $aId . $null;
                        } else {
                            $p = $param . ++$z[$aId];
                            $cols['param'][] = [$p, $v, $type];
                            $r[] = $aId . ' ' . $op . ' ' . $p;
                        }
                    }
                    break;
                case APP['crit']['>']:
                case APP['crit']['>=']:
                case APP['crit']['<']:
                case APP['crit']['<=']:
                    foreach ($val as $v) {
                        $p = $param . ++$z[$aId];
                        $cols['param'][] = [$p, $v, $type];
                        $r[] = $aId . ' ' . $op . ' ' . $p;
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
                        $p = $param . ++$z[$aId];
                        $cols['param'][] = [$p, $pre . str_replace(['%', '_'], ['\%', '\_'], $v) . $post, PDO::PARAM_STR];
                        $r[] = $aId . $not . ' ILIKE ' . $p;
                    }
                    break;
                default:
                    throw new DomainException(app\i18n('Invalid criteria'));
            }

            $o[] = implode(' OR ', $r);
        }

        $cols['where'][] = '(' . implode(' OR ', $o) . ')';
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
function select(array $sel): string
{
    $cols = [];

    foreach ($sel as $as => $col) {
        $cols[] = $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $cols ? ' SELECT ' . implode(', ', $cols) : '';
}

/**
 * FROM part
 */
function from(string $tab): string
{
    return ' FROM ' . $tab;
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
 */
function join(string $tab): string
{
    return ' JOIN ' . $tab . ' USING (id)';
}

/**
 * ORDER BY part
 */
function order(array $order): string
{
    $cols = [];

    foreach ($order as $aId => $dir) {
        $cols[] = $aId . ' ' . ($dir === 'desc' ? 'DESC' : 'ASC');
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
