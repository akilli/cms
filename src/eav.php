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
    $content = data('entity', 'content')['attr'];
    $eav = eav_attr($entity['attr']);
    $crit['entity_id'] = $entity['id'];

    if (!$eav) {
        return flat_size($entity, $crit, $opts);
    }

    $list = [];
    $params = [];

    foreach ($eav as $uid => $attr) {
        if (empty($crit[$uid])) {
            continue;
        }

        $val = db_qa($attr, (array) $crit[$uid]);
        $params[$uid] = db_param($uid);
        $list[] = sprintf(
            '(id IN (SELECT content_id FROM eav WHERE attr_id = %s AND %s IN (%s)))',
            $params[$uid],
            db_cast('value', $attr['backend']),
            implode(', ', $val)
        );
    }

    $stmt = db_prep(
        'SELECT COUNT(*) FROM %s %s %s',
        $entity['tab'],
        where($crit, $content, $opts),
        $list ? ' AND ' . implode(' AND ', $list) : ''
    );

    foreach ($params as $uid => $param) {
        $stmt->bindValue($param, $eav[$uid]['eav_id'], PDO::PARAM_INT);
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
    $content = data('entity', 'content')['attr'];
    $eav = eav_attr($entity['attr']);
    $crit['entity_id'] = $entity['id'];

    if (!$eav) {
        return flat_load($entity, $crit, $opts);
    }

    $opts['as'] = 'e';
    $list = [];
    $params = [];
    $having = [];

    foreach ($eav as $uid => $attr) {
        $params[$uid] = db_param($uid);
        $list[] = sprintf(
            'MAX(CASE WHEN a.attr_id = %s THEN %s END) AS %s',
            $params[$uid],
            $attr['backend'] === 'search' ? 'a.value' : db_cast('a.value', $attr['backend']),
            db_qi($uid)
        );

        if (isset($crit[$uid])) {
            $having[$uid] = $crit[$uid];
            unset($crit[$uid]);
        }
    }

    $stmt = db()->prepare(
        select($content, 'e') . ($list ? ', ' . implode(', ', $list) : '')
        . from($entity['tab'], 'e')
        . ($list ? ' LEFT JOIN eav a ON a.content_id = e.id' : '')
        . where($crit, $content, $opts)
        . group(['id'])
        . having($having, $entity['attr'], $opts)
        . order($opts['order'] ?? [], $entity['attr'])
        . limit($opts['limit'] ?? 0, $opts['offset'] ?? 0)
    );

    foreach ($params as $uid => $param) {
        $stmt->bindValue($param, $eav[$uid]['eav_id'], PDO::PARAM_INT);
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
    $item['modified'] = date(data('format', 'datetime.backend'));
    $item['creator'] = $item['modifier'];
    $item['created'] = $item['modified'];
    $attrs = $item['_entity']['attr'];
    $content = data('entity', 'content')['attr'];
    $eav = eav_attr($attrs);

    // Main attributes
    $item['_entity']['attr'] = $content;
    $result = flat_create($item);
    $item['_entity']['attr'] = $attrs;

    if (!$result || !$eav) {
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

    foreach ($eav as $uid => $attr) {
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
    $item['modified'] = date(data('format', 'datetime.backend'));
    $attrs = $item['_entity']['attr'];
    $content = data('entity', 'content')['attr'];
    $eav = eav_attr($attrs);

    // Main attributes
    $item['_entity']['attr'] = $content;
    $result = flat_save($item);
    $item['_entity']['attr'] = $attrs;

    if (!$result || !$eav) {
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

    foreach ($eav as $uid => $attr) {
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

/**
 * @param array $attrs
 *
 * @return array
 */
function eav_attr(array $attrs): array
{
    return array_filter(
        array_diff_key($attrs, data('entity', 'content')['attr']),
        function (array $attr) {
            return !empty($attr['col']);
        }
    );
}
