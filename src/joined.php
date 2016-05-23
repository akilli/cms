<?php
namespace qnd;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $criteria
 * @param array $opts
 *
 * @return int
 */
function joined_size(string $eId, array $criteria = [], array $opts = []): int
{
    $entity = data('entity', $eId);
    $criteria['entity_id'] = $eId;

    $stmt = prep(
        'SELECT COUNT(*) FROM content c NATURAL JOIN %s j %s',
        $entity['tab'],
        where($criteria, $entity['attr'], $opts)
    );
    $stmt->execute();

    return (int) $stmt->fetchColumn();
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
function joined_load(string $eId, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $criteria['entity_id'] = $eId;
    $attrs = $entity['attr'];
    $opts = ['search' => $index === 'search'];

    $stmt = db()->prepare(
        select($attrs)
        . from('content', 'c')
        . njoin($entity['tab'], 'j')
        . where($criteria, $attrs, $opts)
        . order($order, $attrs)
        . limit($limit)
    );
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Create entity
 *
 * @param array $item
 *
 * @return bool
 */
function joined_create(array & $item): bool
{
    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attr'];

    // Save main attributes
    $mainAttrs = data('entity', 'content')['attr'];
    $cols = cols($mainAttrs, $item);

    $stmt = prep(
        'INSERT INTO content (%s) VALUES (%s)',
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    // Set DB generated id
    $item['id'] = (int) db()->lastInsertId();

    // Save additional attributes
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $addAttrs['id'] = $attrs['id'];
    $addAttrs['id']['generator'] = null;

    $cols = cols($addAttrs, $item);
    $stmt = prep(
        'INSERT INTO %s (%s) VALUES (%s)',
        $item['_entity']['tab'],
        implode(', ', $cols['col']),
        implode(', ', $cols['param'])
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->execute();

    return true;
}

/**
 * Save entity
 *
 * @param array $item
 *
 * @return bool
 */
function joined_save(array & $item): bool
{
    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attr'];

    // Save main attributes
    $mainAttrs = data('entity', 'content')['attr'];
    $cols = cols($mainAttrs, $item);

    $stmt = prep(
        'UPDATE content SET %s WHERE id = :_id',
        implode(', ', $cols['set'])
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':_id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
    $stmt->execute();

    // Save additional attributes
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $cols = cols($addAttrs, $item);
    $stmt = prep(
        'UPDATE %s SET %s WHERE %s = :_id',
        $item['_entity']['tab'],
        implode(', ', $cols['set']),
        $attrs['id']['col']
    );

    foreach ($cols['param'] as $code => $param) {
        $stmt->bindValue($param, $item[$code], db_type($attrs[$code], $item[$code]));
    }

    $stmt->bindValue(':_id', $item['_old']['id'], db_type($attrs['id'], $item['_old']['id']));
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
function joined_delete(array & $item): bool
{
    $item['_entity']['tab'] = 'content';

    return !empty($item['_entity']['id']) && $item['_entity']['id'] === $item['entity_id'] && flat_delete($item);
}
