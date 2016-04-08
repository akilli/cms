<?php
namespace akilli;

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function nestedset_size(string $entity, array $criteria = null, array $options = []): int
{
    return flat_size($entity, $criteria, $options);
}

/**
 * Load data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int[] $limit
 *
 * @return array
 */
function nestedset_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $meta = db_meta($entity);
    $attributes = $orderAttributes = $meta['attributes'];
    $root = !empty($attributes['root_id']);
    $where = 'b.lft < e.lft AND b.rgt > e.rgt'
        . ($root ? ' AND b.' . $attributes['root_id']['column'] . ' = e.' . $attributes['root_id']['column'] : '');
    $selectLevel = $selectParentId = '';
    $options = ['search' => $index === 'search', 'alias' => 'e'];

    // Set hierarchy as default order
    if (empty($order)) {
        $order = $root ? ['root_id' => 'ASC', 'lft' => 'ASC'] : ['lft' => 'ASC'];
    }

    // Order attributes
    if (empty($orderAttributes['level']['column'])) {
        $code = db_quote_identifier('level');
        $orderAttributes['level']['column'] =  $code;
        $selectLevel = ', ('
            . 'SELECT COUNT(b.' . $attributes['id']['column'] . ') + 1'
            . ' FROM ' . $meta['table'] . ' b'
            . ' WHERE ' . $where
            . ') as ' . $code;
    }

    if (empty($orderAttributes['parent_id']['column'])) {
        $code = db_quote_identifier('parent_id');
        $orderAttributes['parent_id']['column'] =  $code;
        $selectParentId = ', ('
            . 'SELECT b.' . $attributes['id']['column']
            . ' FROM ' . $meta['table'] . ' b'
            . ' WHERE ' . $where
            . ' ORDER BY b.' . $attributes['lft']['column'] . ' DESC'
            . ' LIMIT 1'
            . ')' . ($attributes['id']['backend'] === 'int' ? ' + 0 ' : '') . ' as ' . $code;
    }

    // Prepare statement
    $stmt = db()->prepare(
        select($attributes, 'e') . $selectLevel . $selectParentId
        . from($meta['table'], 'e')
        . where((array) $criteria, $attributes, $options)
        . order($order, $orderAttributes)
        . limit($limit)
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
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $attributes = $meta['attributes'];
    $root = !empty($attributes['root_id']);
    $cols = db_columns($attributes, $item);

    if (empty($item['basis']) || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))) {
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
        $stmt = db()->prepare(
            'UPDATE ' . $meta['table']
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
    $stmt = db()->prepare(
        'INSERT INTO ' . $meta['table'] . ' (' . implode(', ', $cols['col']) . ', lft, rgt)'
        . ' SELECT ' . implode(', ', $cols['param']) . ', ' . $curLft . ', ' . $curRgt
        . ' FROM ' . $meta['table']
        . $where
    );

    // Bind values
    foreach ($cols['param'] as $code => $param) {
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
        $item['id'] = (int) db()->lastInsertId($meta['sequence']);
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
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $attributes = $meta['attributes'];
    $root = !empty($attributes['root_id']);
    $basisItem = [];
    $cols = db_columns($attributes, $item, ['root_id']);

    if (!empty($item['basis']) && ($item['basis'] === $item['_old']['id']
            || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))
            || $item['_old']['lft'] < $basisItem['lft'] && $item['_old']['rgt'] > $basisItem['rgt'])
    ) {
        // No change in position or wrong basis given
        $stmt = db()->prepare(
            'UPDATE ' . $meta['table']
            . ' SET ' . implode(', ', $cols['set'])
            . ' WHERE ' . $attributes['id']['column'] . ' = :id'
        );

        // Bind values
        $stmt->bindValue(':id', $item['_old']['id'], db_type($attributes['id'], $item['_old']['id']));

        // Execute statement
        foreach ($cols['param'] as $code => $param) {
            $stmt->bindValue($param, $item[$code], db_type($attributes[$code], $item[$code]));
        }

        // Execute statement
        $stmt->execute();

        return true;
    }

    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];

    if (empty($item['basis'])) {
        $stmt = db()->prepare(
            'SELECT COALESCE(MAX(rgt), 0) + 1 as newlft'
            . ' FROM ' . $meta['table']
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
        if ($root && $item['_old']['root_id'] !== $item['root_id']) {
            $diff = $newLft - $rgt + 1;
        } else {
            $diff = $newLft - $rgt - 1;
        }
    } else {
        $diff = $newLft - $lft;
    }

    $idValue = db_quote(attribute_type($attributes['id'], $item['_old']['id']), $attributes['id']['backend']);
    $rootId = null;
    $oldRootCond = '';
    $rootCond = '';
    $setExpr = '';
    $setRoot = '';

    foreach (array_keys($cols['set']) as $code) {
        $setExpr .= ', ' . $cols['col'][$code] . ' = CASE WHEN ' . $attributes['id']['column'] . ' = ' . $idValue
            . ' THEN ' . $cols['param'][$code] . ' ELSE ' . $cols['col'][$code] . ' END';
    }

    if ($root) {
        $oldRootId = db_quote(
            attribute_type($attributes['root_id'], $item['_old']['root_id']),
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
    $stmt = db()->prepare(
        'UPDATE ' . $meta['table']
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
    foreach ($cols['param'] as $code => $param) {
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
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = db_meta($item['_meta']);
    $attributes = $meta['attributes'];
    $root = !empty($attributes['root_id']);
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];

    // Update
    $stmt = db()->prepare(
        'UPDATE ' . $meta['table']
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
            $item['_old']['root_id'],
            db_type($attributes['root_id'], $item['_old']['root_id'])
        );
    }

    $stmt->execute();

    // Delete
    db()->exec('DELETE FROM ' . $meta['table'] . ' WHERE lft < 0');

    return true;
}
