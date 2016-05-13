<?php
namespace qnd;

use PDO;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function node_size(string $eId, array $criteria = [], array $options = []): int
{
    return flat_size($eId, $criteria, $options);
}

/**
 * Load entity
 *
 * @param string $eId
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function node_load(string $eId, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $order = $order ?: ['root_id' => 'ASC', 'lft' => 'ASC'];

    return array_map(
        function ($item) {
            $item['position'] = $item['root_id'] . ':' . $item['id'];
            return $item;
        },
        flat_load($eId, $criteria, $index, $order, $limit)
    );
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 */
function node_create(array & $item): bool
{
    if (empty($item['_entity']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    $entity = $item['_entity'];
    $attrs = $entity['attributes'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['basis'] = cast($attrs['id'], $parts[1]);
    $rootId = $item['root_id'];

    if (empty($item['basis']) || !($basisItem = entity_load($entity['id'], ['id' => $item['basis']], false))) {
        // No or wrong basis given so append node
        $stmt = db()->prepare('
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                node
            WHERE 
                root_id = :root_id
        ');
        $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
        $stmt->execute();
        $baseLft = (int) $stmt->fetchColumn();
        $item['parent_id'] = null;
        $item['level'] = 1;
    } elseif ($item['mode'] === 'before') {
        $baseLft = $basisItem['lft'];
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    } elseif ($item['mode'] === 'child') {
        $baseLft = $basisItem['rgt'];
        $item['parent_id'] = $basisItem['id'];
        $item['level'] = $basisItem['level'] + 1;
    } else {
        $baseLft = $basisItem['rgt'] + 1;
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    }

    $length = 2;

    // Make space in the new tree
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft + :length
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            rgt = rgt + :length
        WHERE
            root_id = :root_id
            AND rgt >= :lft 
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Insert new node
    $cols = cols($attrs, $item);

    $stmt = prep(
        'INSERT INTO node (%s, root_id, lft, rgt, parent_id, level) VALUES (%s, :root_id, :lft, :rgt, :parent_id, :level)',
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $baseLft + 1, PDO::PARAM_INT);
    $stmt->bindValue(':parent_id', $item['parent_id'], PDO::PARAM_INT);
    $stmt->bindValue(':level', $item['level'], PDO::PARAM_INT);
    $stmt->execute();

    // Set DB generated id
    $item['id'] = (int) db()->lastInsertId();

    return true;
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function node_save(array & $item): bool
{
    if (empty($item['_entity']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    $entity = $item['_entity'];
    $attrs = $entity['attributes'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['basis'] = cast($attrs['id'], $parts[1]);
    $id = $item['_old']['id'];
    $rootId = $item['root_id'];
    $oldRootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $basisItem = [];
    $cols = cols($attrs, $item);

    // Update all attributes that are not involved with the tree
    $stmt = prep(
        'UPDATE node SET %s WHERE id = :_id',
        implode(', ', $cols['set'])
    );
    $stmt->bindValue(':_id', $id, PDO::PARAM_INT);

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // No change in position or wrong basis given
    if (!empty($item['basis']) && ($item['basis'] === $id
            || !($basisItem = entity_load($entity['id'], ['id' => $item['basis']], false))
            || $lft < $basisItem['lft'] && $rgt > $basisItem['rgt'])
    ) {
        return true;
    }

    // Calculate lft position
    if (empty($item['basis'])) {
        $stmt = db()->prepare('
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                node
            WHERE 
                root_id = :root_id
        ');
        $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
        $stmt->execute();
        $baseLft = (int) $stmt->fetchColumn();
        $item['parent_id'] = null;
        $item['level'] = 1;
    } elseif ($item['mode'] === 'before') {
        $baseLft = $basisItem['lft'];
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    } elseif ($item['mode'] === 'child') {
        $baseLft = $basisItem['rgt'];
        $item['parent_id'] = $basisItem['id'];
        $item['level'] = $basisItem['level'] + 1;
    } else {
        $baseLft = $basisItem['rgt'] + 1;
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    }

    if ($baseLft > $lft) {
        $diff = $oldRootId !== $rootId ? $baseLft - $rgt + 1 : $baseLft - $rgt - 1;
    } else {
        $diff = $baseLft - $lft;
    }

    $length = $rgt - $lft + 1;
    $newLft = $lft + $diff;

    // Move all affected nodes from old tree and update their positions for the new tree without adding them yet
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            root_id = :root_id,
            lft = -1 * (lft + :lft_diff),
            rgt = -1 * (rgt + :rgt_diff)
        WHERE
            root_id = :root_id
            AND lft BETWEEN :lft AND :rgt
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':lft_diff', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':rgt_diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft - :length
        WHERE
            root_id = :root_id
            AND lft > :rgt
    ');
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            rgt = rgt - :length
        WHERE
            root_id = :root_id
            AND rgt > :rgt
    ');
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Make space in the new tree
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft + :length
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            rgt = rgt + :length
        WHERE
            root_id = :root_id
            AND rgt >= :lft 
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Finally add the affected nodes to new tree
    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            lft = -1 * lft,
            rgt = -1 * rgt,
            parent_id = IF(id = :id, :parent_id, parent_id),
            level = level + :level
        WHERE
            root_id = :root_id
            AND lft < 0
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':parent_id', $item['parent_id'], PDO::PARAM_INT);
    $stmt->bindValue(':level', $item['level'] - $item['_old']['level'], PDO::PARAM_INT);
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
function node_delete(array & $item): bool
{
    if (empty($item['_entity'])) {
        return false;
    }

    $rootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $diff = $rgt - $lft + 1;

    $stmt = db()->prepare('
        UPDATE
            node
        SET
            lft = -1 * lft,
            rgt = -1 * rgt 
        WHERE 
            root_id = :root_id
            AND lft BETWEEN :lft AND :rgt
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE
            node
        SET 
            lft = lft - :diff
        WHERE 
            root_id = :root_id
            AND lft > :rgt
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE
            node
        SET 
            rgt = rgt - :diff
        WHERE 
            root_id = :root_id
            AND rgt > :rgt
    ');
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    db()->exec('
        DELETE FROM 
            node 
        WHERE 
            lft < 0
    ');

    return true;
}
