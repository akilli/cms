<?php
namespace qnd;

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

    ++$level === 1 ? db()->beginTransaction() : db()->exec('SAVEPOINT LEVEL_' . $level);

    try {
        $callback();
        $level === 1 ? db()->commit() : db()->exec('RELEASE SAVEPOINT LEVEL_' . $level);
        --$level;
    } catch (Exception $e) {
        $level === 1 ? db()->rollBack() : db()->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $level);
        --$level;
        error($e);

        return false;
    }

    return true;
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
    if ($value === null && !empty($attr['nullable'])) {
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
 * @param string $backend
 *
 * @return mixed
 */
function qv($value, string $backend = null)
{
    if ($backend === 'bool') {
        return $value ? '1' : '0';
    }

    if ($backend === 'int') {
        return (int) $value;
    }

    if ($backend === 'decimal') {
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

    return $identifier ? $char . str_replace($char, '', $identifier) . $char : '';
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
 * SELECT part
 *
 * @param array $attrs
 * @param string $alias
 *
 * @return string
 */
function select(array $attrs, string $alias = null): string
{
    $cols = [];
    $alias = $alias ? $alias . '.' : '';

    foreach ($attrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        }

        $cols[$code] = $alias . $attr['column'] . ($code !== $attr['column'] ? ' AS ' . qi($code) : '');
    }

    return $cols ? 'SELECT ' . implode(', ', $cols) : '';
}

/**
 * FROM part
 *
 * @param string $table
 * @param string $alias
 *
 * @return string
 */
function from(string $table, string $alias = null): string
{
    return ' FROM ' . $table . ($alias ? ' ' . $alias : '');
}

/**
 * JOIN part
 *
 * @param string $table
 * @param string $alias
 * @param array $cols
 *
 * @return string
 */
function join(string $table, string $alias, array $cols): string
{
    $cond = stringify(' AND ', $cols, ['keys' => true, 'sep' => ' = ']);

    return $cond ? ' INNER JOIN ' . $table . ' ' . $alias . ' ON ' . $cond : '';
}

/**
 * WHERE part
 *
 * @param array $criteria
 * @param array $attrs
 * @param array $options
 *
 * @return string
 */
function where(array $criteria, array $attrs, array $options = []): string
{
    $cols = [];
    $alias = !empty($options['alias']) ? $options['alias'] . '.' : '';
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

            $r[] = $alias . $attrs[$code]['column'] . ' ' . $operator . ' ' . qv($v, $attrs[$code]['backend']);
        }

        $cols[$code] = '(' . implode(' OR ', $r) . ')';
    }

    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
}

/**
 * ORDER BY part
 *
 * @param string[] $order
 * @param array $attrs
 *
 * @return string
 */
function order(array $order, array $attrs = []): string
{
    $cols = [];

    foreach ($order as $code => $dir) {
        if ($attrs !== null && empty($attrs[$code]['column'])) {
            continue;
        }

        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $cols[$code] = qi($code) . ' ' . $dir;
    }

    return $cols ? ' ORDER BY ' . implode(', ', $cols) : '';
}

/**
 * LIMIT part
 *
 * @param int[] $limit
 *
 * @return string
 */
function limit(array $limit): string
{
    $limit[0] = intval($limit[0] ?? 0);
    $limit[1] = intval($limit[1] ?? 0);

    return $limit[0] > 0 ? ' LIMIT ' . $limit[0] . ' OFFSET ' . $limit[1] : '';
}
