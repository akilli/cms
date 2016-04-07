<?php
namespace akilli;

use Exception;
use PDO;

/**
 * Database factory
 *
 * @param string $key
 *
 * @return PDO
 */
function db(string $key): PDO
{
    $db = & registry('db');

    if (!isset($db[$key])) {
        $data = data('db', $key);
        $db[$key] = new PDO($data['dsn'], $data['username'], $data['password'], $data['driver_options']);
    }

    return $db[$key];
}

/**
 * Transaction
 *
 * @param string $key
 * @param callable $callback
 *
 * @return bool
 */
function db_transaction(string $key, callable $callback): bool
{
    static $data = [];

    $db = db($key);
    $hash = spl_object_hash($db);

    // Begin transaction
    $data[$hash] = !isset($data[$hash]) ? 1 : ++$data[$hash];

    if ($data[$hash] === 1) {
        $db->beginTransaction();
    } else {
        $db->exec('SAVEPOINT LEVEL_' . $data[$hash]);
    }

    try {
        // Perform callback
        $callback();

        // Commit transaction
        if ($data[$hash] === 1) {
            $db->commit();
        } else {
            $db->exec('RELEASE SAVEPOINT LEVEL_' . $data[$hash]);
        }

        --$data[$hash];
    } catch (Exception $e) {
        // Rollback transaction
        if ($data[$hash] === 1) {
            $db->rollBack();
        } else {
            $db->exec('ROLLBACK TO SAVEPOINT LEVEL_' . $data[$hash]);
        }

        --$data[$hash];
        error($e);
        $error = true;
    }

    return empty($error);
}

/**
 * Quotes value
 *
 * @param mixed $value
 * @param mixed $backend
 *
 * @return mixed
 */
function db_quote($value, $backend = null)
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
 * @param PDO $db
 * @param string $identifier
 *
 * @return string
 */
function db_quote_identifier(PDO $db, string $identifier = null): string
{
    $char = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? '`' : '"';

    return !empty($identifier) ? $char . str_replace($char, '', $identifier) . $char : '';
}

/**
 * Set appropriate parameter type
 *
 * @param array $attribute
 * @param mixed $value
 *
 * @return int
 */
function db_type(array $attribute, $value): int
{
    if ($value === null && !empty($attribute['null'])) {
        return PDO::PARAM_NULL;
    }

    if ($attribute['backend'] === 'bool') {
        return PDO::PARAM_BOOL;
    }

    if ($attribute['backend'] === 'int' || $attribute['backend'] === 'decimal') {
        return PDO::PARAM_INT;
    }

    return PDO::PARAM_STR;
}

/**
 * Retrieve metadata with quoted table and columns
 *
 * @param string|array $entity
 *
 * @return array
 */
function db_meta($entity): array
{
    $metadata = is_array($entity) ? $entity : data('metadata', $entity);
    $db = db($metadata['db']);

    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
        && $metadata['sequence'] === null
        && !empty($metadata['attributes']['id']['auto'])
    ) {
        $metadata['sequence'] = $metadata['table'] . '_id_seq';
    }

    $metadata['sequence'] = db_quote_identifier($db, $metadata['sequence']);
    $metadata['table'] = db_quote_identifier($db, $metadata['table']);

    foreach ($metadata['attributes'] as $code => $attribute) {
        $metadata['attributes'][$code]['column'] = db_quote_identifier($db, $attribute['column']);
    }

    return $metadata;
}

/**
 * Prepare columns
 *
 * @param array $attributes
 * @param array $item
 * @param array $columns
 * @param array $params
 * @param array $sets
 * @param array $skip
 *
 * @return bool
 */
function db_columns(array $attributes, array $item, array & $columns, array & $params, array & $sets, array $skip = []): bool
{
    if (empty($item)) {
        return false;
    }

    foreach (array_keys($item) as $code) {
        if (empty($attributes[$code]['column']) || !empty($attributes[$code]['auto']) || in_array($code, $skip)) {
            continue;
        }

        $param = ':__attribute__' . str_replace('-', '_', $code);
        $columns[$code] = $attributes[$code]['column'];
        $params[$code] = $param;
        $sets[$code] = $attributes[$code]['column'] . ' = ' . $param;
    }

    return true;
}

/**
 * Select part
 *
 * @param PDO $db
 * @param array $attributes
 * @param string $alias
 * @param bool $add
 *
 * @return string
 */
function select(PDO $db, array $attributes, string $alias = null, bool $add = false): string
{
    $columns = [];
    $alias = ($alias) ? db_quote_identifier($db, $alias) . '.' : '';

    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        }

        $columns[$code] = $alias . $attribute['column'] . ' as ' . db_quote_identifier($db, $code);
    }

    if (empty($columns)) {
        return '';
    }

    return ($add ? ', ' : 'SELECT ') . implode(', ', $columns);
}

/**
 * From part
 *
 * @param PDO $db
 * @param string $table
 * @param string $alias
 *
 * @return string
 */
function from(PDO $db, string $table, string $alias = null): string
{
    return ' FROM ' . $table . ($alias ? ' as ' . db_quote_identifier($db, $alias) : '');
}

/**
 * Where part
 *
 * @param PDO $db
 * @param array $criteria
 * @param array $attributes
 * @param array $options
 *
 * @return string
 */
function where(PDO $db, array $criteria, array $attributes, array $options = []): string
{
    $columns = [];
    $alias = !empty($options['alias']) ? db_quote_identifier($db, $options['alias']) . '.' : '';
    $search = !empty($options['search']);
    $operator = $search ? 'LIKE' : '=';

    foreach ($criteria as $code => $value) {
        if (empty($attributes[$code]['column'])) {
            continue;
        }

        $r = [];

        foreach ((array) $value as $v) {
            if ($search) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
            }

            $r[] = $alias . $attributes[$code]['column'] . ' ' . $operator . ' '
                . db_quote($v, $attributes[$code]['backend']);
        }

        $columns[$code] = '(' . implode(' OR ', $r) . ')';
    }

    if (empty($columns)) {
        return '';
    }

    return ' WHERE ' . implode(' AND ', $columns);
}

/**
 * Order By part
 *
 * @param PDO $db
 * @param array $order
 * @param array $attributes
 * @param bool $add
 *
 * @return string
 */
function order(PDO $db, array $order, array $attributes = null, bool $add = false): string
{
    $columns = [];

    foreach ($order as $code => $direction) {
        if ($attributes !== null && empty($attributes[$code]['column'])) {
            continue;
        }

        $direction = (strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';
        $columns[$code] = db_quote_identifier($db, $code) . ' ' . $direction;
    }

    if (empty($columns)) {
        return '';
    }

    return ($add ? ', ' : ' ORDER BY ') . implode(', ', $columns);
}

/**
 * Limit part
 *
 * @param int|array $limit
 *
 * @return string
 */
function limit($limit): string
{
    $isArray = is_array($limit);
    $offset = $isArray && !empty($limit[1]) ? (int) $limit[1] : 0;
    $limit = $isArray && !empty($limit[0]) ? (int) $limit[0] : (int) $limit;

    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . $offset : '';
}
