<?php
namespace akilli;

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
    $metadata = db_meta($entity);
    $db = db($metadata['db']);
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
        $code = db_quote_identifier($db, 'level');
        $orderAttributes['level']['column'] =  $code;
        $selectLevel = ', ('
            . 'SELECT COUNT(b.' . $attributes['id']['column'] . ') + 1'
            . ' FROM ' . $metadata['table'] . ' b'
            . ' WHERE ' . $where
            . ') as ' . $code;
    }

    if (empty($orderAttributes['parent_id']['column'])) {
        $code = db_quote_identifier($db, 'parent_id');
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
        db_select($db, $attributes, 'e') . $selectLevel . $selectParentId
        . db_from($db, $metadata['table'], 'e')
        . db_where($db, (array) $criteria, $attributes, 'e', false, $index === 'search')
        . db_order($db, $order, $orderAttributes)
        . db_limit($limit)
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

    $metadata = db_meta($item['_metadata']);
    $db = db($metadata['db']);
    $attributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);

    // Columns
    $columns = $params = $sets = [];
    db_columns($attributes, $item, $columns, $params, $sets);

    if (empty($item['basis']) || !($basisItem = model_load($metadata['id'], ['id' => $item['basis']], false))) {
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
            $stmt->bindValue(':root_id', $item['root_id'], db_type($attributes['root_id'], $item['root_id']));
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
        $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
    }

    if ($root) {
        $stmt->bindValue(':root_id', $item['root_id'], db_type($attributes['root_id'], $item['root_id']));
    }

    if (!empty($item['basis'])) {
        $stmt->bindValue(':basis', $item['basis'], db_type($attributes['id'], $item['basis']));
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

    $metadata = db_meta($item['_metadata']);
    $db = db($metadata['db']);
    $attributes = $metadata['attributes'];
    $root = !empty($attributes['root_id']);
    $basisItem = [];

    // Columns
    $columns = $params = $sets = [];
    db_columns($attributes, $item, $columns, $params, $sets, ['root_id']);

    if (!empty($item['basis']) && ($item['basis'] === $item['_original']['id']
            || !($basisItem = model_load($metadata['id'], ['id' => $item['basis']], false))
            || $item['_original']['lft'] < $basisItem['lft'] && $item['_original']['rgt'] > $basisItem['rgt'])
    ) {
        // No change in position or wrong basis given
        $stmt = $db->prepare(
            'UPDATE ' . $metadata['table']
            . ' SET ' . implode(', ', $sets)
            . ' WHERE ' . $attributes['id']['column'] . ' = :id'
        );

        // Bind values
        $stmt->bindValue(':id', $item['_original']['id'], db_type($attributes['id'], $item['_original']['id']));

        // Execute statement
        foreach ($params as $code => $param) {
            $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
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
            $stmt->bindValue(':root_id', $item['root_id'], db_type($attributes['root_id'], $item['root_id']));
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

    $idValue = db_quote(attribute_type($attributes['id'], $item['_original']['id']), $attributes['id']['backend']);
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
        $oldRootId = db_quote(
            attribute_type($attributes['root_id'], $item['_original']['root_id']),
            $attributes['root_id']['backend']
        );
        $rootId = db_quote(
            attribute_type($attributes['root_id'], $item['root_id']),
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
        $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
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

    $metadata = db_meta($item['_metadata']);
    $db = db($metadata['db']);
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
            db_type($attributes['root_id'], $item['_original']['root_id'])
        );
    }

    $stmt->execute();

    // Delete
    $db->exec('DELETE FROM ' . $metadata['table'] . ' WHERE lft < 0');

    return true;
}
