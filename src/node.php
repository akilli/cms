<?php
namespace qnd;

use LogicException;
use PDO;

/**
 * Size entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function node_size(array $entity, array $crit = [], array $opts = []): int
{
    return flat_size($entity, $crit, $opts);
}

/**
 * Load entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function node_load(array $entity, array $crit = [], array $opts = []): array
{
    $opts['order'] = $opts['order'] ?? ['root_id' => 'asc', 'lft' => 'asc'];

    return flat_load($entity, $crit, $opts);
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
    // Position
    $lft = _node_position($item);
    $range = 2;

    // Make space in the new tree
    _node_insert($item, $lft, $range);

    // Insert new node
    $cols = cols($item['_entity']['attr'], $item);

    $stmt = prep(
        'INSERT INTO node (%s, root_id, lft, rgt, parent_id, level) VALUES (%s, :root_id, :lft, :rgt, :parent_id, :level)',
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
    );

    foreach ($cols['param'] as $uid => $param) {
        $stmt->bindValue($param, $item[$uid], db_type($item['_entity']['attr'][$uid], $item[$uid]));
    }

    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $lft + 1, PDO::PARAM_INT);
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
    // Update all attributes that are not involved with the tree
    $cols = cols($item['_entity']['attr'], $item);

    $stmt = prep(
        'UPDATE node SET %s WHERE id = :_id',
        implode(', ', $cols['set'])
    );
    $stmt->bindValue(':_id', $item['_old']['id'], PDO::PARAM_INT);

    foreach ($cols['param'] as $uid => $param) {
        $stmt->bindValue($param, $item[$uid], db_type($item['_entity']['attr'][$uid], $item[$uid]));
    }

    $stmt->execute();

    // No change in position, so nothing to do anymore
    if ($item['position'] === $item['_old']['position']) {
        return true;
    }

    // Position
    $lft = _node_position($item);
    $range = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

    if ($item['root_id'] === $item['_old']['root_id'] && $lft > $item['_old']['lft']) {
        $lft -= $range;
    }

    $diff = $lft - $item['_old']['lft'];

    // Move all affected nodes from old tree and update their positions for the new tree without adding them yet
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            root_id = :root_id,
            lft = -1 * (lft + :lft_diff),
            rgt = -1 * (rgt + :rgt_diff)
        WHERE
            root_id = :old_root_id
            AND lft BETWEEN :lft AND :rgt
    ');
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':old_root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':lft_diff', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':rgt_diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    _node_remove($item);

    // Make space in the new tree
    _node_insert($item, $lft, $range);

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
    $stmt->bindValue(':id', $item['_old']['id'], PDO::PARAM_INT);
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
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
    $stmt = db()->prepare('
        DELETE FROM 
            node 
        WHERE 
            root_id = :root_id
            AND lft BETWEEN :lft AND :rgt
    ');
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    _node_remove($item);

    return true;
}

/**
 * Calculate node postion
 *
 * @param array $item
 *
 * @return int
 *
 * @throws LogicException
 */
function _node_position(array & $item): int
{
    $parts = explode(':', $item['position']);
    $item['root_id'] = (int) $parts[0];
    $basis = (int) $parts[1];

    // No or wrong basis given so append node
    if (!$basis || !$basisItem = one($item['_entity']['id'], ['root_id' => $item['root_id'], 'lft' => $basis])) {
        $item['parent_id'] = null;
        $item['level'] = 1;

        $stmt = db()->prepare('
            SELECT 
                COALESCE(MAX(rgt), 0) + 1
            FROM 
                node
            WHERE 
                root_id = :root_id
        ');
        $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // Recursion
    if (!empty($item['_old'])
        && $item['root_id'] === $item['_old']['root_id']
        && $item['_old']['lft'] < $basisItem['lft']
        && $item['_old']['rgt'] > $basisItem['rgt']
    ) {
        throw new LogicException(_('Menu Node can not be child of itself'));
    }

    if ($item['mode'] === 'child') {
        $item['parent_id'] = $basisItem['id'];
        $item['level'] = $basisItem['level'] + 1;
        $pos = $basisItem['rgt'];
    } else {
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
        $pos = $item['mode'] === 'before' ? $basisItem['lft'] : $basisItem['rgt'] + 1;
    }

    return $pos;
}

/**
 * Make space in the new tree
 *
 * @param array $item
 * @param int $lft
 * @param int $range
 *
 * @return void
 */
function _node_insert(array $item, int $lft, int $range)
{
    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft + :range
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ');
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            rgt = rgt + :range
        WHERE
            root_id = :root_id
            AND rgt >= :lft 
    ');
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $lft, PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Close gap in old tree
 *
 * @param array $item
 *
 * @return void
 */
function _node_remove(array $item)
{
    $range = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft - :range
        WHERE
            root_id = :root_id
            AND lft > :rgt
    ');
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            rgt = rgt - :range
        WHERE
            root_id = :root_id
            AND rgt > :rgt
    ');
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();
}
