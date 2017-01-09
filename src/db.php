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
 * Transaction
 *
 * @param callable $callback
 *
 * @return bool
 */
function db_trans(callable $callback): bool
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
function db_prep(string $sql, string ...$args): PDOStatement
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
    return $value === null && !empty($attr['nullable']) ? PDO::PARAM_NULL : data('backend', $attr['backend'])['pdo'];
}

/**
 * Cast
 *
 * @param string $col
 * @param string $backend
 *
 * @return string
 */
function db_cast(string $col, string $backend): string
{
    return 'CAST(' . $col . ' AS ' . data('backend', $backend)['db'] . ')';
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
    $data = [];

    foreach ($item as $uid => $val) {
        if (empty($attrs[$uid]['col']) || $attrs[$uid]['auto']) {
            continue;
        }

        $param = db_param($uid);
        $cast = $attrs[$uid]['backend'] === 'search' ? 'TO_TSVECTOR(' . $param . ')' : $param;
        $val = $attrs[$uid]['multiple'] && $attrs[$uid]['backend'] === 'json' ? json_encode($val) : $val;
        $data[$uid]['col'] = $attrs[$uid]['col'];
        $data[$uid]['param'] = $param;
        $data[$uid]['cast'] = $cast;
        $data[$uid]['set'] = $data[$uid]['col'] . ' = ' . $cast;
        $data[$uid]['val'] = $val;
        $data[$uid]['type'] = db_type($attrs[$uid], $val);
    }

    return $data;
}

/**
 * Filter out non-DB columens
 *
 * @param array $attrs
 *
 * @return array
 */
function db_attr(array $attrs): array
{
    return array_filter(
        $attrs,
        function (array $attr) {
            return !empty($attr['col']);
        }
    );
}

/**
 * Quotes identifier
 *
 * @param string $id
 *
 * @return string
 */
function db_qi(string $id = null): string
{
    return $id ? '"' . str_replace('"', '', $id) . '"' : '';
}

/**
 * Quotes value
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return string
 */
function db_qv(array $attr, $value): string
{
    return db()->quote($value, db_type($attr, $value));
}

/**
 * Quotes array value
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return array
 */
function db_qa(array $attr, array $value): array
{
    return array_map(
        function ($v) use ($attr) {
            return db_qv($attr, $v);
        },
        $value
    );
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
    $search = !empty($opts['search']) && is_array($opts['search']) ? $opts['search'] : [];
    $cols = [];

    foreach ($crit as $id => $value) {
        if (empty($attrs[$id]['col'])) {
            continue;
        }

        $attr = $attrs[$id];

        if ($having) {
            $col = db_qi($id);
        } elseif (!empty($opts['as']) && strpos($attr['col'], '.') === false) {
            $col =  $opts['as'] . '.' . $attr['col'];
        } else {
            $col = $attr['col'];
        }

        if ($attr['nullable'] && $value === null) {
            $cols[$id] = '(' . $col . ' IS NULL)';
            continue;
        }

        $value = (array) $value;
        $r = [];

        if (!in_array($id, $search)) {
            $r[] = $col . ' IN (' . implode(', ', db_qa($attr, $value)) . ')';
        } elseif ($attr['backend'] === 'search') {
            $r[] = $col . ' @@ TO_TSQUERY(' . db_qv($attr, implode(' | ', $value)) . ')';
        } else {
            foreach ($value as $v) {
                $r[] = $col . ' ILIKE ' . db_qv($attr, '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%');
            }
        }

        $cols[$id] = '(' . implode(' OR ', $r) . ')';
    }

    return $cols ? ($having ? ' HAVING ' : ' WHERE ') . implode(' AND ', $cols) : '';
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
        $post = $uid !== $attr['col'] ? ' AS ' . db_qi($uid) : '';
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
            $cols[$uid] = db_qi($uid) . ' ' . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
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
