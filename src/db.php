<?php
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
        $dsn = sprintf('%s:host=%s;dbname=%s;charset=%s', $data['driver'], $data['host'], $data['db'], $data['charset']);
        $db = new PDO($dsn, $data['username'], $data['password'], $data['driver_options']);
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
 * Prepare statement with replacing placeholders
 *
 * @param string $sql
 * @param string[] ...$args
 *
 * @return PDOStatement
 */
function prep(string $sql, string ...$args): PDOStatement
{
    return db()->prepare(vsprintf($sql, $args));
}

/**
 * Determine appropriate DB type
 *
 * @param array $attr
 *
 * @return string
 */
function db_cast(array $attr): string
{
    switch ($attr['backend']) {
        case 'bool':
            return 'UNSIGNED';
        case 'date':
            return 'DATE';
        case 'datetime':
            return 'DATETIME';
        case 'decimal':
            return 'DECIMAL';
        case 'int':
            return 'SIGNED';
        case 'json':
            return 'JSON';
        case 'time':
            return 'TIME';
    }

    return 'CHAR';
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
 *
 * @return array
 */
function cols(array $attrs, array $item): array
{
    $data = ['col' => [], 'param' => [], 'set' => []];

    foreach (array_keys($item) as $code) {
        if (empty($attrs[$code]['col']) || $attrs[$code]['generator'] === 'auto') {
            continue;
        }

        $data['col'][$code] = $attrs[$code]['col'];
        $data['param'][$code] = ':' . str_replace('-', '_', $code);
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
        if (empty($attr['col'])) {
            continue;
        }

        $pre = strpos($attr['col'], '.') !== false ? '' : $alias;
        $post = $code !== $attr['col'] ? ' AS ' . qi($code) : '';
        $cols[$code] = $pre . $attr['col'] . $post;
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
 * NATURAL JOIN part
 *
 * @param string $table
 * @param string $alias
 *
 * @return string
 */
function njoin(string $table, string $alias): string
{
    return sprintf(' NATURAL JOIN %s %s', $table, $alias);
}

/**
 * INNER JOIN part
 *
 * @param string $table
 * @param string $alias
 * @param string[] $cols
 *
 * @return string
 */
function ijoin(string $table, string $alias, array $cols): string
{
    if (!$cols) {
        return '';
    }

    return sprintf(
        ' INNER JOIN %s %s ON %s',
        $table,
        $alias,
        stringify(' AND ', $cols, ['keys' => true, 'sep' => ' = '])
    );
}

/**
 * LEFT JOIN part
 *
 * @param string $table
 * @param string $alias
 * @param string[] $cols
 *
 * @return string
 */
function ljoin(string $table, string $alias, array $cols): string
{
    if (!$cols) {
        return '';
    }

    return sprintf(
        ' LEFT JOIN %s %s ON %s',
        $table,
        $alias,
        stringify(' AND ', $cols, ['keys' => true, 'sep' => ' = '])
    );
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
    $op = $search ? 'LIKE' : '=';

    foreach ($criteria as $code => $value) {
        if (empty($attrs[$code]['col'])) {
            continue;
        }

        $attr = $attrs[$code];
        $pre = strpos($attr['col'], '.') !== false ? '' : $alias;
        $r = [];

        foreach ((array) $value as $v) {
            if ($search) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = $pre . $attr['col'] . ' ' . $op . ' ' . qv($v, $attr['backend']);
        }

        $cols[$code] = '(' . implode(' OR ', $r) . ')';
    }

    return $cols ? ' WHERE ' . implode(' AND ', $cols) : '';
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
    return $cols ? ' GROUP BY ' . implode(', ' , $cols) : '';
}

/**
 * HAVING part
 *
 * @param array $criteria
 * @param array $attrs
 * @param array $options
 *
 * @return string
 */
function having(array $criteria, array $attrs, array $options = []): string
{
    $cols = [];
    $search = !empty($options['search']);
    $op = $search ? 'LIKE' : '=';

    foreach ($criteria as $code => $value) {
        if (empty($attrs[$code])) {
            continue;
        }

        $r = [];

        foreach ((array) $value as $v) {
            if ($search) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = qi($code) . ' ' . $op . ' ' . qv($v, $attrs[$code]['backend']);
        }

        $cols[$code] = '(' . implode(' OR ', $r) . ')';
    }

    return $cols ? ' HAVING ' . implode(' AND ', $cols) : '';
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
        if (empty($attrs[$code]['col'])) {
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
