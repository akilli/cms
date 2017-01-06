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
        $dsn = sprintf('pgsql:host=%s;dbname=%s', $data['host'], $data['db']);
        $db = new PDO($dsn, $data['user'], $data['password'], $data['opt']);
    }

    return $db;
}

/**
 * @return void
 */
function db_setup(): void
{
    db()->exec(file_get_contents(path('sql', 'pg.sql')));
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
 * Parameter name
 *
 * @param string $name
 *
 * @return string
 */
function db_param(string $name): string
{
    return ':' . str_replace('-', '_', $name);
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
        return $value ? 'TRUE' : 'FALSE';
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
 * @param string $id
 *
 * @return string
 */
function qi(string $id = null): string
{
    return $id ? '"' . str_replace('"', '', $id) . '"' : '';
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
    $data = [];

    foreach ($item as $uid => $val) {
        if (empty($attrs[$uid]['col']) || $attrs[$uid]['auto']) {
            continue;
        }

        $data[$uid]['col'] = $attrs[$uid]['col'];
        $data[$uid]['param'] = db_param($uid);
        $data[$uid]['set'] = $data[$uid]['col'] . ' = ' . $data[$uid]['param'];
        $data[$uid]['val'] = $attrs[$uid]['multiple'] && $attrs[$uid]['backend'] === 'json' ? json_encode($val) : $val;
        $data[$uid]['type'] = db_type($attrs[$uid], $data[$uid]['val']);
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
    return db_crit($crit, $attrs, $opts);
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
    return db_crit($crit, $attrs, $opts, true);
}

/**
 * Internal WHERE and HAVING function
 *
 * @param array $crit
 * @param array $attrs
 * @param array $opts
 * @param bool $having
 *
 * @return string
 */
function db_crit(array $crit, array $attrs, array $opts = [], bool $having = false): string
{
    $cols = [];

    foreach ($crit as $id => $value) {
        if (empty($attrs[$id]['col'])) {
            continue;
        }

        if ($having) {
            $col = qi($id);
        } elseif (!empty($opts['as']) && strpos($attrs[$id]['col'], '.') === false) {
            $col =  $opts['as'] . '.' . $attrs[$id]['col'];
        } else {
            $col = $attrs[$id]['col'];
        }

        if ($attrs[$id]['nullable'] && $value === null) {
            $cols[$id] = '(' . $col . ' IS NULL)';
            continue;
        }

        $op = !empty($opts['search']) && in_array($attrs[$id]['backend'], ['varchar', 'text']) ? 'ILIKE' : '=';
        $r = [];

        foreach ((array) $value as $v) {
            $v = $op === 'ILIKE' ? '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%' : $v;
            $r[] = $col . ' ' . $op . ' ' . qv($v, $attrs[$id]['backend']);
        }

        $cols[$id] = '(' . implode(' OR ', $r) . ')';
    }

    return $cols ? ($having ? ' HAVING ' : ' WHERE ') . implode(' AND ', $cols) : '';
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
        if (!empty($attrs[$uid]['col'])) {
            $cols[$uid] = qi($uid) . ' ' . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
        }
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
