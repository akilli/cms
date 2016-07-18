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
    $item['lft'] = node_position($item);
    $item['rgt'] = $item['lft'] + 1;

    // Make space in the new tree
    node_insert($item);

    // Insert new node
    return flat_create($item);
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
    $attrs = $item['_entity']['attr'];
    unset(
        $item['_entity']['attr']['root_id'],
        $item['_entity']['attr']['lft'],
        $item['_entity']['attr']['rgt'],
        $item['_entity']['attr']['parent_id'],
        $item['_entity']['attr']['level']
    );
    flat_save($item);
    $item['_entity']['attr'] = $attrs;

    // No change in position, so nothing to do anymore
    if ($item['position'] === $item['_old']['position']) {
        return true;
    }

    // Position
    $item['lft'] = node_position($item);
    $range = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

    if ($item['root_id'] === $item['_old']['root_id'] && $item['lft'] > $item['_old']['lft']) {
        $item['lft'] -= $range;
    }

    $item['rgt'] = $item['lft'] + $range - 1;
    $diff = $item['lft'] - $item['_old']['lft'];

    // Move all affected nodes from old tree and update their positions for the new tree without adding them yet
    $stmt = prep(
        'UPDATE %1$s
        SET %2$s = :root_id, %3$s = -1 * (%3$s + :lft_diff), %4$s = -1 * (%4$s + :rgt_diff)
        WHERE %2$s = :old_root_id AND %3$s BETWEEN :lft AND :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':old_root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':lft_diff', $diff, PDO::PARAM_INT);
    $stmt->bindValue(':rgt_diff', $diff, PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    node_remove($item);
    // Make space in the new tree
    node_insert($item);

    // Finally add the affected nodes to new tree
    $stmt = prep(
        'UPDATE %1$s
        SET %3$s = -1 * %3$s, %4$s = -1 * %4$s, %6$s = IF(%5$s = :id, :parent_id, %6$s), %7$s = %7$s + :level
        WHERE %2$s = :root_id AND %3$s < 0',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col'],
        $attrs['rgt']['col'],
        $attrs['id']['col'],
        $attrs['parent_id']['col'],
        $attrs['level']['col']
    );
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
    $attrs = $item['_entity']['attr'];

    $stmt = prep(
        'DELETE FROM %s WHERE %s = :root_id AND %s BETWEEN :lft AND :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    node_remove($item);

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
function node_position(array & $item): int
{
    $attrs = $item['_entity']['attr'];
    $parts = explode(':', $item['position']);
    $item['root_id'] = (int) $parts[0];
    $basis = (int) $parts[1];

    // No or wrong basis given so append node
    if (!$basis || !$basisItem = one($item['_entity']['id'], ['root_id' => $item['root_id'], 'lft' => $basis])) {
        $item['parent_id'] = null;
        $item['level'] = 1;

        $stmt = prep(
            'SELECT COALESCE(MAX(%s), 0) + 1 FROM %s WHERE %s = :root_id',
            $attrs['rgt']['col'],
            $item['_entity']['tab'],
            $attrs['root_id']['col']
        );
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
        throw new LogicException(_('Node can not be child of itself'));
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
 *
 * @return void
 */
function node_insert(array $item)
{
    $attrs = $item['_entity']['attr'];
    $range = $item['rgt'] - $item['lft'] + 1;

    $stmt = prep(
        'UPDATE %1$s SET %3$s = %3$s + :range WHERE %2$s = :root_id AND %3$s >= :lft',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = prep(
        'UPDATE %1$s SET %3$s = %3$s + :range WHERE %2$s = :root_id AND %3$s >= :lft',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':lft', $item['lft'], PDO::PARAM_INT);
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
function node_remove(array $item)
{
    $attrs = $item['_entity']['attr'];
    $range = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

    $stmt = prep(
        'UPDATE %1$s SET %3$s = %3$s - :range WHERE %2$s = :root_id AND %3$s > :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = prep(
        'UPDATE %1$s SET %3$s = %3$s - :range WHERE %2$s = :root_id AND %3$s > :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();
}
