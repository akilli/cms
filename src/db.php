<?php
namespace akilli;

use Exception;
use PDO;

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
        $db = new PDO($data['dsn'], $data['username'], $data['password'], $data['driver_options']);
    }

    return $db;
}

/**
 * Transaction
 *
 * @param callable $callback
 *
 * @return bool
 */
function trans(callable $callback): bool
{
    static $level = 0;

    if (++$level === 1) {
        db()->beginTransaction();
    } else {
        db()->exec('SAVEPOINT LEVEL_' . $level);
    }

    try {
        $callback();

        if ($level === 1) {
            db()->commit();
        } else {
            db()->exec('RELEASE SAVEPOINT LEVEL_' . $level);
        }

        --$level;
    } catch (Exception $e) {
        if ($level === 1) {
            db()->rollBack();
        } else {
            db()->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $level);
        }

        --$level;
        error($e);
        $error = true;
    }

    return empty($error);
}

/**
 * Set appropriate parameter type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return int
 */
function db_type(array $attr, $value): int
{
    if ($value === null && !empty($attr['is_nullable'])) {
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
 * Quotes value
 *
 * @param mixed $value
 * @param mixed $backend
 *
 * @return mixed
 */
function qv($value, $backend = null)
{
    if ($backend === 'bool') {
        $value = $value ? '1' : '0';
    } elseif ($backend === 'int') {
        return (int) $value;
    } elseif ($backend === 'decimal') {
        return sprintf('%F', $value);
    }

    return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
}

/**
 * Quotes identifier
 *
 * @param string $identifier
 *
 * @return string
 */
function qi(string $identifier = null): string
{
    $char = db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? '`' : '"';

    return !empty($identifier) ? $char . str_replace($char, '', $identifier) . $char : '';
}

/**
 * Prepare columns
 *
 * @param array $attrs
 * @param array $item
 * @param array $skip
 *
 * @return array
 */
function cols(array $attrs, array $item, array $skip = []): array
{
    $data = ['col' => [], 'param' => [], 'set' => []];

    foreach (array_keys($item) as $code) {
        if (empty($attrs[$code]['column']) || !empty($attrs[$code]['auto']) || in_array($code, $skip)) {
            continue;
        }

        $data['col'][$code] = $attrs[$code]['column'];
        $data['param'][$code] = ':__attribute__' . str_replace('-', '_', $code);
        $data['set'][$code] = $data['col'][$code] . ' = ' . $data['param'][$code];
    }

    return $data;
}

/**
 * Select part
 *
 * @param array $attrs
 * @param string $alias
 *
 * @return string
 */
function select(array $attrs, string $alias = null): string
{
    $columns = [];
    $alias = ($alias) ? qi($alias) . '.' : '';

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        }

        $columns[$code] = $alias . $attr['column'] . ' as ' . qi($code);
    }

    if (empty($columns)) {
        return '';
    }

    return 'SELECT ' . implode(', ', $columns);
}

/**
 * From part
 *
 * @param string $table
 * @param string $alias
 *
 * @return string
 */
function from(string $table, string $alias = null): string
{
    return ' FROM ' . $table . ($alias ? ' as ' . qi($alias) : '');
}

/**
 * Where part
 *
 * @param array $criteria
 * @param array $attrs
 * @param array $options
 *
 * @return string
 */
function where(array $criteria, array $attrs, array $options = []): string
{
    $columns = [];
    $alias = !empty($options['alias']) ? qi($options['alias']) . '.' : '';
    $search = !empty($options['search']);
    $operator = $search ? 'LIKE' : '=';

    foreach ($criteria as $code => $value) {
        if (empty($attrs[$code]['column'])) {
            continue;
        }

        $r = [];

        foreach ((array) $value as $v) {
            if ($search) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = $alias . $attrs[$code]['column'] . ' ' . $operator . ' '
                . qv($v, $attrs[$code]['backend']);
        }

        $columns[$code] = '(' . implode(' OR ', $r) . ')';
    }

    return $columns ? ' WHERE ' . implode(' AND ', $columns) : '';
}

/**
 * Order By part
 *
 * @param array $order
 * @param array $attrs
 *
 * @return string
 */
function order(array $order, array $attrs = null): string
{
    $columns = [];

    foreach ($order as $code => $direction) {
        if ($attrs !== null && empty($attrs[$code]['column'])) {
            continue;
        }

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $columns[$code] = qi($code) . ' ' . $direction;
    }

    return $columns ? ' ORDER BY ' . implode(', ', $columns) : '';
}

/**
 * Limit part
 *
 * @param int[] $limit
 *
 * @return string
 */
function limit(array $limit = null): string
{
    $limit[0] = !empty($limit[0]) ? (int) $limit[0] : 0;
    $limit[1] = !empty($limit[1]) ? (int) $limit[1] : 0;

    return $limit[0] > 0 ? ' LIMIT ' . $limit[0] . ' OFFSET ' . $limit[1] : '';
}
