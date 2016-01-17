<?php
namespace sql;

use app;
use db;
use i18n;
use log;
use PDO;
use Exception;
use RuntimeException;

/**
 * Database factory
 *
 * @param array $data
 *
 * @return PDO
 */
function factory(array $data)
{
    $data = prepare($data);

    if ($data['driver'] === 'mysql') {
        $data['dsn'] .= ';charset=' . $data['charset'];
    } elseif ($data['driver'] === 'sqlite') {
        if (strpos($data['dbname'], '/') !== 0) {
            $data['dbname'] = app\path('db', 'sqlite/' . $data['dbname']);
        }

        $data['dsn'] = $data['driver'] . ':' . $data['dbname'];
    }

    $db = new PDO($data['dsn'], $data['username'], $data['password'], $data['driver_options']);

    if ($data['driver'] === 'pgsql') {
        $db->exec('SET NAMES ' . quote($data['charset']));
    }

    return $db;
}

/**
 * Prepare database configuration
 *
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function prepare(array $data)
{
    if (empty($data['driver'])
        || empty($data['host'])
        || empty($data['dbname'])
        || empty($data['username'])
        || !isset($data['password'])
    ) {
        throw new RuntimeException(i18n\translate('Invalid database configuration'));
    }

    $data['charset'] = !empty($data['charset']) ? $data['charset'] : 'utf8';
    $data['driver_options'] = !empty($data['driver_options']) ? $data['driver_options'] : [];
    $data['dsn'] = $data['driver'] . ':host=' . $data['host'] . ';dbname=' . $data['dbname'];

    return $data;
}

/**
 * Transaction
 *
 * @param PDO $db
 * @param callable $callback
 *
 * @return bool
 */
function transaction(PDO $db, callable $callback)
{
    static $data = [];

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
        log\error($e);
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
function quote($value, $backend = null)
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
function quote_identifier(PDO $db, $identifier)
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
function type(array $attribute, $value)
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
 * @return bool
 */
function meta($entity)
{
    $metadata = is_array($entity) ? $entity : app\data('metadata', $entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
        && $metadata['sequence'] === null
        && !empty($metadata['attributes']['id']['auto'])
    ) {
        $metadata['sequence'] = $metadata['table'] . '_id_seq';
    }

    $metadata['sequence'] = quote_identifier($db, $metadata['sequence']);
    $metadata['table'] = quote_identifier($db, $metadata['table']);

    foreach ($metadata['attributes'] as & $attribute) {
        $attribute['column'] = quote_identifier($db, $attribute['column']);
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
function columns(array $attributes, array $item, array & $columns, array & $params, array & $sets, array $skip = [])
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
function select(PDO $db, array $attributes, $alias = null, $add = false)
{
    $columns = [];
    $alias = ($alias) ? quote_identifier($db, $alias) . '.' : '';

    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        }

        $columns[$code] = $alias . $attribute['column'] . ' as ' . quote_identifier($db, $code);
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
function from(PDO $db, $table, $alias = null)
{
    return ' FROM ' . $table . ($alias ? ' as ' . quote_identifier($db, $alias) : '');
}

/**
 * Where part
 *
 * @param PDO $db
 * @param array $criteria
 * @param array $attributes
 * @param string $alias
 * @param bool $add
 * @param bool $search
 *
 * @return string
 */
function where(PDO $db, array $criteria, array $attributes, $alias = null, $add = false, $search = false)
{
    $columns = [];
    $alias = $alias ? quote_identifier($db, $alias) . '.' : '';
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
                . quote($v, $attributes[$code]['backend']);
        }

        $columns[$code] = '(' . implode(' OR ', $r) . ')';
    }

    if (empty($columns)) {
        return '';
    }

    return ($add ? ' AND ' : ' WHERE ') . implode(' AND ', $columns);
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
function order(PDO $db, array $order, array $attributes = null, $add = false)
{
    $columns = [];

    foreach ($order as $code => $direction) {
        if ($attributes !== null && empty($attributes[$code]['column'])) {
            continue;
        }

        $direction = (strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';
        $columns[$code] = quote_identifier($db, $code) . ' ' . $direction;
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
function limit($limit)
{
    $isArray = is_array($limit);
    $offset = $isArray && !empty($limit[1]) ? (int) $limit[1] : 0;
    $limit = $isArray && !empty($limit[0]) ? (int) $limit[0] : (int) $limit;

    return $limit > 0 ? ' LIMIT ' . $limit . ' OFFSET ' . $offset : '';
}
