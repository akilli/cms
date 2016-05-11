<?php
namespace qnd;

use PDO;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function tree_size(string $entity, array $criteria = null, array $options = []): int
{
    return flat_size($entity, $criteria, $options);
}

/**
 * Load entity
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function tree_load(string $entity, array $criteria = null, $index = null, array $order = [], array $limit = []): array
{
    $meta = data('meta', $entity);
    $attrs = $orderAttrs = $meta['attributes'];
    $options = ['search' => $index === 'search', 'alias' => 'e'];

    // Set hierarchy as default order
    if (empty($order)) {
        $order = ['root_id' => 'ASC', 'lft' => 'ASC'];
    }

    // Order attributes
    $orderAttrs['level']['column'] =  'level';
    $selectLevel = ", (
        SELECT 
            COUNT(b.{$attrs['id']['column']}) + 1
        FROM
            {$meta['table']} b
        WHERE 
            b.lft < e.lft 
            AND b.rgt > e.rgt 
            AND b.{$attrs['root_id']['column']} = e.{$attrs['root_id']['column']}
        ) as level";

    $orderAttrs['parent_id']['column'] =  'parent_id';
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
        ) $x as parent_id";

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
            $item['position'] = $item['root_id'] . ':' . $item['id'];

            return $item;
        },
        $stmt->fetchAll()
    );
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 */
function tree_create(array & $item): bool
{
    if (empty($item['_meta']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['basis'] = cast($attrs['id'], $parts[1]);
    $cols = cols($attrs, $item, ['root_id']);
    $rootId = $item['root_id'];

    if (empty($item['basis']) || !($basisItem = entity_load($meta['id'], ['id' => $item['basis']], false))) {
        // No or wrong basis given so append node
        $stmt = db()->prepare("
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                {$meta['table']}
            WHERE 
                {$attrs['root_id']['column']} = :root_id
        ");
        $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
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
            {$meta['table']}
        SET
            lft = lft + :__length__
        WHERE
            {$attrs['root_id']['column']} = :root_id
            AND lft >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':__lft__', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            rgt = rgt + :__length__
        WHERE
            {$attrs['root_id']['column']} = :root_id
            AND rgt >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':__lft__', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Insert new node
    $colList = implode(', ', $cols['col']);
    $paramList = implode(', ', $cols['param']);
    $stmt = db()->prepare("
        INSERT INTO 
            {$meta['table']}
            (root_id, lft, rgt, $colList)
        VALUES 
            (:root_id, :lft, :rgt, $paramList)
    ");

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
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
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function tree_save(array & $item): bool
{
    if (empty($item['_meta']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['basis'] = cast($attrs['id'], $parts[1]);
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
            {$meta['table']}
        SET 
            $setList
        WHERE 
            {$attrs['id']['column']} = :id
    ");
    $stmt->bindValue(':id', $id, db_type($attrs['id'], $id));

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // No change in position or wrong basis given
    if (!empty($item['basis']) && ($item['basis'] === $id
            || !($basisItem = entity_load($meta['id'], ['id' => $item['basis']], false))
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
                {$meta['table']}
            WHERE 
                {$attrs['root_id']['column']} = :root_id
        ");
        $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
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
            {$meta['table']}
        SET
            {$attrs['root_id']['column']} = :root_id,
            lft = -1 * (lft + :__lft_diff__),
            rgt = -1 * (rgt + :__rgt_diff__)
        WHERE
            {$attrs['root_id']['column']} = :__root_id__
            AND lft BETWEEN :lft AND :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':__root_id__', $oldRootId, db_type($attrs['root_id'], $oldRootId));
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__lft_diff__', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':__rgt_diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            lft = lft - :__length__
        WHERE
            {$attrs['root_id']['column']} = :__root_id__
            AND lft > :rgt
    ");
    $stmt->bindValue(':__root_id__', $oldRootId, db_type($attrs['root_id'], $oldRootId));
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            rgt = rgt - :__length__
        WHERE
            {$attrs['root_id']['column']} = :__root_id__
            AND rgt > :rgt
    ");
    $stmt->bindValue(':__root_id__', $oldRootId, db_type($attrs['root_id'], $oldRootId));
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Make space in the new tree
    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            lft = lft + :__length__
        WHERE
            {$attrs['root_id']['column']} = :root_id
            AND lft >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':__lft__', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            rgt = rgt + :__length__
        WHERE
            {$attrs['root_id']['column']} = :root_id
            AND rgt >= :__lft__ 
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':__lft__', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':__length__', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Finally add the affected nodes to new tree
    $stmt = db()->prepare("
        UPDATE 
            {$meta['table']}
        SET
            lft = -1 * lft,
            rgt = -1 * rgt
        WHERE
            {$attrs['root_id']['column']} = :root_id
            AND lft < 0
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->execute();

    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function tree_delete(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $rootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $diff = $rgt - $lft + 1;

    $stmt = db()->prepare("
        UPDATE
            {$meta['table']}
        SET
            lft = -1 * lft,
            rgt = -1 * rgt 
        WHERE 
            {$attrs['root_id']['column']} = :root_id
            AND lft BETWEEN :lft AND :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE
            {$meta['table']}
        SET 
            lft = lft - :__diff__
        WHERE 
            {$attrs['root_id']['column']} = :root_id
            AND lft > :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE
            {$meta['table']}
        SET 
            rgt = rgt - :__diff__
        WHERE 
            {$attrs['root_id']['column']} = :root_id
            AND rgt > :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, db_type($attrs['root_id'], $rootId));
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':__diff__', $diff, PDO::PARAM_INT);
    $stmt->execute();

    db()->exec('DELETE FROM ' . $meta['table'] . ' WHERE lft < 0');

    return true;
}
