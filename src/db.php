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
 * Set appropriate parameter type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return int
 */
function db_type(array $attr, $value): int
{
    return $value === null && !empty($attr['nullable']) ? PDO::PARAM_NULL : $attr['db_type'];
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

    foreach (array_keys($item) as $uid) {
        if (empty($attrs[$uid]['col']) || $attrs[$uid]['generator'] === 'auto') {
            continue;
        }

        $data['col'][$uid] = $attrs[$uid]['col'];
        $data['param'][$uid] = ':' . str_replace('-', '_', $uid);
        $data['set'][$uid] = $data['col'][$uid] . ' = ' . $data['param'][$uid];
    }

    return $data;
}

/**
 * SELECT part
 *
 * @param array $attrs
 * @param string $as
 *
 * @return string
 */
function select(array $attrs, string $as = null): string
{
    $cols = [];
    $as = $as ? $as . '.' : '';

    foreach ($attrs as $uid => $attr) {
        if (empty($attr['col'])) {
            continue;
        }

        $pre = strpos($attr['col'], '.') !== false ? '' : $as;
        $post = $uid !== $attr['col'] ? ' AS ' . qi($uid) : '';
        $cols[$uid] = $pre . $attr['col'] . $post;
    }

    return $cols ? 'SELECT ' . implode(', ', $cols) : '';
}

/**
 * FROM part
 *
 * @param string $tab
 * @param string $as
 *
 * @return string
 */
function from(string $tab, string $as = null): string
{
    return ' FROM ' . $tab . ($as ? ' ' . $as : '');
}

/**
 * NATURAL JOIN part
 *
 * @param string $tab
 * @param string $as
 *
 * @return string
 */
function njoin(string $tab, string $as): string
{
    return sprintf(' NATURAL JOIN %s %s', $tab, $as);
}

/**
 * WHERE part
 *
 * @param array $crit
 * @param array $attrs
 * @param array $opts
 *
 * @return string
 */
function where(array $crit, array $attrs, array $opts = []): string
{
    $cols = [];
    $as = !empty($opts['as']) ? $opts['as'] . '.' : '';

    foreach ($crit as $id => $value) {
        if (empty($attrs[$id]['col'])) {
            continue;
        }

        $pre = strpos($attrs[$id]['col'], '.') !== false ? '' : $as;

        if ($attrs[$id]['nullable'] && $value === null) {
            $cols[$id] = '(' . $pre . $attrs[$id]['col'] . ' IS NULL)';
            continue;
        }

        $op = !empty($opts['search']) && in_array($attrs[$id]['backend'], ['varchar', 'text']) ? 'LIKE' : '=';
        $r = [];

        foreach ((array) $value as $v) {
            if ($op === 'LIKE') {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = $pre . $attrs[$id]['col'] . ' ' . $op . ' ' . qv($v, $attrs[$id]['backend']);
        }

        $cols[$id] = '(' . implode(' OR ', $r) . ')';
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
 * @param array $crit
 * @param array $attrs
 * @param array $opts
 *
 * @return string
 */
function having(array $crit, array $attrs, array $opts = []): string
{
    $cols = [];

    foreach ($crit as $id => $value) {
        if (empty($attrs[$id])) {
            continue;
        }

        if ($attrs[$id]['nullable'] && $value === null) {
            $cols[$id] = '(' . qi($id) . ' IS NULL)';
            continue;
        }

        $op = !empty($opts['search']) && in_array($attrs[$id]['backend'], ['varchar', 'text']) ? 'LIKE' : '=';
        $r = [];

        foreach ((array) $value as $v) {
            if ($op === 'LIKE') {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = qi($id) . ' ' . $op . ' ' . qv($v, $attrs[$id]['backend']);
        }

        $cols[$id] = '(' . implode(' OR ', $r) . ')';
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

    foreach ($order as $uid => $dir) {
        if (empty($attrs[$uid]['col'])) {
            continue;
        }

        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $cols[$uid] = qi($uid) . ' ' . $dir;
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
function limit(int $limit, int $offset = 0): string
{
    $offset = $offset >= 0 ? $offset : 0;

    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . $offset : '';
}
