<?php
namespace qnd;

use LogicException;
use PDO;

/**
 * Load entity
 *
 * @param array $entity
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function nestedset_load(array $entity, array $crit = [], array $opts = []): array
{
    $opts['order'] = $opts['mode'] === 'size' || $opts['order'] ? $opts['order'] : ['root_id' => 'asc', 'lft' => 'asc'];
    $opts['select'] = ['pos' => "root_id || ':' || lft"];

    return flat_load($entity, $crit, $opts);
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function nestedset_save(array & $item): bool
{
    // New node
    if (empty($item['_old'])) {
        $item = nestedset_position($item);
        nestedset_prepare($item);

        return flat_save($item);
    }

    // Update all attributes that are not involved with the tree
    $temp = $item;
    unset($temp['root_id'], $temp['lft'], $temp['rgt'], $temp['level']);
    flat_save($temp);

    // No change in position, so nothing to do anymore
    if ($item['pos'] === $item['_old']['pos']) {
        return true;
    }

    // Handle tree changes
    $item = nestedset_position($item);
    nestedset_move($item);
    nestedset_remove($item);
    nestedset_prepare($item);
    nestedset_insert($item);

    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function nestedset_delete(array & $item): bool
{
    $attrs = $item['_entity']['attr'];

    $stmt = db_prep(
        'DELETE FROM %s WHERE %s = :root_id AND %s BETWEEN :lft AND :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], db_pdo($item['_old']['root_id'], $attrs['root_id']));
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->execute();

    // Close gap in old tree
    nestedset_remove($item);

    return true;
}

/**
 * Calculate node postion
 *
 * @param array $item
 *
 * @return array
 *
 * @throws LogicException
 */
function nestedset_position(array $item): array
{
    $attrs = $item['_entity']['attr'];
    $o = $item['_old'] ?? null;
    $range = $o ? $o['rgt'] - $o['lft'] + 1 : 2;
    $parts = explode(':', $item['pos']);
    $item['root_id'] = cast($attrs['root_id'], $parts[0]);
    $bLft = (int) $parts[1];

    if (!$bLft || !$b = one($item['_entity']['uid'], ['root_id' => $item['root_id'], 'lft' => $bLft])) {
        // No or wrong basis given so append node
        $stmt = db_prep(
            'SELECT COALESCE(MAX(%s), 0) + 1 FROM %s WHERE %s = :root_id',
            $attrs['rgt']['col'],
            $item['_entity']['tab'],
            $attrs['root_id']['col']
        );
        $stmt->bindValue(':root_id', $item['root_id'], db_pdo($item['root_id'], $attrs['root_id']));
        $stmt->execute();

        $item['lft'] = (int) $stmt->fetchColumn();
        $item['level'] = 1;
    } elseif ($o && $item['root_id'] === $o['root_id'] && $o['lft'] < $b['lft'] && $o['rgt'] > $b['rgt']) {
        // Recursion
        throw new LogicException(_('Node can not be child of itself'));
    } elseif ($item['mode'] === 'child') {
        // Add child
        $item['lft'] = $b['rgt'];
        $item['level'] = $b['level'] + 1;
    } elseif ($item['mode'] === 'before') {
        // Ad before
        $item['lft'] = $b['lft'];
        $item['level'] = $b['level'];
    } else {
        // Add after
        $item['lft'] = $b['rgt'] + 1;
        $item['level'] = $b['level'];
    }

    if ($o && $item['root_id'] === $o['root_id'] && $item['lft'] > $o['lft']) {
        $item['lft'] -= $range;
    }

    $item['rgt'] = $item['lft'] + $range - 1;

    return $item;
}

/**
 * Move all affected nodes from old tree and update their positions for the new tree without adding them yet
 *
 * @param array $item
 *
 * @return void
 */
function nestedset_move(array $item): void
{
    $attrs = $item['_entity']['attr'];

    $stmt = db_prep(
        'UPDATE %1$s SET %2$s = :root_id, %3$s = -1 * (%3$s + :lft_diff), %4$s = -1 * (%4$s + :rgt_diff) WHERE %2$s = :old_root_id AND %3$s BETWEEN :lft AND :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], db_pdo($item['root_id'], $attrs['root_id']));
    $stmt->bindValue(':old_root_id', $item['_old']['root_id'], db_pdo($item['_old']['root_id'], $attrs['root_id']));
    $stmt->bindValue(':lft', $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':lft_diff', $item['lft'] - $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':rgt_diff', $item['lft'] - $item['_old']['lft'], PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Close gap in old tree
 *
 * @param array $item
 *
 * @return void
 */
function nestedset_remove(array $item): void
{
    $attrs = $item['_entity']['attr'];
    $range = $item['_old']['rgt'] - $item['_old']['lft'] + 1;

    $stmt = db_prep(
        'UPDATE %1$s SET %3$s = %3$s - :range WHERE %2$s = :root_id AND %3$s > :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], db_pdo($item['_old']['root_id'], $attrs['root_id']));
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db_prep(
        'UPDATE %1$s SET %3$s = %3$s - :range WHERE %2$s = :root_id AND %3$s > :rgt',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['_old']['root_id'], db_pdo($item['_old']['root_id'], $attrs['root_id']));
    $stmt->bindValue(':rgt', $item['_old']['rgt'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Make space in new tree
 *
 * @param array $item
 *
 * @return void
 */
function nestedset_prepare(array $item): void
{
    $attrs = $item['_entity']['attr'];
    $range = $item['rgt'] - $item['lft'] + 1;

    $stmt = db_prep(
        'UPDATE %1$s SET %3$s = %3$s + :range WHERE %2$s = :root_id AND %3$s >= :lft',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], db_pdo($item['root_id'], $attrs['root_id']));
    $stmt->bindValue(':lft', $item['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = db_prep(
        'UPDATE %1$s SET %3$s = %3$s + :range WHERE %2$s = :root_id AND %3$s >= :lft',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['rgt']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], db_pdo($item['root_id'], $attrs['root_id']));
    $stmt->bindValue(':lft', $item['lft'], PDO::PARAM_INT);
    $stmt->bindValue(':range', $range, PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Add affected nodes to new tree
 *
 * @param array $item
 *
 * @return void
 */
function nestedset_insert(array $item): void
{
    $attrs = $item['_entity']['attr'];

    $stmt = db_prep(
        'UPDATE %1$s SET %3$s = -1 * %3$s, %4$s = -1 * %4$s, %5$s = %5$s + :level WHERE %2$s = :root_id AND %3$s < 0',
        $item['_entity']['tab'],
        $attrs['root_id']['col'],
        $attrs['lft']['col'],
        $attrs['rgt']['col'],
        $attrs['level']['col']
    );
    $stmt->bindValue(':root_id', $item['root_id'], db_pdo($item['root_id'], $attrs['root_id']));
    $stmt->bindValue(':level', $item['level'] - $item['_old']['level'], PDO::PARAM_INT);
    $stmt->execute();
}
