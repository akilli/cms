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
function eav_size(string $eId, array $criteria = [], array $options = []): int
{
    $entity = data('entity', $eId);
    $attrs = $entity['attributes'];
    $mainAttrs = data('entity', 'content')['attributes'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $criteria['entity_id'] = $entity['id'];
    $list = [];
    $params = [];

    foreach ($addAttrs as $code => $attr) {
        if (empty($attr['column']) || empty($criteria[$code])) {
            continue;
        }

        $val = array_map(
            function ($v) use ($attr) {
                return qv($v, $attr['backend']);
            },
            (array) $criteria[$code]
        );
        $params[$code] = ':' . str_replace('-', '_', $code);
        $list[] = sprintf(
            '(id IN (SELECT content_id FROM eav WHERE attribute_id = %s AND CAST(value AS %s) IN (%s)))',
            $params[$code],
            db_cast($attr),
            implode(', ', $val)
        );
    }

    $stmt = prep(
        'SELECT COUNT(*) FROM content %s %s',
        where($criteria, $mainAttrs, $options),
        $list ? ' AND ' . implode(' AND ', $list) : ''
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $attrs[$code]['id'], PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
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
function eav_load(string $eId, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $attrs = $entity['attributes'];
    $mainAttrs = data('entity', 'content')['attributes'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $criteria['entity_id'] = $entity['id'];
    $options = ['alias' => 'e', 'search' => $index === 'search'];
    $list = [];
    $params = [];
    $having = [];

    foreach ($addAttrs as $code => $attr) {
        if (empty($attr['column'])) {
            continue;
        }

        $params[$code] = ':' . str_replace('-', '_', $code);
        $list[] = sprintf(
            'MAX(CASE WHEN a.attribute_id = %s THEN CAST(a.value AS %s) END) AS %s',
            $params[$code],
            db_cast($attr),
            qi($code)
        );

        if (isset($criteria[$code])) {
            $having[$code] = $criteria[$code];
            unset($criteria[$code]);
        }
    }

    $stmt = db()->prepare(
        select($mainAttrs, 'e')
        . ($list ? ', ' . implode(', ', $list) : '')
        . ' FROM content e'
        . ($list ? ' LEFT JOIN eav a ON a.content_id = e.id' : '')
        . where($criteria, $mainAttrs, $options)
        . ' GROUP BY e.id'
        . having($having, $attrs, $options)
        . order($order, $attrs)
        . limit($limit)
    );

    foreach ($params as $code => $param) {
        $stmt->bindValue($param, $attrs[$code]['id'], PDO::PARAM_STR);
    }

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
function eav_create(array & $item): bool
{
    if (empty($item['_entity'])) {
        return false;
    }

    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attributes'];
    $mainAttrs = data('entity', 'content')['attributes'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
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

    // Insert values
    $stmt = db()->prepare('
        INSERT INTO 
            eav
            (attribute_id, content_id, value) 
         VALUES 
            (:attribute_id, :content_id, :value)
    ');

    foreach ($addAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attribute_id', $attr['id'], PDO::PARAM_STR);
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $item[$code], PDO::PARAM_STR);
        $stmt->execute();
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
function eav_save(array & $item): bool
{
    if (empty($item['_entity'])) {
        return false;
    }

    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attributes'];
    $mainAttrs = data('entity', 'content')['attributes'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
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

    // Save values
    $stmt = db()->prepare('
        INSERT INTO 
            eav
        SET
            attribute_id = :attribute_id,
            content_id = :content_id,
            value = :value
        ON DUPLICATE KEY UPDATE
            value = VALUES(value)
    ');

    foreach ($addAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attribute_id', $attr['id'], PDO::PARAM_STR);
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $item[$code], PDO::PARAM_STR);
        $stmt->execute();
    }

    return true;
}

/**
 * Delete entity
 *
 * @param array $item
 *
 * @return bool
 */
function eav_delete(array & $item): bool
{
    return !empty($item['_entity']['id']) && $item['_entity']['id'] === $item['entity_id'] && flat_delete($item);
}
