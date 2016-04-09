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
    $attrs = $orderAttrs = $meta['attributes'];
    $options = ['search' => $index === 'search', 'alias' => 'e'];

    // Set hierarchy as default order
    if (empty($order)) {
        $order = ['root_id' => 'ASC', 'lft' => 'ASC'];
    }

    // Order attributes
    $code = qi('level');
    $orderAttrs['level']['column'] =  $code;
    $selectLevel = ", (
        SELECT 
            COUNT(b.{$attrs['id']['column']}) + 1
        FROM
            {$meta['table']} b
        WHERE 
            b.lft < e.lft 
            AND b.rgt > e.rgt 
            AND b.{$attrs['root_id']['column']} = e.{$attrs['root_id']['column']}
        ) as $code";

    $code = qi('parent_id');
    $orderAttrs['parent_id']['column'] =  $code;
    $x = $attrs['id']['backend'] === 'int' ? ' + 0 ' : '';
    $selectParentId = ", (
        SELECT 
            b.{$attrs['id']['column']}
        FROM
            {$meta['table']} b
        WHERE 
            b.lft < e.lft 
            AND b.rgt > e.rgt 
            AND b.{$attrs['root_id']['column']} = e.{$attrs['root_id']['column']}
        ORDER BY 
            b.{$attrs['lft']['column']} DESC
        LIMIT 
            1
        ) $x as $code";

    $stmt = db()->prepare(
        select($attrs, 'e') . $selectLevel . $selectParentId
        . from($meta['table'], 'e')
        . where((array) $criteria, $attrs, $options)
        . order($order, $orderAttrs)
        . limit($limit)
    );
    $stmt->execute();

    return array_map(
        function (array $item) {
            $item['menubasis'] = $item['root_id'] . ':' . $item['id'];

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
    $attrs = $meta['attributes'];
    $cols = cols($attrs, $item);

    if (empty($item['basis']) || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))) {
        // No basis given so append node
        $curLft = 'COALESCE(MAX(rgt), 0) + 1';
        $curRgt = 'COALESCE(MAX(rgt), 0) + 2';
        $where = 'WHERE ' . $attrs['root_id']['column'] . ' = :root_id';
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
        $stmt = db()->prepare("
            UPDATE 
                {$meta['table']}
            SET 
                lft = CASE WHEN $lft THEN lft + 2 ELSE lft END,
                rgt = CASE WHEN $rgt THEN rgt + 2 ELSE rgt END
            WHERE 
                ($lft OR $rgt) 
                AND {$attrs['root_id']['column']} = :root_id
        ");
        $stmt->bindValue(':root_id', $item['root_id'], db_type($attrs['root_id'], $item['root_id']));
        $stmt->execute();

        // Insert
        $where = ' WHERE ' . $attrs['id']['column'] . ' = :basis AND ' . $attrs['root_id']['column'] . ' = :root_id';
    }

    $colList = implode(', ', $cols['col']);
    $paramList = implode(', ', $cols['param']);
    $stmt = db()->prepare("
        INSERT INTO 
            {$meta['table']}
            ($colList, lft, rgt)
        SELECT 
            $paramList, 
            $curLft, 
            $curRgt
        FROM 
            {$meta['table']}
        $where
    ");

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':root_id', $item['root_id'], db_type($attrs['root_id'], $item['root_id']));

    if (!empty($item['basis'])) {
        $stmt->bindValue(':basis', $item['basis'], db_type($attrs['id'], $item['basis']));
    }

    $stmt->execute();

    // Set DB generated id
    if (!empty($attrs['id']['auto'])) {
        $item['id'] = (int) db()->lastInsertId();
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
    $attrs = $meta['attributes'];
    $basisItem = [];
    $cols = cols($attrs, $item, ['root_id']);

    if (!empty($item['basis']) && ($item['basis'] === $item['_old']['id']
            || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))
            || $item['_old']['lft'] < $basisItem['lft'] && $item['_old']['rgt'] > $basisItem['rgt'])
    ) {
        // No change in position or wrong basis given
        $setList = implode(', ', $cols['set']);
        $stmt = db()->prepare("
            UPDATE 
                {$meta['table']}
            SET 
                $setList
            WHERE 
                {$attrs['id']['column']} = :id
        ");
        $stmt->bindValue(':id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));

        foreach ($cols['param'] as $code => $param) {
            $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
        }

        $stmt->execute();

        return true;
    }

    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];

    if (empty($item['basis'])) {
        $stmt = db()->prepare("
            SELECT 
                COALESCE(MAX(rgt), 0) + 1 as newlft
            FROM 
                {$meta['table']}
            WHERE 
                {$attrs['root_id']['column']} = :root_id
        ");
        $stmt->bindValue(':root_id', $item['root_id'], db_type($attrs['root_id'], $item['root_id']));
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
        if ($item['_old']['root_id'] !== $item['root_id']) {
            $diff = $newLft - $rgt + 1;
        } else {
            $diff = $newLft - $rgt - 1;
        }
    } else {
        $diff = $newLft - $lft;
    }

    $idValue = qv(cast($attrs['id'], $item['_old']['id']), $attrs['id']['backend']);
    $setExpr = '';

    foreach (array_keys($cols['set']) as $code) {
        $setExpr .= ", 
            {$cols['col'][$code]} = CASE 
                WHEN {$attrs['id']['column']} = $idValue THEN {$cols['param'][$code]} 
                ELSE {$cols['col'][$code]}
             END";
    }

    $oldRootId = qv(cast($attrs['root_id'], $item['_old']['root_id']), $attrs['root_id']['backend']);
    $rootId = qv(cast($attrs['root_id'], $item['root_id']), $attrs['root_id']['backend']);
    $oldRootCond = ' AND ' . $attrs['root_id']['column'] . ' = ' . $oldRootId ;
    $rootCond = ' AND ' . $attrs['root_id']['column'] . ' = ' . $rootId;
    $isChild = '(lft BETWEEN ' . $lft . ' AND ' . $rgt . $oldRootCond . ')';
    $oldAfter = '(lft > ' . $rgt . $oldRootCond . ')';
    $oldParent = '(rgt > ' . $rgt . $oldRootCond . ')';
    $newAfter = '(lft >= ' . $newLft . $rootCond . ')';
    $newParent = '(rgt >= ' . $newLft . $rootCond . ')';
    $length = $rgt - $lft + 1;

    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET 
            lft = CASE 
                WHEN $oldAfter AND NOT $newAfter THEN lft - $length
                WHEN $isChild THEN lft + $diff
                WHEN $newAfter AND NOT $oldAfter THEN lft + $length
                ELSE lft 
            END,
            rgt = CASE 
                WHEN $oldParent AND NOT $newParent THEN rgt - $length
                WHEN $isChild THEN rgt + $diff
                WHEN $newParent AND NOT $oldParent THEN rgt + $length 
                ELSE rgt 
            END,
            {$attrs['root_id']['column']} = CASE 
                WHEN $isChild THEN $rootId 
                ELSE {$attrs['root_id']['column']}
            END
            $setExpr
        WHERE 
            $isChild 
            OR $oldAfter 
            OR $oldParent 
            OR $newAfter 
            OR $newParent
    ");

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();
    message($stmt->queryString);

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
    $attrs = $meta['attributes'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];

    $stmt = db()->prepare("
        UPDATE
            {$meta['table']}
        SET 
            lft = CASE 
                WHEN lft > $rgt THEN lft - ($rgt - $lft + 1)
                WHEN lft BETWEEN $lft AND $rgt THEN -1 * lft 
                ELSE lft 
            END,
            rgt = CASE 
                WHEN rgt > $rgt THEN rgt - ($rgt - $lft + 1)
                WHEN lft BETWEEN $lft AND $rgt THEN -1 * rgt 
                ELSE rgt 
            END
        WHERE 
            (lft > $rgt OR rgt > $rgt OR lft BETWEEN $lft AND $rgt)
            AND {$attrs['root_id']['column']} = :root_id
    ");
    $stmt->bindValue(':root_id', $item['_old']['root_id'], db_type($attrs['root_id'], $item['_old']['root_id']));
    $stmt->execute();

    db()->exec('DELETE FROM ' . $meta['table'] . ' WHERE lft < 0');

    return true;
}
