<?php
namespace akilli;

use PDO;

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function menu_size(string $entity, array $criteria = null, array $options = []): int
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
function menu_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $meta = data('meta', $entity);
    $options = ['search' => $index === 'search', 'alias' => 'e'];

    // Set hierarchy as default order
    if (empty($order)) {
        $order = ['root_id' => 'ASC', 'lft' => 'ASC'];
    }

    // Order attributes
    $orderAttrs = $meta['attributes'];
    $orderAttrs['level']['column'] =  'level';
    $orderAttrs['parent_id']['column'] =  'parent_id';

    // Statement
    $whereSql = where((array) $criteria, $meta['attributes'], $options);
    $orderSql = order($order, $orderAttrs);
    $limitSql = limit($limit);

    $stmt = db()->prepare("
        SELECT 
            e.id, 
            e.name, 
            e.target, 
            e.root_id, 
            e.lft, 
            e.rgt, 
            CONCAT(e.root_id, ':', e.id) as menubasis,
            (
                SELECT 
                    COUNT(b.id) + 1
                FROM
                    menu b
                WHERE 
                    b.lft < e.lft 
                    AND b.rgt > e.rgt 
                    AND b.root_id = e.root_id
            ) as level,
            (
                SELECT 
                    b.id
                FROM
                    menu b
                WHERE 
                    b.lft < e.lft 
                    AND b.rgt > e.rgt 
                    AND b.root_id = e.root_id
                ORDER BY 
                    b.lft DESC
                LIMIT 
                    1
            ) + 0 as parent_id
        FROM 
            menu e 
        $whereSql
        $orderSql
        $limitSql
    ");
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Create
 *
 * @param array $item
 *
 * @return bool
 */
function menu_create(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $cols = cols($attrs, $item, ['root_id']);
    $rootId = $item['root_id'];

    if (empty($item['basis']) || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))) {
        // No or wrong basis given so append node
        $stmt = db()->prepare("
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                menu
            WHERE 
                root_id = :root_id
        ");
        $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
        $stmt->execute();
        $baseLft = (int) $stmt->fetchColumn();
    } elseif ($item['mode'] === 'before') {
        $baseLft = $basisItem['lft'];
    } elseif ($item['mode'] === 'child') {
        $baseLft = $basisItem['rgt'];
    } else {
        $baseLft = $basisItem['rgt'] + 1;
    }

    $length = 2;

    // Make space in the new tree
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            lft = lft + :__length__
        WHERE
            root_id = :root_id
            AND lft >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':__lft__', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            rgt = rgt + :__length__
        WHERE
            root_id = :root_id
            AND rgt >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':__lft__', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Insert new node
    $colList = implode(', ', $cols['col']);
    $paramList = implode(', ', $cols['param']);
    $stmt = db()->prepare("
        INSERT INTO 
            menu
            (root_id, lft, rgt, $colList)
        VALUES 
            (:root_id, :lft, :rgt, $paramList)
    ");

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $baseLft + 1, PDO::PARAM_INT);
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
function menu_save(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $id = $item['_old']['id'];
    $rootId = $item['root_id'];
    $oldRootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $basisItem = [];

    // Update all attributes that are not involved with the tree
    $cols = cols($attrs, $item, ['root_id']);
    $setList = implode(', ', $cols['set']);
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET 
            $setList
        WHERE 
            id = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // No change in position or wrong basis given
    if (!empty($item['basis']) && ($item['basis'] === $id
            || !($basisItem = model_load($meta['id'], ['id' => $item['basis']], false))
            || $lft < $basisItem['lft'] && $rgt > $basisItem['rgt'])
    ) {
        return true;
    }

    // Calculate lft position
    if (empty($item['basis'])) {
        $stmt = db()->prepare("
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                menu
            WHERE 
                root_id = :root_id
        ");
        $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
        $stmt->execute();
        $baseLft = (int) $stmt->fetchColumn();
    } elseif ($item['mode'] === 'before') {
        $baseLft = $basisItem['lft'];
    } elseif ($item['mode'] === 'child') {
        $baseLft = $basisItem['rgt'];
    } else {
        $baseLft = $basisItem['rgt'] + 1;
    }

    if ($baseLft > $lft) {
        $diff = $oldRootId !== $rootId ? $baseLft - $rgt + 1 : $baseLft - $rgt - 1;
    } else {
        $diff = $baseLft - $lft;
    }

    $length = $rgt - $lft + 1;
    $newLft = $lft + $diff;

    // Move all affected nodes from old tree and update their positions for the new tree without adding them yet
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            root_id = :root_id,
            lft = -1 * (lft + :__lft_diff__),
            rgt = -1 * (rgt + :__rgt_diff__)
        WHERE
            root_id = :__root_id__
            AND lft BETWEEN :lft AND :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':__root_id__', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__lft_diff__', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':__rgt_diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            lft = lft - :__length__
        WHERE
            root_id = :__root_id__
            AND lft > :rgt
    ");
    $stmt->bindValue(':__root_id__', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            rgt = rgt - :__length__
        WHERE
            root_id = :__root_id__
            AND rgt > :rgt
    ");
    $stmt->bindValue(':__root_id__', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Make space in the new tree
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            lft = lft + :__length__
        WHERE
            root_id = :root_id
            AND lft >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':__lft__', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            rgt = rgt + :__length__
        WHERE
            root_id = :root_id
            AND rgt >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':__lft__', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Finally add the affected nodes to new tree
    $stmt = db()->prepare("
        UPDATE 
            menu
        SET
            lft = -1 * lft,
            rgt = -1 * rgt
        WHERE
            root_id = :root_id
            AND lft < 0
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
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
function menu_delete(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $rootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $diff = $rgt - $lft + 1;

    $stmt = db()->prepare("
        UPDATE
            menu
        SET
            lft = -1 * lft,
            rgt = -1 * rgt 
        WHERE 
            root_id = :root_id
            AND lft BETWEEN :lft AND :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE
            menu
        SET 
            lft = lft - :__diff__
        WHERE 
            root_id = :root_id
            AND lft > :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE
            menu
        SET 
            rgt = rgt - :__diff__
        WHERE 
            root_id = :root_id
            AND rgt > :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    db()->exec("
        DELETE FROM 
            menu 
        WHERE 
            lft < 0
    ");

    return true;
}
