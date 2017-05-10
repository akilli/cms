<?php
declare(strict_types = 1);

namespace qnd;

use Exception;
use PDO;
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
        logger((string) $e);

        return false;
    }

    return true;
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
    $cols = ['param' => [], 'val' => []];

    foreach (array_intersect_key($data, $attrs) as $aId => $val) {
        $attr = $attrs[$aId];
        $p = ':' . $attr['id'];
        $val = $attr['multiple'] && $attr['backend'] === 'json' ? json_encode($val) : $val;
        $cols['param'][$attr['col']] = [$p, $val, $attr['nullable'] && $val === null ? PDO::PARAM_NULL : $attr['pdo']];
        $cols['val'][$attr['col']] = $attr['backend'] === 'search' ? 'TO_TSVECTOR(' . $p . ')' : $p;
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
    $cols = ['where' => [], 'param' => []];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $o = [];

        foreach ($part as $c) {
            $attr = $attrs[$c[0]] ?? null;
            $val = $c[1] ?? null;
            $op = $c[2] ?? CRIT['='];

            if (!$attr || empty(CRIT[$op]) || is_array($val) && !$val) {
                throw new RuntimeException(_('Invalid criteria'));
            }

            $param = ':crit_' . $attr['id'] . '_';
            $i = 0;
            $val = is_array($val) ? $val : [$val];
            $r = [];

            switch ($op) {
                case CRIT['=']:
                case CRIT['!=']:
                    $null = ' IS' . ($op === CRIT['!='] ? ' NOT' : '') . ' NULL';

                    foreach ($val as $v) {
                        $p = $param . ++$i;
                        $cols['param'][] = [$p, $v, $attr['pdo']];
                        $r[] = $attr['col'] . ($v === null ? $null : ' ' . $op . ' ' . $p);
                    }
                    break;
                case CRIT['>']:
                case CRIT['>=']:
                case CRIT['<']:
                case CRIT['<=']:
                    foreach ($val as $v) {
                        $p = $param . ++$i;
                        $cols['param'][] = [$p, $v, $attr['pdo']];
                        $r[] = $attr['col'] . ' ' . $op . ' ' . $p;
                    }
                    break;
                case CRIT['~']:
                case CRIT['!~']:
                case CRIT['~^']:
                case CRIT['!~^']:
                case CRIT['~$']:
                case CRIT['!~$']:
                    if ($attr['backend'] === 'search') {
                        $op = in_array($op, [CRIT['!~'], CRIT['!~^'], CRIT['!~$']]) ? '!!' : '@@';
                        $p = $param . ++$i;
                        $val = array_map(
                            function ($v) {
                                return str_replace(['&', '|'], '', $v);
                            },
                            $val
                        );
                        $cols['param'][] = [$p, implode(' | ', array_filter($val)), $attr['pdo']];
                        $r[] = $attr['col'] . ' ' . $op . ' TO_TSQUERY(' . $p . ')';
                    } else {
                        $not = in_array($op, [CRIT['!~'], CRIT['!~^'], CRIT['!~$']]) ? ' NOT' : '';
                        $pre = in_array($op, [CRIT['~'], CRIT['!~'], CRIT['~$'], CRIT['!~$']]) ? '%' : '';
                        $post = in_array($op, [CRIT['~'], CRIT['!~'], CRIT['~^'], CRIT['!~^']]) ? '%' : '';

                        foreach ($val as $v) {
                            $p = $param . ++$i;
                            $cols['param'][] = [$p, $pre . str_replace(['%', '_'], ['\%', '\_'], $v) . $post, $attr['pdo']];
                            $r[] = $attr['col'] . $not . ' ILIKE ' . $p;
                        }
                    }
                    break;
                default:
                    throw new RuntimeException(_('Invalid criteria'));
            }

            $o[] = implode(' OR ', $r);
        }

        $cols['where'][] = '(' . implode(' OR ', $o) . ')';
    }

    return $cols;
}

/**
 * INSERT part
 *
 * @param string $tab
 *
 * @return string
 */
function db_insert(string $tab): string
{
    return 'INSERT INTO ' . $tab;
}

/**
 * VALUES part
 *
 * @param array $cols
 *
 * @return string
 */
function db_values(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

/**
 * UPDATE part
 *
 * @param string $tab
 *
 * @return string
 */
function db_update(string $tab): string
{
    return 'UPDATE ' . $tab;
}

/**
 * SET part
 *
 * @param array $cols
 *
 * @return string
 */
function db_set(array $cols): string
{
    $set = '';

    foreach ($cols as $col => $val) {
        $set .= ($set ? ', ' : '') . $col . ' = ' . $val;
    }

    return ' SET ' . $set;
}

/**
 * DELETE part
 *
 * @param string $tab
 *
 * @return string
 */
function db_delete(string $tab): string
{
    return 'DELETE FROM ' . $tab;
}

/**
 * SELECT part
 *
 * @param array $sel
 *
 * @return string
 */
function db_select(array $sel): string
{
    $cols = [];

    foreach ($sel as $as => $col) {
        $cols[] = $col . ($as && is_string($as) ? ' AS ' . $as : '');
    }

    return $cols ? ' SELECT ' . implode(', ', $cols) : '';
}

/**
 * FROM part
 *
 * @param string $tab
 *
 * @return string
 */
function db_from(string $tab): string
{
    return ' FROM ' . $tab;
}

/**
 * WHERE part
 *
 * @param array $cols
 *
 * @return string
 */
function db_where(array $cols): string
{
    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

/**
 * ORDER BY part
 *
 * @param string[] $order
 *
 * @return string
 */
function db_order(array $order): string
{
    $cols = [];

    foreach ($order as $aId => $dir) {
        $cols[] = $aId . ' ' . ($dir === 'desc' ? 'DESC' : 'ASC');
    }

    return $cols ? ' ORDER BY ' . implode(', ', $cols) : '';
}

/**
 * LIMIT part
 *
 * @param int $limit
 * @param int $offset
 *
 * @return string
 */
function db_limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}
