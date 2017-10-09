<?php
declare(strict_types = 1);

namespace cms;

use Exception;
use PDO;
use RuntimeException;

/**
 * Database
 */
function db(): PDO
{
    static $db;

    if ($db === null) {
        $cfg = cfg('db');
        $db = new PDO($cfg['dsn'], $cfg['user'], $cfg['password'], $cfg['opt']);
    }

    return $db;
}

/**
 * Transaction
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
 * Set appropriate data type
 */
function db_type(array $attr, $val): int
{
    if ($attr['nullable'] && $val === null) {
        return PDO::PARAM_NULL;
    }

    if ($attr['backend'] === 'bool') {
        return PDO::PARAM_BOOL;
    }

    if ($attr['backend'] === 'int' || $attr['backend'] === 'decimal') {
        return PDO::PARAM_INT;
    }

    return PDO::PARAM_STR;
}

/**
 * Prepare columns
 */
function db_cols(array $attrs, array $data): array
{
    $attrs = db_attr($attrs, true);
    $cols = ['param' => [], 'val' => []];

    foreach (array_intersect_key($data, $attrs) as $aId => $val) {
        $attr = $attrs[$aId];
        $p = ':' . $attr['id'];
        $val = is_array($val) && $attr['backend'] === 'json' ? json_encode($val) : $val;
        $cols['param'][$attr['col']] = [$p, $val, db_type($attr, $val)];
        $cols['val'][$attr['col']] = $attr['backend'] === 'search' ? 'TO_TSVECTOR(' . $p . ')' : $p;
    }

    return $cols;
}

/**
 * Filter out non-DB and optionally auto increment columns
 */
function db_attr(array $attrs, bool $auto = false): array
{
    foreach ($attrs as $aId => $attr) {
        if (empty($attr['col']) || $auto && !empty($attr['auto'])) {
            unset($attrs[$aId]);
        }
    }

    return $attrs;
}

/**
 * Maps attribute IDs to DB columns and handles search criteria
 */
function db_crit(array $crit, array $attrs): array
{
    $cols = ['where' => [], 'param' => []];

    foreach ($crit as $part) {
        $part = is_array($part[0]) ? $part : [$part];
        $o = [];
        $z = [];

        foreach ($part as $c) {
            $attr = $attrs[$c[0]] ?? null;
            $val = $c[1] ?? null;
            $op = $c[2] ?? CRIT['='];

            if (!$attr || empty(CRIT[$op]) || is_array($val) && !$val) {
                throw new RuntimeException(_('Invalid criteria'));
            }

            $param = ':crit_' . $attr['id'] . '_';
            $type = db_type($attr, $val);
            $z[$attr['id']] = $z[$attr['id']] ?? 0;
            $val = is_array($val) ? $val : [$val];
            $r = [];

            switch ($op) {
                case CRIT['=']:
                case CRIT['!=']:
                    $null = ' IS' . ($op === CRIT['!='] ? ' NOT' : '') . ' NULL';

                    foreach ($val as $v) {
                        if ($v === null) {
                            $r[] = $attr['col'] . $null;
                        } else {
                            $p = $param . ++$z[$attr['id']];
                            $cols['param'][] = [$p, $v, $type];
                            $r[] = $attr['col'] . ' ' . $op . ' ' . $p;
                        }
                    }
                    break;
                case CRIT['>']:
                case CRIT['>=']:
                case CRIT['<']:
                case CRIT['<=']:
                    foreach ($val as $v) {
                        $p = $param . ++$z[$attr['id']];
                        $cols['param'][] = [$p, $v, $type];
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

                        foreach ($val as $k => $v) {
                            $v = filter_param($v);

                            if (!$v) {
                                unset($val[$k]);
                            } else {
                                $val[$k] = $v;
                            }
                        }

                        $p = $param . ++$z[$attr['id']];
                        $cols['param'][] = [$p, implode(' | ', $val), PDO::PARAM_STR];
                        $r[] = $attr['col'] . ' ' . $op . ' TO_TSQUERY(' . $p . ')';
                    } else {
                        $not = in_array($op, [CRIT['!~'], CRIT['!~^'], CRIT['!~$']]) ? ' NOT' : '';
                        $pre = in_array($op, [CRIT['~'], CRIT['!~'], CRIT['~$'], CRIT['!~$']]) ? '%' : '';
                        $post = in_array($op, [CRIT['~'], CRIT['!~'], CRIT['~^'], CRIT['!~^']]) ? '%' : '';

                        foreach ($val as $v) {
                            $p = $param . ++$z[$attr['id']];
                            $cols['param'][] = [$p, $pre . str_replace(['%', '_'], ['\%', '\_'], $v) . $post, PDO::PARAM_STR];
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
 */
function db_insert(string $tab): string
{
    return 'INSERT INTO ' . $tab;
}

/**
 * VALUES part
 */
function db_values(array $cols): string
{
    return ' (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', $cols) . ')';
}

/**
 * UPDATE part
 */
function db_update(string $tab): string
{
    return 'UPDATE ' . $tab;
}

/**
 * SET part
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
 */
function db_delete(string $tab): string
{
    return 'DELETE FROM ' . $tab;
}

/**
 * SELECT part
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
 */
function db_from(string $tab): string
{
    return ' FROM ' . $tab;
}

/**
 * WHERE part
 */
function db_where(array $cols): string
{
    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

/**
 * ORDER BY part
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
 */
function db_limit(int $limit, int $offset = 0): string
{
    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset) : '';
}
