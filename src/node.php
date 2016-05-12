<?php
namespace qnd;

use PDO;
use RuntimeException;

/**
 * Size entity
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 *
 * @throws RuntimeException
 */
function node_size(string $entity, array $criteria = [], array $options = []): int
{
    if ($entity !== 'node') {
        throw new RuntimeException(_('Invalid entity: %s', $entity));
    }

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
 *
 * @throws RuntimeException
 */
function node_load(string $entity, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    if ($entity !== 'node') {
        throw new RuntimeException(_('Invalid entity: %s', $entity));
    }

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
            COUNT(b.id) + 1
        FROM
            node b
        WHERE 
            b.lft < e.lft 
            AND b.rgt > e.rgt 
            AND b.root_id = e.root_id
        ) as level";

    $orderAttrs['parent_id']['column'] =  'parent_id';
    $selectParentId = ", (
        SELECT 
            b.id
        FROM
            node b
        WHERE 
            b.lft < e.lft 
            AND b.rgt > e.rgt 
            AND b.root_id = e.root_id
        ORDER BY 
            b.lft DESC
        LIMIT 
            1
        ) + 0 as parent_id";

    $stmt = db()->prepare(
        select($attrs, 'e') . $selectLevel . $selectParentId
        . from($meta['table'], 'e')
        . where($criteria, $attrs, $options)
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
 *
 * @throws RuntimeException
 */
function node_create(array & $item): bool
{
    if (empty($item['_meta']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    if ($item['_meta']['id'] !== 'node') {
        throw new RuntimeException(_('Invalid entity: %s', $item['_meta']['id']));
    }

    $meta = $item['_meta'];
    $attrs = $meta['attributes'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $item['basis'] = cast($attrs['id'], $parts[1]);
    $rootId = $item['root_id'];

    if (empty($item['basis']) || !($basisItem = entity_load($meta['id'], ['id' => $item['basis']], false))) {
        // No or wrong basis given so append node
        $stmt = db()->prepare("
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                node
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
            node
        SET
            lft = lft + :length
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            rgt = rgt + :length
        WHERE
            root_id = :root_id
            AND rgt >= :lft 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $baseLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Insert new node
    $cols = cols($attrs, $item);
    $colList = implode(', ', $cols['col']);
    $paramList = implode(', ', $cols['param']);
    $stmt = db()->prepare("
        INSERT INTO 
            node
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
    if ($attrs['id']['generator'] === 'auto') {
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
 *
 * @throws RuntimeException
 */
function node_save(array & $item): bool
{
    if (empty($item['_meta']) || empty($item['position']) || strpos($item['position'], ':') <= 0) {
        return false;
    }

    if ($item['_meta']['id'] !== 'node') {
        throw new RuntimeException(_('Invalid entity: %s', $item['_meta']['id']));
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
                node
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
            node
        SET
            root_id = :root_id,
            lft = -1 * (lft + :lft_diff),
            rgt = -1 * (rgt + :rgt_diff)
        WHERE
            root_id = :root_id
            AND lft BETWEEN :lft AND :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':lft_diff', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':rgt_diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            lft = lft - :length
        WHERE
            root_id = :root_id
            AND lft > :rgt
    ");
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            rgt = rgt - :length
        WHERE
            root_id = :root_id
            AND rgt > :rgt
    ");
    $stmt->bindValue(':root_id', $oldRootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    // Make space in the new tree
    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            lft = lft + :length
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':lft', $newLft, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE 
            node
        SET
            rgt = rgt + :length
        WHERE
            root_id = :root_id
            AND rgt >= :lft 
    ");
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
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 *
 * @throws RuntimeException
 */
function node_delete(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    if ($item['_meta']['id'] !== 'node') {
        throw new RuntimeException(_('Invalid entity: %s', $item['_meta']['id']));
    }

    $rootId = $item['_old']['root_id'];
    $lft = $item['_old']['lft'];
    $rgt = $item['_old']['rgt'];
    $diff = $rgt - $lft + 1;

    $stmt = db()->prepare("
        UPDATE
            node
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
            node
        SET 
            lft = lft - :diff
        WHERE 
            root_id = :root_id
            AND lft > :rgt
    ");
    $stmt->bindValue(':root_id', $rootId, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $rgt, PDO::PARAM_INT);
    $stmt->bindValue(':diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare("
        UPDATE
            node
        SET 
            rgt = rgt - :diff
        WHERE 
            root_id = :root_id
            AND rgt > :rgt
    ");
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
