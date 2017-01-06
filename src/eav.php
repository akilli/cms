<?php
namespace qnd;

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
function eav_size(array $entity, array $crit = [], array $opts = []): int
{
    $main = data('entity', 'content')['attr'];
    $add = array_diff_key($entity['attr'], $main);
    $crit['entity_id'] = $entity['id'];

    if (!$add) {
        return flat_size($entity, $crit, $opts);
    }

    $backend = data('backend');
    $list = [];
    $params = [];

    foreach ($add as $uid => $attr) {
        if (empty($attr['col']) || empty($crit[$uid])) {
            continue;
        }

        $val = array_map(
            function ($v) use ($attr) {
                return qv($v, $attr['backend']);
            },
            (array) $crit[$uid]
        );
        $params[$uid] = db_param($uid);
        $list[] = sprintf(
            '(id IN (SELECT content_id FROM eav WHERE attr_id = %s AND CAST(value AS %s) IN (%s)))',
            $params[$uid],
            $backend[$attr['backend']],
            implode(', ', $val)
        );
    }

    $stmt = prep(
        'SELECT COUNT(*) FROM %s %s %s',
        $entity['tab'],
        where($crit, $main, $opts),
        $list ? ' AND ' . implode(' AND ', $list) : ''
    );

    foreach ($params as $uid => $param) {
        $stmt->bindValue($param, $add[$uid]['eav_id'], PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
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
function eav_load(array $entity, array $crit = [], array $opts = []): array
{
    $main = data('entity', 'content')['attr'];
    $add = array_diff_key($entity['attr'], $main);
    $crit['entity_id'] = $entity['id'];

    if (!$add) {
        return flat_load($entity, $crit, $opts);
    }

    $opts['as'] = 'e';
    $backend = data('backend');
    $list = [];
    $params = [];
    $having = [];

    foreach ($add as $uid => $attr) {
        if (empty($attr['col'])) {
            continue;
        }

        $params[$uid] = db_param($uid);
        $list[] = sprintf(
            'MAX(CASE WHEN a.attr_id = %s THEN CAST(a.value AS %s) END) AS %s',
            $params[$uid],
            $backend[$attr['backend']],
            qi($uid)
        );

        if (isset($crit[$uid])) {
            $having[$uid] = $crit[$uid];
            unset($crit[$uid]);
        }
    }

    $stmt = db()->prepare(
        select($main, 'e') . ($list ? ', ' . implode(', ', $list) : '')
        . from($entity['tab'], 'e')
        . ($list ? ' LEFT JOIN eav a ON a.content_id = e.id' : '')
        . where($crit, $main, $opts)
        . group(['id'])
        . having($having, $entity['attr'], $opts)
        . order($opts['order'] ?? [], $entity['attr'])
        . limit($opts['limit'] ?? 0, $opts['offset'] ?? 0)
    );

    foreach ($params as $uid => $param) {
        $stmt->bindValue($param, $add[$uid]['eav_id'], PDO::PARAM_INT);
    }

    $stmt->execute();

    if (!empty($opts['one'])) {
        return $stmt->fetch() ?: [];
    }

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
    $item['modifier'] = account('id');
    $item['modified'] = date(BACKEND_DATETIME);
    $item['creator'] = $item['modifier'];
    $item['created'] = $item['modified'];
    $attrs = $item['_entity']['attr'];
    $main = data('entity', 'content')['attr'];
    $add = array_diff_key($attrs, $main);

    // Main attributes
    $item['_entity']['attr'] = $main;
    $result = flat_create($item);
    $item['_entity']['attr'] = $attrs;

    if (!$result || !$add) {
        return $result;
    }

    // Save additional attributes
    $stmt = db()->prepare('
        INSERT INTO 
            eav
            (content_id, attr_id, value) 
         VALUES 
            (:content_id, :attr_id, :value)
    ');

    foreach ($add as $uid => $attr) {
        if (!array_key_exists($uid, $item)) {
            continue;
        }

        $val = $attr['multiple'] && $attr['backend'] === 'json' ? json_encode($item[$uid]) : $item[$uid];
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':attr_id', $attr['eav_id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $val, PDO::PARAM_STR);
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
    $item['modifier'] = account('id');
    $item['modified'] = date(BACKEND_DATETIME);
    $attrs = $item['_entity']['attr'];
    $main = data('entity', 'content')['attr'];
    $add = array_diff_key($attrs, $main);

    // Main attributes
    $item['_entity']['attr'] = $main;
    $result = flat_save($item);
    $item['_entity']['attr'] = $attrs;

    if (!$result || !$add) {
        return $result;
    }

    // Save additional attributes
    $stmt = db()->prepare('
        INSERT INTO 
            eav 
            (content_id, attr_id, value) 
        VALUES 
            (:content_id, :attr_id, :value) 
        ON CONFLICT 
            (content_id, attr_id) 
        DO UPDATE SET 
            value = EXCLUDED.value
    ');

    foreach ($add as $uid => $attr) {
        if (!array_key_exists($uid, $item)) {
            continue;
        }

        $val = $attr['multiple'] && $attr['backend'] === 'json' ? json_encode($item[$uid]) : $item[$uid];
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':attr_id', $attr['eav_id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', $val, PDO::PARAM_STR);
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
