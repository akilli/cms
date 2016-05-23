<?php
namespace qnd;

use PDO;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function node_size(string $eId, array $crit = [], array $opts = []): int
{
    return flat_size($eId, $crit, $opts);
}

/**
 * Load entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function node_load(string $eId, array $crit = [], array $opts = []): array
{
    $opts['order'] = $opts['order'] ?? ['root_id' => 'asc', 'lft' => 'asc'];

    return flat_load($eId, $crit, $opts);
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
    $attrs = $item['_entity']['attr'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = (int) $parts[0];
    $basis = (int) $parts[1];

    if (!$basisItem = one($item['_entity']['id'], ['root_id' => $item['root_id'], 'lft' => $basis])) {
        // No or wrong basis given so append node
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
        $basisLft = (int) $stmt->fetchColumn();
        $item['parent_id'] = null;
        $item['level'] = 1;
    } elseif ($item['mode'] === 'before') {
        $basisLft = $basisItem['lft'];
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    } elseif ($item['mode'] === 'child') {
        $basisLft = $basisItem['rgt'];
        $item['parent_id'] = $basisItem['id'];
        $item['level'] = $basisItem['level'] + 1;
    } else {
        $basisLft = $basisItem['rgt'] + 1;
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    }

    // Make space in the new tree
    $length = 2;

    $stmt = db()->prepare('
        UPDATE 
            node
        SET
            lft = lft + :length
        WHERE
            root_id = :root_id
            AND lft >= :lft 
    ');
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $basisLft, PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $basisLft, PDO::PARAM_INT);
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

    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $basisLft, PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $basisLft + 1, PDO::PARAM_INT);
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
    $attrs = $item['_entity']['attr'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = (int) $parts[0];
    $basis = (int) $parts[1];
    $basisItem = [];

    // Update all attributes that are not involved with the tree
    $cols = cols($attrs, $item);

    $stmt = prep(
        'UPDATE node SET %s WHERE id = :_id',
        implode(', ', $cols['set'])
    );
    $stmt->bindValue(':_id', $item['_old']['id'], PDO::PARAM_INT);

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // No change in position or wrong basis given
    if ($basis && ($basis === $item['_old']['id']
            || !($basisItem = one($item['_entity']['id'], ['root_id' => $item['root_id'], 'lft' => $basis]))
            || $item['_old']['lft'] < $basisItem['lft'] && $item['_old']['rgt'] > $basisItem['rgt'])
    ) {
        return true;
    }

    // Calculate lft position
    if (!$basis) {
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
        $basisLft = (int) $stmt->fetchColumn();
        $item['parent_id'] = null;
        $item['level'] = 1;
    } elseif ($item['mode'] === 'before') {
        $basisLft = $basisItem['lft'];
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    } elseif ($item['mode'] === 'child') {
        $basisLft = $basisItem['rgt'];
        $item['parent_id'] = $basisItem['id'];
        $item['level'] = $basisItem['level'] + 1;
    } else {
        $basisLft = $basisItem['rgt'] + 1;
        $item['parent_id'] = $basisItem['parent_id'];
        $item['level'] = $basisItem['level'];
    }

    if ($basisLft > $item['_old']['lft']) {
        $diff = $item['_old']['root_id'] !== $item['root_id'] ? $basisLft - $item['_old']['rgt'] + 1 : $basisLft - $item['_old']['rgt'] - 1;
    } else {
        $diff = $basisLft - $item['_old']['lft'];
    }

    $length = $item['_old']['rgt'] - $item['_old']['lft'] + 1;
    $newLft = $item['_old']['lft'] + $diff;

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
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
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
    $diff = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

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
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
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
