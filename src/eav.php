<?php
namespace qnd;

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
function eav_load(array $entity, array $crit = [], array $opts = []): array
{
    $crit['entity_id'] = $entity['eav_id'];

    if (!$eav = eav_attr($entity['attr'])) {
        return flat_load($entity, $crit, $opts);
    }

    $attrs = db_attr($entity['attr']);
    $main = db_attr(data('entity', 'content')['attr']);
    $select = array_column($main, 'col');
    $list = ['id' => 'content_id'];
    $params = [];

    foreach ($eav as $uid => $attr) {
        $select[] = db_qi($uid);
        $params[$uid] = db_param($uid);
        $list[$uid] = sprintf(
            'MAX(CASE WHEN attr_id = %s THEN %s END)',
            $params[$uid],
            $attr['backend'] === 'search' ? 'value' : db_cast('value', $attr['backend'])
        );
    }

    $select = $opts['mode'] === 'size' ? ['COUNT(*)'] : $select;
    $stmt = db()->prepare(
        select($select)
        . from($entity['tab'])
        . njoin('(' . select($list) . from('eav'). group(['id']) . ')', 'a')
        . where($crit, $attrs, $opts)
        . order($opts['order'])
        . limit($opts['limit'], $opts['offset'])
    );

    foreach ($params as $uid => $param) {
        $stmt->bindValue($param, $eav[$uid]['eav_id'], PDO::PARAM_INT);
    }

    $stmt->execute();

    if ($opts['mode'] === 'size') {
        return [(int) $stmt->fetchColumn()];
    }

    if ($opts['mode'] === 'one') {
        return $stmt->fetch() ?: [];
    }

    return $stmt->fetchAll();
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
    $item['entity_id'] = $item['_entity']['eav_id'];
    $item['modifier'] = account('id');
    $item['modified'] = date(data('format', 'datetime.backend'));

    if (empty($item['_old'])) {
        $item['creator'] = $item['modifier'];
        $item['created'] = $item['modified'];
    }

    $attrs = $item['_entity']['attr'];
    $eav = eav_attr($attrs);

    // Main attributes
    $item['_entity']['attr'] = data('entity', 'content')['attr'];
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

    foreach (array_intersect_key($eav, $item) as $uid => $attr) {
        $stmt->bindValue(':content_id', $item['id'], PDO::PARAM_INT);
        $stmt->bindValue(':attr_id', $attr['eav_id'], PDO::PARAM_INT);
        $stmt->bindValue(':value', db_val($item[$uid], $attr), PDO::PARAM_STR);
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
    return !empty($item['_entity']['eav_id']) && $item['_entity']['eav_id'] === $item['entity_id'] && flat_delete($item);
}

/**
 * EAV DB attributes
 *
 * @param array $attrs
 *
 * @return array
 */
function eav_attr(array $attrs): array
{
    return db_attr(array_diff_key($attrs, data('entity', 'content')['attr']));
}
