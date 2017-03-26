<?php
declare(strict_types = 1);

namespace qnd;

use Exception;
use PDO;
use PDOStatement;

/**
 * Database
 *
 * @return PDO
 */
function db(): PDO
{
    static $db;

    if ($db === null) {
        $data = data('db');
        $dsn = sprintf('pgsql:host=%s;dbname=%s', $data['host'], $data['db']);
        $db = new PDO($dsn, $data['user'], $data['password'], $data['opt']);
    }

    return $db;
}

/**
 * Transaction
 *
 * @param callable $call
 *
 * @return bool
 */
function db_trans(callable $call): bool
{
    static $level = 0;

    ++$level === 1 ? db()->beginTransaction() : db()->exec('SAVEPOINT LEVEL_' . $level);

    try {
        $call();
        $level === 1 ? db()->commit() : db()->exec('RELEASE SAVEPOINT LEVEL_' . $level);
        --$level;
    } catch (Exception $e) {
        $level === 1 ? db()->rollBack() : db()->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $level);
        --$level;
        error((string) $e);

        return false;
    }

    return true;
}

/**
 * Prepare statement with replacing placeholders
 *
 * @param string $sql
 * @param string[] ...$args
 *
 * @return PDOStatement
 */
function db_prep(string $sql, string ...$args): PDOStatement
{
    return db()->prepare(vsprintf($sql, $args));
}

/**
 * Set appropriate PDO parameter type
 *
 * @param mixed $val
 * @param array $attr
 *
 * @return int
 */
function db_pdo($val, array $attr): int
{
    return $attr['nullable'] && $val === null ? PDO::PARAM_NULL : $attr['pdo'];
}

/**
 * Prepare columns
 *
 * @param array $attrs
 * @param array $item
 *
 * @return array
 */
function db_cols(array $attrs, array $item): array
{
    $attrs = db_attr($attrs, true);
    $cols = ['in' => [], 'up' => [], 'param' => []];

    foreach (array_intersect_key($item, $attrs) as $aId => $val) {
        $col = $attrs[$aId]['col'];
        $param = ':' . $aId;
        $val = $attrs[$aId]['multiple'] && $attrs[$aId]['backend'] === 'json' ? json_encode($val) : $val;
        $cols['in'][$col] = $attrs[$aId]['backend'] === 'search' ? 'TO_TSVECTOR(' . $param . ')' : $param;
        $cols['up'][$col] = $col . ' = ' . $cols['in'][$col];
        $cols['param'][$col] = [$param, $val, db_pdo($val, $attrs[$aId])];
    }

    return $cols;
}

/**
 * Filter out non-DB and optionally auto increment columns
 *
 * @param array $attrs
 * @param bool $auto
 *
 * @return array
 */
function db_attr(array $attrs, bool $auto = false): array
{
    return array_filter(
        $attrs,
        function (array $attr) use ($auto) {
            return !empty($attr['col']) && (!$auto || empty($attr['auto']));
        }
    );
}

/**
 * Maps attribute IDs to DB columns and handles search criteria
 *
 * @param array $crit
 * @param array $attrs
 * @param array $opts
 *
 * @return array
 */
function db_crit(array $crit, array $attrs, array $opts = []): array
{
    $search = !empty($opts['search']) && is_array($opts['search']) ? $opts['search'] : [];
    $cols = [];

    foreach ($crit as $aId => $val) {
        $attr = $attrs[$aId];
        $col = $attr['col'];

        if ($val === null) {
            $cols[$aId] = db_null($col);
            continue;
        } elseif (is_array($val) && !$val) {
            continue;
        }

        $val = (array) $val;
        $r = [];

        if (!in_array($aId, $search)) {
            $r[] = db_eq($col, db_qva($val, $attr));
        } elseif ($attr['backend'] === 'search') {
            $r[] = db_search($col, db_qv(implode(' | ', $val), $attr));
        } else {
            foreach ($val as $v) {
                $r[] = db_like($col, db_qv('%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%', $attr));
            }
        }

        $cols[$aId] = db_or($r);
    }

    return $cols;
}

/**
 * Quotes value
 *
 * @param mixed $value
 * @param array $attr
 *
 * @return string
 */
function db_qv($value, array $attr): string
{
    if ($attr['backend'] === 'bool') {
        return $value ? 'TRUE' : 'FALSE';
    }

    if ($attr['backend'] === 'int') {
        return (string) $value;
    }

    if ($attr['backend'] === 'decimal') {
        return sprintf('%F', $value);
    }

    return db()->quote($value, db_pdo($value, $attr));
}

/**
 * Quotes array value
 *
 * @param array $value
 * @param array $attr
 *
 * @return array
 */
function db_qva(array $value, array $attr): array
{
    return array_map(
        function ($v) use ($attr) {
            return db_qv($v, $attr);
        },
        $value
    );
}

/**
 * AND expression
 *
 * @param array $crit
 *
 * @return string
 */
function db_and(array $crit): string
{
    return implode(' AND ', $crit);
}

/**
 * OR expression
 *
 * @param array $crit
 *
 * @return string
 */
function db_or(array $crit): string
{
    return '(' . implode(' OR ', $crit) . ')';
}

/**
 * IS NULL expression
 *
 * @param string $col
 *
 * @return string
 */
function db_null(string $col): string
{
    return $col . ' IS NULL';
}

/**
 * Equal expression
 *
 * @param string $col
 * @param array $vals
 *
 * @return string
 */
function db_eq(string $col, array $vals): string
{
    return $col . (count($vals) === 1 ? ' = ' . current($vals) : ' IN (' . db_list($vals) . ')');
}

/**
 * LIKE expression
 *
 * @param string $col
 * @param string $val
 *
 * @return string
 */
function db_like(string $col, string $val): string
{
    return $col . ' ILIKE ' . $val;
}

/**
 * SEARCH expression
 *
 * @param string $col
 * @param string $val
 *
 * @return string
 */
function db_search(string $col, string $val): string
{
    return $col . ' @@ TO_TSQUERY(' . $val . ')';
}

/**
 * List columns
 *
 * @param array $cols
 *
 * @return string
 */
function db_list(array $cols): string
{
    return implode(', ', $cols);
}

/**
 * SELECT part
 *
 * @param array $select
 *
 * @return string
 */
function select(array $select): string
{
    $cols = [];

    foreach ($select as $as => $col) {
        $cols[] = $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $cols ? 'SELECT ' . db_list($cols) : '';
}

/**
 * FROM part
 *
 * @param string $tab
 *
 * @return string
 */
function from(string $tab): string
{
    return ' FROM ' . $tab;
}

/**
 * NATURAL JOIN part
 *
 * @param string $tab
 *
 * @return string
 */
function njoin(string $tab): string
{
    return ' NATURAL JOIN ' . $tab;
}

/**
 * INNER JOIN part
 *
 * @param string $tab
 * @param array $cond
 *
 * @return string
 */
function ijoin(string $tab, array $cond): string
{
    return ' INNER JOIN ' . $tab . ' ON ' . db_and($cond);
}

/**
 * LEFT JOIN part
 *
 * @param string $tab
 * @param array $cond
 *
 * @return string
 */
function ljoin(string $tab, array $cond): string
{
    return ' LEFT JOIN ' . $tab . ' ON ' . db_and($cond);
}

/**
 * RIGHT JOIN part
 *
 * @param string $tab
 * @param array $cond
 *
 * @return string
 */
function rjoin(string $tab, array $cond): string
{
    return ' RIGHT JOIN ' . $tab . ' ON ' . db_and($cond);
}

/**
 * WHERE part
 *
 * @param array $cols
 *
 * @return string
 */
function where(array $cols): string
{
    return $cols ? ' WHERE ' . db_and($cols) : '';
}

/**
 * GROUP BY part
 *
 * @param string[] $cols
 *
 * @return string
 */
function group(array $cols): string
{
    return ' GROUP BY ' . db_list($cols);
}

/**
 * ORDER BY part
 *
 * @param string[] $order
 *
 * @return string
 */
function order(array $order): string
{
    $cols = [];

    foreach ($order as $aId => $dir) {
        $cols[] = $aId . ' ' . ($dir === 'desc' ? 'DESC' : 'ASC');
    }

    return $cols ? ' ORDER BY ' . db_list($cols) : '';
}

/**
 * LIMIT part
 *
 * @param int $limit
 * @param int $offset
 *
 * @return string
 */
function limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}
