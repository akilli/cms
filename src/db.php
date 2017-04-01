<?php
declare(strict_types = 1);

namespace qnd;

use Exception;
use PDO;
use PDOStatement;
use RuntimeException;

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
 * @param array $data
 *
 * @return array
 */
function db_cols(array $attrs, array $data): array
{
    $attrs = db_attr($attrs, true);
    $cols = ['in' => [], 'up' => [], 'param' => []];

    foreach (array_intersect_key($data, $attrs) as $aId => $val) {
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
 *
 * @return array
 */
function db_crit(array $crit, array $attrs): array
{
    $cols = [];

    foreach ($crit as $item) {
        $attr = $attrs[$item[0]] ?? null;
        $val = $item[1] ?? null;
        $op = $item[2] ?? CRIT['='];

        if (!$attr || empty(CRIT[$op])) {
            throw new RuntimeException(_('Invalid criteria'));
        }

        if (is_array($val) && !$val) {
            continue;
        }

        $val = is_array($val) ? $val : [$val];
        $r = [];

        switch ($op) {
            case CRIT['=']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ($v === null ? ' IS NULL' : ' = ' . db_qv($v, $attr));
                }
                break;
            case CRIT['!=']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ($v === null ? ' IS NOT NULL' : ' != ' . db_qv($v, $attr));
                }
                break;
            case CRIT['>']:
            case CRIT['>=']:
            case CRIT['<']:
            case CRIT['<=']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' ' . $op . ' ' . db_qv($v, $attr);
                }
                break;
            case CRIT['~']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' ILIKE ' . db_qv('%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%', $attr);
                }
                break;
            case CRIT['!~']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' NOT ILIKE ' . db_qv('%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%', $attr);
                }
                break;
            case CRIT['~^']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' ILIKE ' . db_qv(str_replace(['%', '_'], ['\%', '\_'], $v) . '%', $attr);
                }
                break;
            case CRIT['!~^']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' NOT ILIKE ' . db_qv(str_replace(['%', '_'], ['\%', '\_'], $v) . '%', $attr);
                }
                break;
            case CRIT['~$']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' ILIKE ' . '%' . db_qv(str_replace(['%', '_'], ['\%', '\_'], $v), $attr);
                }
                break;
            case CRIT['!~$']:
                foreach ($val as $v) {
                    $r[] = $attr['col'] . ' NOT ILIKE ' . '%' . db_qv(str_replace(['%', '_'], ['\%', '\_'], $v), $attr);
                }
                break;
            case CRIT['@@']:
            case CRIT['!!']:
                $r[] = $attr['col'] . ' ' . $op . ' TO_TSQUERY(' . db_qv(implode(' | ', $val), $attr) . ')';
                break;
        }

        $cols[] = db_or($r);
    }

    return $cols;
}

/**
 * Quotes value
 *
 * @param mixed $val
 * @param array $attr
 *
 * @return string|null
 */
function db_qv($val, array $attr): ?string
{
    if ($attr['nullable'] && $val === null) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return $val ? 'TRUE' : 'FALSE';
    }

    if ($attr['backend'] === 'int') {
        return (string) $val;
    }

    if ($attr['backend'] === 'decimal') {
        return sprintf('%F', $val);
    }

    return db()->quote($val, db_pdo($val, $attr));
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
