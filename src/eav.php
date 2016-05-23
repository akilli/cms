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
function eav_size(string $eId, array $crit = [], array $opts = []): int
{
    $entity = data('entity', $eId);
    $attrs = $entity['attr'];
    $mainAttrs = data('entity', 'content')['attr'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $crit['entity_id'] = $entity['id'];
    $list = [];
    $params = [];

    foreach ($addAttrs as $code => $attr) {
        if (empty($attr['col']) || empty($crit[$code])) {
            continue;
        }

        $val = array_map(
            function ($v) use ($attr) {
                return qv($v, $attr['backend']);
            },
            (array) $crit[$code]
        );
        $params[$code] = ':' . str_replace('-', '_', $code);
        $list[] = sprintf(
            '(id IN (SELECT content_id FROM eav WHERE attr_id = %s AND CAST(value AS %s) IN (%s)))',
            $params[$code],
            db_cast($attr),
            implode(', ', $val)
        );
    }

    $stmt = prep(
        'SELECT COUNT(*) FROM content %s %s',
        where($crit, $mainAttrs, $opts),
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
 * @param array $crit
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function eav_load(string $eId, array $crit = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $attrs = $entity['attr'];
    $mainAttrs = data('entity', 'content')['attr'];
    $addAttrs = array_diff_key($attrs, $mainAttrs);
    $crit['entity_id'] = $entity['id'];
    $opts = ['as' => 'e', 'search' => $index === 'search'];
    $list = [];
    $params = [];
    $having = [];

    foreach ($addAttrs as $code => $attr) {
        if (empty($attr['col'])) {
            continue;
        }

        $params[$code] = ':' . str_replace('-', '_', $code);
        $list[] = sprintf(
            'MAX(CASE WHEN a.attr_id = %s THEN CAST(a.value AS %s) END) AS %s',
            $params[$code],
            db_cast($attr),
            qi($code)
        );

        if (isset($crit[$code])) {
            $having[$code] = $crit[$code];
            unset($crit[$code]);
        }
    }

    $stmt = db()->prepare(
        select($mainAttrs, 'e')
        . ($list ? ', ' . implode(', ', $list) : '')
        . ' FROM content e'
        . ($list ? ' LEFT JOIN eav a ON a.content_id = e.id' : '')
        . where($crit, $mainAttrs, $opts)
        . group(['id'])
        . having($having, $attrs, $opts)
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
    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attr'];
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
    $stmt = db()->prepare('
        INSERT INTO 
            eav
            (attr_id, content_id, value) 
         VALUES 
            (:attr_id, :content_id, :value)
    ');

    foreach ($addAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attr_id', $attr['id'], PDO::PARAM_STR);
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
    $item['entity_id'] = $item['_entity']['id'];
    $attrs = $item['_entity']['attr'];
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
    $stmt = db()->prepare('
        INSERT INTO 
            eav
        SET
            attr_id = :attr_id,
            content_id = :content_id,
            value = :value
        ON DUPLICATE KEY UPDATE
            value = VALUES(value)
    ');

    foreach ($addAttrs as $code => $attr) {
        if (!array_key_exists($code, $item)) {
            continue;
        }

        $stmt->bindValue(':attr_id', $attr['id'], PDO::PARAM_STR);
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
