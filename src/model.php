<?php
namespace model;

use account;
use akilli;
use attribute;
use db;
use i18n;
use log;
use metadata;
use PDO;
use session;
use sql;
use Exception;
use RuntimeException;

/**
 * Validate
 *
 * @param array $item
 *
 * @return bool
 */
function validate(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    foreach ($item['_metadata']['attributes'] as $attribute) {
        // Validate attribute
        if (!$attribute['validate']($attribute, $item)) {
            $error = true;
        }
    }

    return empty($error);
}

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param bool $search
 *
 * @return int
 */
function size(string $entity, array $criteria = null, bool $search = false): int
{
    $metadata = akilli\data('metadata', $entity);
    $callback = 'model\\' . $metadata['model'] . '_size';

    try {
        return $callback($entity, $criteria, $search);
    } catch (Exception $e) {
        log\error($e);
        session\message(i18n\translate('Data could not be loaded'));
    }

    return 0;
}

/**
 * Load data
 *
 * Combined entity and collection loader.
 * By default it will load a collection, unless $index is explicitly set to (bool) false
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = akilli\data('metadata', $entity);
    $callback = 'model\\' . $metadata['model'] . '_load';
    $single = $index === false;
    $data = [];

    // Result
    try {
        $result = $callback($entity, $criteria, $index, $order, $limit);

        if (!$index
            || $index === 'search'
            || !is_array($index) && empty($metadata['attributes'][$index]) && $index !== 'unique'
        ) {
            $index = 'id';
        }

        foreach ($result as $item) {
            // Attribute load callback
            foreach ($item as $code => $value) {
                if (isset($metadata['attributes'][$code])) {
                    $item[$code] = $metadata['attributes'][$code]['load']($metadata['attributes'][$code], $item);
                }
            }

            $item['name'] = !isset($item['name']) ? $item['id'] : $item['name'];
            $item['_original'] = $item;
            $item['_metadata'] = empty($item['_metadata']) ? $metadata : $item['_metadata'];
            $item['_id'] = $item['id'];

            // Entity load events
            akilli\event(
                ['model.load', 'model.load.' . $metadata['model'], 'entity.load.' . $entity],
                $item
            );

            // Single result
            if ($single) {
                return $item;
            }

            if ($index === 'unique') {
                // Index unique
                foreach ($item as $code => $value) {
                    if (!empty($metadata['attributes'][$code]['is_unique'])) {
                        $data[$code][$item['id']] = $value;
                    }
                }
            } elseif (is_array($index)
                && !empty($index[0])
                && !empty($index[1])
                && !empty($item[$index[0]])
                && !empty($item[$index[1]])
            ) {
                // Array index
                $data[$item[$index[0]]][$item[$index[1]]] = $item;
            } else {
                // Default index
                $data[$item[$index]] = $item;
            }
        }
    } catch (Exception $e) {
        log\error($e);
        session\message(i18n\translate('Data could not be loaded'));
    }

    return $data;
}

/**
 * Save data
 *
 * @param string $entity
 * @param array $data
 *
 * @return bool
 */
function save(string $entity, array & $data): bool
{
    $metadata = akilli\data('metadata', $entity);
    $original = load($entity);

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $item = array_replace(empty($original[$id]) ? metadata\skeleton($entity) : $original[$id], $item);
        $data[$id] = $item;
        $callback = 'model\\' . $metadata['model'] . '_' . (empty($original[$id]) ? 'create' : 'save');
        $item['modified'] = date_format(date_create('now'), 'Y-m-d H:i:s');
        $item['modifier'] = account\account('id');

        if (empty($original[$id])) {
            $item['created'] = $item['modified'];
            $item['creator'] = $item['modifier'];
        }

        // Validate
        if (!validate($item)) {
            if (!empty($item['__error'])) {
                $data[$id]['__error'] = $item['__error'];
            }

            $error = true;
            continue;
        }

        // Attributes
        foreach (array_keys($item) as $code) {
            if (!isset($metadata['attributes'][$code])) {
                continue;
            }

            // Attribute save callback
            if (!$metadata['attributes'][$code]['save']($metadata['attributes'][$code], $item)) {
                if (!empty($item['__error'])) {
                    $data[$id]['__error'] = $item['__error'];
                }

                $error = true;
                continue 2;
            }

            // Ignored attributes
            if (attribute\ignore($metadata['attributes'][$code], $item)) {
                unset($item[$code]);
            }
        }

        // Transaction
        $success = db\transaction(
            $metadata['db'],
            function () use ($entity, & $item, $callback, $metadata) {
                // Entity before save events
                akilli\event(
                    [
                        'model.save_before',
                        'model.save_before.' . $metadata['model'],
                        'entity.save_before.' . $entity
                    ],
                    $item
                );

                // Execute
                if (!$callback($item)) {
                    throw new RuntimeException('Save call failed');
                }

                // Entity after save events
                akilli\event(
                    [
                        'model.save_after',
                        'model.save_after.' . $metadata['model'],
                        'entity.save_after.' . $entity
                    ],
                    $item
                );
            }
        );

        // Unset item
        if (!$success) {
            $error = true;
        } else {
            unset($data[$id]);
        }
    }

    // Message
    session\message(i18n\translate(empty($error) ? 'Data successfully saved' : 'Data could not be saved'));

    return empty($error);
}

/**
 * Delete data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 * @param bool $system
 *
 * @return bool
 */
function delete(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null, bool $system = false): bool
{
    $metadata = akilli\data('metadata', $entity);
    $callback = 'model\\' . $metadata['model'] . '_delete';

    // Check if anything is there to delete
    if (!$data = load($entity, $criteria, $index, $order, $limit)) {
        return false;
    }

    // Check if single result
    if ($index === false) {
        $data = [$data['id'] => $data];
    }

    foreach ($data as $id => $item) {
        // Filter system items
        if (!$system && !empty($item['is_system'])) {
            session\message(i18n\translate('You must not delete system items! Therefore skipped ID %s', $id));
            unset($data[$id]);
            continue;
        }

        // Attribute delete callback
        foreach (array_keys($item) as $code) {
            if (isset($metadata['attributes'][$code])
                && !$metadata['attributes'][$code]['delete']($metadata['attributes'][$code], $item)
            ) {
                if (!empty($item['__error'][$code])) {
                    session\message($item['__error'][$code]);
                }

                $error = true;
                continue 2;
            }
        }

        // Transaction
        $success = db\transaction(
            $metadata['db'],
            function () use ($entity, & $item, $callback, $metadata) {
                // Entity before delete events
                akilli\event(
                    [
                        'model.delete_before',
                        'model.delete_before.' . $metadata['model'],
                        'entity.delete_before.' . $entity
                    ],
                    $item
                );

                // Execute
                if (!$callback($item)) {
                    throw new RuntimeException('Delete call failed');
                }

                // Entity after delete events
                akilli\event(
                    [
                        'model.delete_after',
                        'model.delete_after.' . $metadata['model'],
                        'entity.delete_after.' . $entity
                    ],
                    $item
                );
            }
        );

        if (!$success) {
            $error = true;
        }

        $data[$id] = $item;
    }

    // Message
    session\message(i18n\translate(empty($error) ? 'Data successfully deleted' : 'Data could not be deleted'));

    return empty($error);
}

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param bool $search
 *
 * @return int
 */
function sql_size(string $entity, array $criteria = null, bool $search = false): int
{
    $metadata = sql\meta($entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    // Prepare statement
    $stmt = $db->prepare(
        'SELECT COUNT(*) as total'
        . sql\from($db, $metadata['table'])
        . sql\where($db, (array) $criteria, $metadata['attributes'], null, false, $search)
    );

    // Execute statement
    $stmt->execute();

    // Result
    $item = $stmt->fetch();

    return (int) $item['total'];
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function sql_load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = sql\meta($entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    // Prepare statement
    $stmt = $db->prepare(
        sql\select($db, $metadata['attributes'])
        . sql\from($db, $metadata['table'])
        . sql\where($db, (array) $criteria, $metadata['attributes'], null, false, $index === 'search')
        . sql\order($db, (array) $order, $metadata['attributes'])
        . sql\limit($limit)
    );

    // Execute statement
    $stmt->execute();

    // Result
    return $stmt->fetchAll();
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 */
function sql_create(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    // Columns
    $columns = $params = $sets = [];
    sql\columns($metadata['attributes'], $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'INSERT INTO ' . $metadata['table']
        . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($metadata['attributes'][$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($metadata['attributes']['id']['auto'])) {
        $item['id'] = (int) $db->lastInsertId($metadata['sequence']);
    }

    return true;
}

/**
 * Save
 *
 * @param array $item
 *
 * @return bool
 */
function sql_save(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    // Columns
    $columns = $params = $sets = [];
    sql\columns($metadata['attributes'], $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET ' . implode(', ', $sets)
        . ' WHERE ' . $metadata['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($metadata['attributes'][$code], $item[$code]));
    }

    $stmt->bindValue(
        ':id',
        $item['_original']['id'],
        sql\type($metadata['attributes']['id'], $item['_original']['id'])
    );

    // Execute statement
    $stmt->execute();

    return true;
}

/**
 * Delete data
 *
 * @param array $item
 *
 * @return bool
 */
function sql_delete(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);

    // Prepare statement
    $stmt = $db->prepare(
        'DELETE FROM ' . $metadata['table'] . ' WHERE ' . $metadata['attributes']['id']['column'] . '  = :id'
    );

    // Bind values
    $stmt->bindValue(
        ':id',
        $item['_original']['id'],
        sql\type($metadata['attributes']['id'], $item['_original']['id'])
    );

    // Execute statement
    $stmt->execute();

    return true;
}

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param bool $search
 *
 * @return int
 */
function nestedset_size(string $entity, array $criteria = null, bool $search = false): int
{
    return sql_size($entity, $criteria, $search);
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function nestedset_load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = sql\meta($entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $attributes = $orderAttributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);
    $where = 'b.lft < e.lft AND b.rgt > e.rgt'
        . ($root ? ' AND b.' . $attributes['root_id']['column'] . ' = e.' . $attributes['root_id']['column'] : '');
    $selectLevel = $selectParentId = '';

    // Set hierarchy as default order
    if (empty($order)) {
        $order = $root ? ['root_id' => 'ASC', 'lft' => 'ASC'] : ['lft' => 'ASC'];
    }

    // Order attributes
    if (empty($orderAttributes['level']['column'])) {
        $code = sql\quote_identifier($db, 'level');
        $orderAttributes['level']['column'] =  $code;
        $selectLevel = ', ('
            . 'SELECT COUNT(b.' . $attributes['id']['column'] . ') + 1'
            . ' FROM ' . $metadata['table'] . ' b'
            . ' WHERE ' . $where
            . ') as ' . $code;
    }

    if (empty($orderAttributes['parent_id']['column'])) {
        $code = sql\quote_identifier($db, 'parent_id');
        $orderAttributes['parent_id']['column'] =  $code;
        $selectParentId = ', ('
            . 'SELECT b.' . $attributes['id']['column']
            . ' FROM ' . $metadata['table'] . ' b'
            . ' WHERE ' . $where
            . ' ORDER BY b.' . $attributes['lft']['column'] . ' DESC'
            . ' LIMIT 1'
            . ')' . ($attributes['id']['backend'] === 'int' ? ' + 0 ' : '') . ' as ' . $code;
    }

    // Prepare statement
    $stmt = $db->prepare(
        sql\select($db, $attributes, 'e') . $selectLevel . $selectParentId
        . sql\from($db, $metadata['table'], 'e')
        . sql\where($db, (array) $criteria, $attributes, 'e', false, $index === 'search')
        . sql\order($db, $order, $orderAttributes)
        . sql\limit($limit)
    );

    // Execute statement
    $stmt->execute();

    // Result
    return array_map(
        function (array $item) use ($root) {
            $item['menubasis'] = $root ? $item['root_id'] . ':' . $item['id'] : $item['id'];

            return $item;
        },
        $stmt->fetchAll()
    );
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 */
function nestedset_create(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $attributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);

    // Columns
    $columns = $params = $sets = [];
    sql\columns($attributes, $item, $columns, $params, $sets);

    if (empty($item['basis']) || !($basisItem = load($metadata['id'], ['id' => $item['basis']], false))) {
        // No basis given so append node
        $curLft = 'COALESCE(MAX(rgt), 0) + 1';
        $curRgt = 'COALESCE(MAX(rgt), 0) + 2';
        $where = $root ? 'WHERE ' . $attributes['root_id']['column'] . ' = :root_id' : '';
    } else {
        // Basis given so insert new node depending on mode
        if ($item['mode'] === 'before') {
            $lft = 'lft >= ' . $basisItem['lft'];
            $rgt = 'rgt > ' . $basisItem['lft'];
            $curLft = 'lft - 2';
            $curRgt = 'lft - 1';
        } elseif ($item['mode'] === 'child') {
            $lft = 'lft > ' . $basisItem['rgt'];
            $rgt = 'rgt >= ' . $basisItem['rgt'];
            $curLft = 'rgt - 2';
            $curRgt = 'rgt - 1';
        } else {
            $lft = 'lft > ' . $basisItem['rgt'];
            $rgt = 'rgt > ' . $basisItem['rgt'];
            $curLft = 'rgt + 1';
            $curRgt = 'rgt + 2';
        }

        // Update position
        $stmt = $db->prepare(
            'UPDATE ' . $metadata['table']
            . ' SET lft = CASE WHEN ' . $lft . ' THEN lft + 2 ELSE lft END,'
            . ' rgt = CASE WHEN ' . $rgt . ' THEN rgt + 2 ELSE rgt END'
            . ' WHERE (' . $lft . ' OR ' . $rgt . ') '
            . ($root ? ' AND ' . $attributes['root_id']['column'] . ' = :root_id' : '')
        );

        if ($root) {
            $stmt->bindValue(':root_id', $item['root_id'], sql\type($attributes['root_id'], $item['root_id']));
        }

        $stmt->execute();

        // Insert
        $where = ' WHERE ' . $attributes['id']['column'] . ' = :basis'
            . ($root ? ' AND ' . $attributes['root_id']['column'] . ' = :root_id' : '');
    }

    // Prepare statement
    $stmt = $db->prepare(
        'INSERT INTO ' . $metadata['table'] . ' (' . implode(', ', $columns) . ', lft, rgt)'
        . ' SELECT ' . implode(', ', $params) . ', ' . $curLft . ', ' . $curRgt
        . ' FROM ' . $metadata['table']
        . $where
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($attributes[$code], $item[$code]));
    }

    if ($root) {
        $stmt->bindValue(':root_id', $item['root_id'], sql\type($attributes['root_id'], $item['root_id']));
    }

    if (!empty($item['basis'])) {
        $stmt->bindValue(':basis', $item['basis'], sql\type($attributes['id'], $item['basis']));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($attributes['id']['auto'])) {
        $item['id'] = (int) $db->lastInsertId($metadata['sequence']);
    }

    return true;
}

/**
 * Save
 *
 * @param array $item
 *
 * @return bool
 */
function nestedset_save(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $attributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);
    $basisItem = [];

    // Columns
    $columns = $params = $sets = [];
    sql\columns($attributes, $item, $columns, $params, $sets, ['root_id']);

    if (!empty($item['basis']) && ($item['basis'] === $item['_original']['id']
            || !($basisItem = load($metadata['id'], ['id' => $item['basis']], false))
            || $item['_original']['lft'] < $basisItem['lft'] && $item['_original']['rgt'] > $basisItem['rgt'])
    ) {
        // No change in position or wrong basis given
        $stmt = $db->prepare(
            'UPDATE ' . $metadata['table']
            . ' SET ' . implode(', ', $sets)
            . ' WHERE ' . $attributes['id']['column'] . ' = :id'
        );

        // Bind values
        $stmt->bindValue(':id', $item['_original']['id'], sql\type($attributes['id'], $item['_original']['id']));

        // Execute statement
        foreach ($params as $code => $param) {
            $stmt->bindValue($param, $item[$code], sql\type($attributes[$code], $item[$code]));
        }

        // Execute statement
        $stmt->execute();

        return true;
    }

    $lft = $item['_original']['lft'];
    $rgt = $item['_original']['rgt'];

    if (empty($item['basis'])) {
        $stmt = $db->prepare(
            'SELECT COALESCE(MAX(rgt), 0) + 1 as newlft'
            . ' FROM ' . $metadata['table']
            . ($root ? ' WHERE ' . $attributes['root_id']['column'] . ' = :root_id' : '')
        );

        if ($root) {
            $stmt->bindValue(':root_id', $item['root_id'], sql\type($attributes['root_id'], $item['root_id']));
        }

        $stmt->execute();
        $newLft = (int) $stmt->fetchColumn();
    } elseif ($item['mode'] === 'before') {
        $newLft = $basisItem['lft'];
    } elseif ($item['mode'] === 'child') {
        $newLft = $basisItem['rgt'];
    } else {
        $newLft = $basisItem['rgt'] + 1;
    }

    if ($newLft > $lft) {
        if ($root && $item['_original']['root_id'] !== $item['root_id']) {
            $diff = $newLft - $rgt + 1;
        } else {
            $diff = $newLft - $rgt - 1;
        }
    } else {
        $diff = $newLft - $lft;
    }

    $idValue = sql\quote(attribute\type($attributes['id'], $item['_original']['id']), $attributes['id']['backend']);
    $rootId = null;
    $oldRootCond = '';
    $rootCond = '';
    $setExpr = '';
    $setRoot = '';

    foreach (array_keys($sets) as $code) {
        $setExpr .= ', ' . $columns[$code] . ' = CASE WHEN ' . $attributes['id']['column'] . ' = ' . $idValue
            . ' THEN ' . $params[$code] . ' ELSE ' . $columns[$code] . ' END';
    }

    if ($root) {
        $oldRootId = sql\quote(
            attribute\type($attributes['root_id'], $item['_original']['root_id']),
            $attributes['root_id']['backend']
        );
        $rootId = sql\quote(
            attribute\type($attributes['root_id'], $item['root_id']),
            $attributes['root_id']['backend']
        );
        $oldRootCond = ' AND ' . $attributes['root_id']['column'] . ' = ' . $oldRootId ;
        $rootCond = ' AND ' . $attributes['root_id']['column'] . ' = ' . $rootId;
    }

    $isChild = '(lft BETWEEN ' . $lft . ' AND ' . $rgt . $oldRootCond . ')';
    $oldAfter = '(lft > ' . $rgt . $oldRootCond . ')';
    $oldParent = '(rgt > ' . $rgt . $oldRootCond . ')';
    $newAfter = '(lft >= ' . $newLft . $rootCond . ')';
    $newParent = '(rgt >= ' . $newLft . $rootCond . ')';
    $length = $rgt - $lft + 1;

    if ($root) {
        $setRoot = ', ' . $attributes['root_id']['column'] . ' = CASE WHEN ' . $isChild
            . ' THEN ' . $rootId . ' ELSE ' . $attributes['root_id']['column'] . ' END';
    }

    // Prepare statement
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET lft = CASE WHEN ' . $oldAfter . ' AND NOT ' . $newAfter . ' THEN lft - ' . $length
        . ' WHEN ' . $isChild . ' THEN lft + ' . $diff
        . ' WHEN ' . $newAfter . ' AND NOT ' . $oldAfter . ' THEN lft + ' . $length . ' ELSE lft END,'
        . ' rgt = CASE WHEN ' . $oldParent . ' AND NOT ' . $newParent . ' THEN rgt - ' . $length
        . ' WHEN ' . $isChild . ' THEN rgt + ' . $diff
        . ' WHEN ' . $newParent . ' AND NOT ' . $oldParent . ' THEN rgt + ' . $length . ' ELSE rgt END'
        . $setRoot
        . $setExpr
        . ' WHERE ' . $isChild . ' OR ' . $oldAfter . ' OR ' . $oldParent . ' OR ' . $newAfter . ' OR ' . $newParent
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($attributes[$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    return true;
}

/**
 * Delete data
 *
 * @param array $item
 *
 * @return bool
 */
function nestedset_delete(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $attributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);
    $lft = $item['_original']['lft'];
    $rgt = $item['_original']['rgt'];

    // Update
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET lft = CASE WHEN lft > ' . $rgt . ' THEN lft - (' . $rgt . ' - ' . $lft . ' + 1)'
        . ' WHEN lft BETWEEN ' . $lft . ' AND ' . $rgt . ' THEN -1 * lft ELSE lft END,'
        . ' rgt = CASE WHEN rgt > ' . $rgt . ' THEN rgt - (' . $rgt . ' - ' . $lft . ' + 1)'
        . ' WHEN lft BETWEEN ' . $lft . ' AND ' . $rgt . ' THEN -1 * rgt ELSE rgt END'
        . ' WHERE (lft > ' . $rgt . ' OR rgt > ' . $rgt . ' OR lft BETWEEN ' . $lft . ' AND ' . $rgt . ')'
        . ($root ? ' AND ' .  $attributes['root_id']['column'] . ' = :root_id' : '')
    );

    // Execute update
    if ($root) {
        $stmt->bindValue(
            ':root_id',
            $item['_original']['root_id'],
            sql\type($attributes['root_id'], $item['_original']['root_id'])
        );
    }

    $stmt->execute();

    // Delete
    $db->exec('DELETE FROM ' . $metadata['table'] . ' WHERE lft < 0');

    return true;
}

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param bool $search
 *
 * @return int
 */
function eav_size(string $entity, array $criteria = null, bool $search = false): int
{
    $metadata = sql\meta($entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $contentMetadata = sql\meta('eav_content');
    $valueMetadata = sql\meta('eav_value');
    $attributes = $metadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentMetadata['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $metadata['id'];

    // Prepare attributes
    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = sql\quote_identifier($db, $code);
            $attributes[$code]['column'] = $alias . '.' . $attribute['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMetadata['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMetadata['attributes']['content_id']['column']
                . ' = e.' . $metadata['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMetadata['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attributes[$code]['column'] = 'e.' . $attribute['column'];
        }
    }

    // Prepare statement
    $stmt = $db->prepare(
        'SELECT COUNT(*) as total'
        . sql\from($db, $metadata['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . sql\where($db, $criteria, $attributes, null, false, $search)
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attributes[$code]['id'],
            sql\type($valueMetadata['attributes']['attribute_id'], $attributes[$code]['id'])
        );
    }

    // Execute statement
    $stmt->execute();

    // Result
    $item = $stmt->fetch();

    return (int) $item['total'];
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int|array $limit
 *
 * @return array
 */
function eav_load(string $entity, array $criteria = null, $index = null, array $order = null, $limit = null): array
{
    $metadata = sql\meta($entity);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $contentMetadata = sql\meta('eav_content');
    $valueMetadata = sql\meta('eav_value');
    $attributes = $metadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentMetadata['attributes']);
    $joins = $params = [];
    $criteria['entity_id'] = $metadata['id'];

    // Prepare attributes
    foreach ($attributes as $code => $attribute) {
        if (empty($attribute['column'])) {
            continue;
        } elseif (!empty($valueAttributes[$code])) {
            $alias = sql\quote_identifier($db, $code);
            $attributes[$code]['column'] = $alias . '.' . $attribute['column'];
            $params[$code] = ':__attribute__' . str_replace('-', '_', $code);
            $joins[$code] = 'LEFT JOIN ' . $valueMetadata['table'] . ' ' . $alias . ' ON '
                . $alias . '.' . $valueMetadata['attributes']['content_id']['column']
                . ' = e.' . $metadata['attributes']['id']['column'] . ' AND '
                . $alias . '.' . $valueMetadata['attributes']['attribute_id']['column'] . ' = ' . $params[$code];
        } else {
            $attributes[$code]['column'] = 'e.' . $attribute['column'];
        }
    }

    // Prepare statement
    $stmt = $db->prepare(
        sql\select($db, $attributes)
        . sql\from($db, $metadata['table'], 'e')
        . (!empty($joins) ? implode(' ', $joins) : '')
        . sql\where($db, $criteria, $attributes, null, false, $index === 'search')
        . sql\order($db, (array) $order, $attributes)
        . sql\limit($limit)
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue(
            $param,
            $attributes[$code]['id'],
            sql\type($valueMetadata['attributes']['attribute_id'], $attributes[$code]['id'])
        );
    }

    // Execute statement
    $stmt->execute();

    // Result
    return $stmt->fetchAll();
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function eav_create(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $contentMetadata = sql\meta('eav_content');
    $attributes = $metadata['attributes'];
    $contentAttributes = $contentMetadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentAttributes);
    $valueModel = metadata\skeleton('eav_value');

    // Entity
    $item['entity_id'] = $metadata['id'];

    // Columns
    $columns = $params = $sets = [];
    sql\columns($contentAttributes, $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'INSERT INTO ' . $metadata['table']
        . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($attributes[$code], $item[$code]));
    }

    // Execute statement
    $stmt->execute();

    // Add DB generated id
    if (!empty($attributes['id']['auto'])) {
        $item['id'] = (int) $db->lastInsertId($metadata['sequence']);
    }

    // Values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attribute) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attribute['backend'];
            $save[--$i] = array_replace(
                $valueModel,
                [
                    'entity_id' => $item['entity_id'],
                    'attribute_id' => $attribute['id'],
                    'content_id' => $item['id'],
                    $valueCode => $item[$code]
                ]
            );
        }

        // Create Values
        if (count($save) > 0 && !save('eav_value', $save)) {
            throw new RuntimeException('Save call failed');
        }
    }

    return true;
}

/**
 * Save
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function eav_save(array & $item): bool
{
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    $metadata = sql\meta($item['_metadata']);
    /** @var PDO $db */
    $db = db\factory($metadata['db']);
    $contentMetadata = sql\meta('eav_content');
    $attributes = $metadata['attributes'];
    $contentAttributes = $contentMetadata['attributes'];
    $valueAttributes = array_diff_key($attributes, $contentAttributes);
    $valueModel = metadata\skeleton('eav_value');

    if ($valueAttributes) {
        $values = load('eav_value', ['content_id' => $item['_original']['id']], 'attribute_id');
    } else {
        $values = [];
    }

    // Entity
    $item['entity_id'] = $metadata['id'];

    // Columns
    $columns = $params = $sets = [];
    sql\columns($contentAttributes, $item, $columns, $params, $sets);

    // Prepare statement
    $stmt = $db->prepare(
        'UPDATE ' . $metadata['table']
        . ' SET ' . implode(', ', $sets)
        . ' WHERE ' . $attributes['id']['column'] . '  = :id'
    );

    // Bind values
    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $item[$code], sql\type($attributes[$code], $item[$code]));
    }

    $stmt->bindValue(':id', $item['_original']['id'], sql\type($attributes['id'], $item['_original']['id']));

    // Execute statement
    $stmt->execute();

    // Values
    if ($valueAttributes) {
        $save = [];
        $i = 0;

        foreach ($valueAttributes as $code => $attribute) {
            if (!array_key_exists($code, $item)) {
                continue;
            }

            $valueCode = 'value_' . $attribute['backend'];
            $valueItem = [
                'entity_id' => $item['entity_id'],
                'attribute_id' => $attribute['id'],
                'content_id' => $item['id'],
                $valueCode => $item[$code]
            ];

            if (!empty($values[$code])) {
                $save[$values[$code]['id']] = array_replace($values[$code], $valueItem);
            } else {
                $save[--$i] = array_replace($valueModel, $valueItem);
            }
        }

        // Save Values
        if (!save('eav_value', $save)) {
            throw new RuntimeException('Save call failed');
        }
    }

    return true;
}

/**
 * Delete data
 *
 * @param array $item
 *
 * @return bool
 */
function eav_delete(array $item): bool
{
    // No metadata provided
    if (empty($item['_metadata']) || $item['_metadata']['id'] !== $item['entity_id']) {
        return false;
    }

    return sql_delete($item);
}
